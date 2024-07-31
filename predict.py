from simpletransformers.classification import ClassificationModel, ClassificationArgs
import time
from transformers import BertTokenizer, BertModel
import torch
import model_db
from datetime import datetime
import random
import sys

def predict(listData):
    dir_name = r'C:\114project\outputs\bert-base-Chinese-bs-64-epo-3'

    model_args = ClassificationArgs()
    model_args.train_batch_size = 64
    model_args.num_train_epochs = 3

    model = ClassificationModel(
        'bert', 
        dir_name , 
        use_cuda = True,
        cuda_device=0, 
        num_labels = 6, 
        args = model_args
    )

    predictions, raw_outputs = model.predict(listData)
    return predictions

def spilt(article):
    # 加载中文BERT模型和tokenizer
    tokenizer = BertTokenizer.from_pretrained('bert-base-chinese')
    model = BertModel.from_pretrained('bert-base-chinese')

    # 文章 string

    # 分成适当的长度
    max_length = 512
    article_chunks = [article[i:i+max_length] for i in range(0, len(article), max_length)]

    # 断句
    sentences = []
    for chunk in article_chunks:
        # 文本编码
        inputs = tokenizer(chunk, return_tensors="pt", max_length=max_length, truncation=True)
        with torch.no_grad():
            outputs = model(**inputs)

    # 判断断句位置（这里简单地基于句号判断）
    tokenized_text = tokenizer.tokenize(chunk)
    sentence_indices = [i for i, token in enumerate(tokenized_text) if token == '。' or token == ';' or token == '.' or token == '；' or token == '?' or token == '!' or token == '~' or token == '．' or token == '？' or token == '！' or token == ' ']

    # 将文章断句
    start = 0
    for idx in sentence_indices:
        sentence = tokenizer.decode(inputs.input_ids[0][start:idx+1])
         

        sentences.append(sentence)
        start = idx + 1

    # 打印结果
    data = []
    for sentence in sentences:
        if len(sentence) > 1:
            data.append(sentence)
    return data

def stats(finalpredict):
    total = len(finalpredict)  # 總共的情緒數量
    other = 0  # 0
    like = 0   # 1
    sadness = 0  # 2
    disgust = 0  # 3
    anger = 0    # 4
    happiness = 0  # 5
    
    # Initialize data list to store counts for each emotion
    data = [0] * 6
    
    # Count occurrences of each emotion
    for i in finalpredict:
        if i == 0:
            other += 1
        elif i == 1:
            like += 1
        elif i == 2:
            sadness += 1
        elif i == 3:
            disgust += 1
        elif i == 4:
            anger += 1
        elif i == 5:
            happiness += 1
    
    # Assign counts to data list in corresponding index positions
    data[0] = sadness/total*100
    data[1] = disgust/total*100
    data[2] = like/total*100
    data[3] = anger/total*100
    data[4] = happiness/total*100
    data[5] = other/total*100
    
    return data

def randomToChooose(data): 
    pos = [data[2], data[4]]
    neg = [data[0], data[1], data[3]]

    maxPos = max(pos)
    maxNeg = max(neg)

    maxPosIndex = pos.index(maxPos)
    maxNegIndex = neg.index(maxNeg)

    posRandom = random.randint(0, 5) if maxPosIndex == 0 else random.randint(6, 10)
    negRandom = random.randint(0, 5) if maxNegIndex == 0 else (random.randint(6, 10) if maxNegIndex == 1 else random.randint(11, 15))
    
    return posRandom, negRandom

if __name__ =="__main__":
    tStart = time.time()
    # article = str(input("請輸入你的想法: "))
    if len(sys.argv) != 2:
        print("Usage:python predict.py <diary_id>")
        sys.exit(1)
    diary_Id = sys.argv[1]
    # diary_Id = str(input("請輸入日記id: "))
    
    listData = []

    articles = []
    articles = model_db.get_diary_content(diary_Id)

    listData = spilt(articles) # 斷句
    finalpredict = predict(listData) # 最終預測結果
    print(finalpredict)

    statistics = []
    statistics = stats(finalpredict) # 數據分析
    posRandom, negRandom = randomToChooose(statistics)

    date = datetime.now().strftime('%Y-%m-%d')
    model_db.save_db_analysis(date, statistics, diary_Id)
    model_db.save_db_diaries_mon_ang(diary_Id, posRandom, negRandom)
    
    tEnd = time.time()
    print(f"執行花費{tEnd-tStart}秒。")