import requests
import json
from datetime import datetime, timedelta

API_URL = "http://163.22.32.24/smiley_backend/analysis/getAnalysis.php"

# 從 API 獲取分析結果
def fetch_analysis_result(user_id, date):
    formatted_date = date.strftime("%Y-%m-%d")  # 格式化日期
    response = requests.post(
        API_URL,
        data={"user_id": user_id, "date": formatted_date} 
    )
    ##print(f"Fetching analysis for User ID: {user_id}, Date: {formatted_date}")
    
    # 打印完整的 API 回應
    ##print("API Response:", response.text, )
    
    if response.status_code == 200:
        result = response.json()  
        if result.get("success"):  
            analysis_result = {
                "happiness": result.get("happiness") or 0,  
                "like": result.get("like") or 0,  
                "sadness": result.get("sadness") or 0,  
                "disgust": result.get("disgust") or 0,  
                "anger": result.get("anger") or 0,  
                "other": result.get("other") or 0  
            }
            #print(f'用戶{user_id}, 日期{date}, {analysis_result}')
            return analysis_result
    #print("沒有資料")
    return {}  

# 最高情緒
def calculate_highest_emotion(daily_data):
    if not daily_data:
        return "無數據"

    emotions = {k: v for k, v in daily_data.items() if k in ["happiness", "like", "sadness", "disgust", "anger", "other"]}

    # 找出數值最大的情緒
    highest_emotion = max(emotions, key=emotions.get)
    highest_value = emotions[highest_emotion]

    #print(f"今日最高情緒：{highest_emotion}，數值：{highest_value}%")
    return highest_emotion, highest_value

# 週
def process_emotion_data(user_id, start_date, end_date):
    #print(f"計算用戶 {user_id} 從 {start_date} 到 {end_date} 的情緒百分比 ")
    positive_sums = []  
    negative_sums = []  
    time = 0  

    current_date = start_date
    while current_date <= end_date:
        daily_data = fetch_analysis_result(user_id, current_date)
        if daily_data and (daily_data.get("happiness") is not None):  # 確認有數據且不是 None
            time += 1
            # 計算正面和負面情緒總和
            positive_sum = daily_data.get("happiness", 0) + daily_data.get("like", 0)
            negative_sum = (
                daily_data.get("sadness", 0)
                + daily_data.get("disgust", 0)
                + daily_data.get("anger", 0)
            )
            total = positive_sum + negative_sum

            if total > 0:
                mul = 100.0 / total
                positive_result = round(positive_sum * mul, 1)  # 保留一位小數
                negative_result = round(negative_sum * mul, 1)  # 保留一位小數
            else:
                positive_result = 1000.0  # 無數據時設置為 50%
                negative_result = 1000.0  # 無數據時設置為 50%
            
            positive_sums.append(positive_result)
            negative_sums.append(negative_result)
        else:
            # 如果當天沒有數據，跳過
            continue

        current_date += timedelta(days=1)

    result = {
        "time": time,
        "positive_sums": positive_sums,
        "negative_sums": negative_sums,
    }
    #print(f"共讀取 {time} 筆資料，週分析百分比為 {result}")
    #print("週分析完畢")
    return result


# 日
def daily_analysis(user_id, date):
    #print(f"計算今日情緒百分比")
    daily_data = fetch_analysis_result(user_id, date)  
    if daily_data:  
        emo_sum = (
            daily_data["happiness"]
            + daily_data["like"]
            + daily_data["sadness"]
            + daily_data["disgust"]
            + daily_data["anger"]
        )
        if emo_sum > 0:  
            for key in ["happiness", "like", "sadness", "disgust", "anger"]:
                daily_data[key] = round(daily_data[key] * 100 / emo_sum, 1)  
        #print("今日情緒百分比:", daily_data)
        return daily_data  
    return None  

# 計算日期範圍
def calculate_date_range():
    today = datetime.now()

    # 如果是周一 顯示從週日(昨天)開始-6
    if today.weekday() == 0:  
        end_date = today - timedelta(days=1)
    else:
        # 如果不是周一 顯示從上週日開始-6
        end_date = today - timedelta(days=today.weekday() + 1)
    
    start_date = end_date - timedelta(days=6)
    
    # #print(f"日期範圍: {start_date.strftime('%Y-%m-%d')} 到 {end_date.strftime('%Y-%m-%d')}")
    return start_date, end_date

# # 示例用法
# if __name__ == "__main__":
#     user_id = "23"  
#     start_date, end_date = calculate_date_range()

#     # 處理日期範圍內的情緒數據
#     result = process_emotion_data(user_id, start_date, end_date)

#     # 日分析
#     dailyData = daily_analysis(user_id, datetime.now())


