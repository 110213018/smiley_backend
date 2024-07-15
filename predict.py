from simpletransformers.classification import ClassificationModel, ClassificationArgs
import time
from transformers import BertTokenizer, BertModel
import torch
import model_db
from datetime import datetime

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
    total = 0 # 總共以及各情緒數量
    other = 0 # 0
    like = 0 # 1
    sadness = 0 # 2
    disgust = 0 # 3
    anger = 0 # 4
    happiness = 0 # 5
    data = []
    for i in finalpredict:
        if i == 0:
            other += 1
            total += 1
        if i == 1:
            like += 1
            total += 1
        if i == 2:
            sadness += 1
            total += 1
        if i == 3:
            disgust += 1
            total += 1
        if i == 4:
            anger += 1
            total += 1
        if i == 5:
            happiness += 1
            total += 1
    for i in range(len):
        data[0].append(other)
        data[1].append(like)
        data[2].append(sadness)
        data[3].append(disgust)
        data[4].append(anger)
        data[5].append(happiness)
    return data


if __name__ =="__main__":
    tStart = time.time()
    # article = str(input("請輸入你的想法: "))

    diaryId = str(input("請輸入日記id: "))
    
    listData = []

    listData = get_diary_content(diaryId)

    listData = spilt(articles) # 斷句
    finalpredict = predict(listData) # 最終預測結果
    print(finalpredict)

    statistics = []
    statistics = stats(finalpredict) # 數據分析

    date = datetime.now().strftime('%Y-%m-%d')
    save_db(date, statistics, diary_id)

    tEnd = time.time()
    print(f"執行花費{tEnd-tStart}秒。")