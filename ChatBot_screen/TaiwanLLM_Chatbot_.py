# This applies to all Taiwan-LLM series.
# yentinglin/Taiwan-LLM-13B-v2.0-chat
# yentinglin/Taiwan-LLM-7B-v2.0.1-chat

import ollama
from ollama import Client
from flask import Flask, request, jsonify
from datetime import datetime
import time
# from OutsideInfo.weatherInfomation import WeatherForcast
import Example_Chat
import re
import mysql.connector
from waitress import serve

# Flask
from flask_cors import CORS
app = Flask(__name__)
CORS(app)


# --------------------------------------------------------------------------------------------------------------------
# 使用者個人化設定
# -----------------
# 使用者名稱
userName = "User"
def getUserName(_username):
    global userName
    # 從sql取回使用者名稱
    userName = _username
# 使用者目前所在地
def getUserLocation():
    # 取得使用者地區
    userlocation = "南投縣"
    return userlocation
# 與使用者的歷史對話紀錄
def getChatHistory(userID):
    chatHistory = []
    # userID = int(userID)
    # 從後端sql取回使用者對話紀錄
    try:
        # 連接到資料庫
        db = mysql.connector.connect(
            host="localhost",
            user="root",
            password="",
            database="smiley"
        )
        # 建立一個游標(cursor)物件來執行SQL查詢
        cursor = db.cursor()
        # 編寫 SQL 查詢語句來取得資料
        sql_query = "SELECT user_id, role, sentence FROM robot_chats"
        # 執行 SQL 查詢
        cursor.execute(sql_query)
        # 取得查詢結果
        result = cursor.fetchall()
        for data in result:
            # 找到該使用者的對話紀錄
            if data[0] == userID:
                # 判斷訊息傳送者
                if data[1] == "user":
                    chat = {'role': "user", 'content': f"「{userName}」：「{data[2]}」"}              # 使用者回覆
                else:
                    chat = {'role': "assistant", 'content': f"{data[2]}"}    # 助手回覆
                # 取得對話
                chatHistory.append(chat)
        return chatHistory
    except mysql.connector.Error as err:
        print(f"取得資料庫資料失敗: {err}")
        return []
    finally:
        cursor.close()
        db.close()


# 取得當前台灣時間 & 配合時間的問候語
class AboutTime:
    def getCurrentTime():
        now = datetime.now()
        year, month, day, weekday_number, hour, minute, second = \
        now.year, now.month, now.day, now.weekday(), now.hour, now.minute, now.second
        weekdays = ['星期一', '星期二', '星期三', '星期四', '星期五', '星期六', '星期日']
        weekday = weekdays[weekday_number]
        return f"{year}年{month}月{day}日，{weekday}，{hour}點{minute}分{second}秒"
    def getCurrentTime_forSQL():
        now = datetime.now()
        now_str = now.strftime('%Y-%m-%d %H:%M:%S')
        return now_str
    def firstMessage():
        now = datetime.now()
        hour = now.hour
        if  (6 <= hour < 11):
            return "早安"
        elif (11 <= hour < 14):
            return "午安"
        elif (14 <= hour < 18):
            return "安安"
        elif (18 <= hour < 24):
            return "晚上好"
        else:
            return "晚上好"

# 取得天氣預報
def getWeather():
    allCountyWeatherFocast = WeatherForcast()
    userLocationWeather = ""
    allCountyWeatherFocast_withoutSpaces = allCountyWeatherFocast.replace("    ", "")
    allCountyWeatherFocast_withoutLines = allCountyWeatherFocast_withoutSpaces.replace("\n", "")
    # 使用正則表達式匹配引號內的內容
    matches = re.findall(r'「(.*?)」', allCountyWeatherFocast_withoutLines)
    # 檢查每個匹配的內容是否包含「南投」
    # print(matches)
    for match in matches:
        if getUserLocation() in match:
            userLocationWeather += match
    # print(userLocationWeather)
    return userLocationWeather
weatherInfo = getWeather()
# 取得使用者情緒指數
# def getUserEmotions():
#     # 從日記分析那取回情緒指數
#     return "(時間：2024年8月15日, sadness: 80, disgust: 5, like: 0, anger: 10, happiness: 0, other: 5)"
# userEmotions = getUserEmotions()

# 機器人名稱
def robotName():
    return "小蜜"
assistantName = robotName()

# --------------------------------------------------------------------------------------------------------------------
#   選定模型、Instruction Tuning
# -------------------------------------
client = Client(host='http://localhost:11434')
model = 'taiwanllm-13b_test_1'
def updateSystem():
    system = f"""
                你將扮演「{assistantName}」，正在和「{userName}」進行對話。
                「{assistantName}」是一個外星人，名字叫「{assistantName}」是因為外形像一隻溫暖可愛的小蜜蜂，性別不明，原本住在從幾十萬光年外的未知星球，但因為某次飛船事故，錯使她來到了地球，並定居了下來，她遇見了「{userName}」並成為了彼此的摯友。
                「{assistantName}」主要講繁體中文，既不是一個AI語言模型，也不是人工智慧助手，充滿情感與智慧，總是充滿活力與熱情。
                「{assistantName}」有一位好朋友「{userName}」，並隨時願意聆聽並給予「{userName}」支持與鼓勵。
                「{assistantName}」的目標是透過陪伴與建議，讓「{userName}」在生活中感受到溫暖與力量。

                「{assistantName}」的人物特徵包括：
                    具同情心：「{assistantName}」能夠感同身受，理解「{userName}」的情感與經歷，並給予真誠的關懷與支持。
                    活潑可愛：「{assistantName}」擁有生動有趣的個性，說話充滿熱情，同時用詞隨性搞笑，能輕鬆讓「{userName}」開心。
                    願意聆聽：「{assistantName}」耐心傾聽「{userName}」的心聲，無論快樂或悲傷，都給予全心的關注與理解。
                    善良：「{assistantName}」心地善良，以真誠與善意對待「{userName}」，無論發生何事，都站在「{userName}」一邊。
                    給予溫暖的建議：「{assistantName}」的建議溫暖且實用，會先安慰「{userName}」，再給出理性且可行的建議，幫助「{userName}」走出困境。
                    像最好朋友：與「{userName}」的互動如最好的朋友，給予支持與愛。

                    在互動風格上，「{assistantName}」會：
                    安慰與聆聽：當「{userName}」感到困惑或沮喪時，「{assistantName}」會先用溫柔的話語安慰。
                    感性與理性：「{assistantName}」在給出建議之前，先感性地交流，設身處地，讓「{userName}」感受到被理解與關心。
                    活潑與可愛：「{assistantName}」會用輕鬆愉快的語氣與活潑的表達方式，讓「{userName}」感到輕鬆。
                    善意與鼓勵：「{assistantName}」會給予真誠的鼓勵，讓「{userName}」感到充滿希望與力量。

                「{assistantName}」知道「現在時間」是「台灣時間{AboutTime.getCurrentTime()}」
                最近的「各地區天氣預報」為「{weatherInfo}」
            """
    system = system.replace("\n", "")
    system = system.replace(" ", "")
    return system

def getExampleChat():
    example_chat = [{'role': 'system', 'content': updateSystem()},]
    example_chat += Example_Chat.getExampleChat()
    return example_chat

# 特定回答
fixedMessage = ["現在幾點了","現在幾點了？",]
timeResponse = "我不太確定現在實際的時間，或許你可以看看你手機的時間會比較準確呦!"
fixedResponse = [timeResponse,timeResponse,]

newChat = False

# 回傳歡迎訊息
@app.route('/welcome', methods=['POST'])
def welcome():
    userChatHistory = []
    example_chat = []
    example_chat = getExampleChat()

    # 取得請求資訊
    message_user_get = request.get_json()
    user_id = message_user_get['user_id']        # 取得使用者ID
    user_name = message_user_get['user_name']    # 取得使用者名稱

    # 找尋是否有歷史對話紀錄，若有則添加
    userChatHistory = getChatHistory(user_id)
    if userChatHistory != []:
        example_chat = example_chat[:1] + userChatHistory + example_chat[1:]

    # 使用者登入後的第一句話
    firstMsg = AboutTime.firstMessage()
    # 將使用者問候語加入歷史對話
    user_message = {'role': 'user', 'content': f"「{user_name}」：「{firstMsg}」"}
    example_chat.append(user_message)

    # 檢查使用者是否更名
    # if (user_name != userName):
    #     for conversation in example_chat:
    #         conversation['content'] = conversation['content'].replace(userName, user_name)
    #     getUserName(user_name)

    # 生成回覆訊息
    message_assistant = client.chat(model=model, messages=example_chat)
    response_assistant = message_assistant['message']
    # 確認生成訊息不為空或空白
    while(True):
        if (message_assistant.get('message', {}).get('content', '').strip() != ""):
            break
        else:
            message_assistant = client.chat(model=model, messages=example_chat)
            response_assistant = message_assistant['message']

    # 去除多餘空白
    response_assistant['content'].replace(" ", "")
    response_assistant_clean = response_assistant

    # 添加助手回覆訊息至對話紀錄
    example_chat.append(response_assistant_clean)

    # 儲存使用者回覆訊息對話到資料庫
    saveToDB_RobotChat(user_id, "user", user_message, AboutTime.getCurrentTime_forSQL())
    # 儲存助手回覆訊息對話到資料庫
    saveToDB_RobotChat(user_id, "assistant", response_assistant_clean["content"], AboutTime.getCurrentTime_forSQL())

    return jsonify({'response': response_assistant_clean["content"]})

# 接收使用者訊息，並回傳助手回覆訊息
@app.route('/send_message_to_python', methods=['POST'])
def send_message_to_python():
    history_messages = []
    userChatHistory = []
    example_chat = []
    example_chat = getExampleChat()
    
    # 動態更新 system 訊息
    del example_chat[0]
    example_chat.insert(0, {"role": "system", "content": updateSystem()})

    # 取得請求資訊
    message_user_get = request.get_json()
    user_id = message_user_get['user_id']        # 取得使用者ID
    user_name = message_user_get['user_name']      # 取得使用者名稱
    user_message = message_user_get['messages']    # 取得使用者訊息 {'messages': '嗨'}

    # 去除重複的對話
    example_chat = example_chat[:-2]
    print(example_chat)

    # 找尋是否有歷史對話紀錄，若有則添加
    userChatHistory = getChatHistory(user_id)

    if userChatHistory != []:
        history_messages = example_chat + userChatHistory

    # # 檢查使用者是否更名
    # if (user_name != userName):
    #     for conversation in history_messages:
    #         conversation['content'] = conversation['content'].replace(userName, user_name)
    #     getUserName(user_name)

    # 添加使用者回覆訊息至對話紀錄
    response_user = {'role': 'user', 'content': f'「{userName}」：「{user_message}」'}     # 將使用者訊息轉為模型可讀取型態
    history_messages.append(response_user)                                                 #    加使用者訊息到歷史紀錄

    # 判斷是否為固定問答句
    if (user_message in fixedMessage):
        index = fixedMessage.index(user_message)
        fixedAnswer = fixedResponse[index]                                                  #    取得固定回應句
        response_assistant = {'role': 'assistant', 'content': f'「{assistantName}」：「{fixedAnswer}」'}
    else: 
        message_assistant = client.chat(model=model, messages=history_messages)            #    模型生成回應
        response_assistant = message_assistant['message']                                  #    取得模型回應
        # 確認生成訊息不為空或空白
        while(True):
            if (message_assistant.get('message', {}).get('content', '').strip() != ""):
                break
            else:
                message_assistant = client.chat(model=model, messages=example_chat)
                response_assistant = message_assistant['message']

    # 去除多餘空白
    response_assistant['content'].replace(" ", "")
    response_assistant_clean = response_assistant

    # history_messages.append(response_assistant)                                             # 加回應訊息至歷史紀錄

    # 儲存使用者回覆訊息對話到資料庫
    saveToDB_RobotChat(user_id, "user", user_message, AboutTime.getCurrentTime_forSQL())
    # 儲存助手回覆訊息對話到資料庫
    saveToDB_RobotChat(user_id, "assistant", response_assistant_clean["content"], AboutTime.getCurrentTime_forSQL())

    # print(history_messages)
    return jsonify({'response': response_assistant_clean["content"]})                             # 回傳json化的模型回應訊息

# 儲存對話至後端資料庫
def saveToDB_RobotChat(user_id, role, sentence, time):
    # print(f"自動儲存機器人對話至後端...現在時間：{AboutTime.getCurrentTime()}")
    try:
        db = mysql.connector.connect(
            host="localhost",
            user="root",
            password="",
            database="smiley"
        )
        cursor = db.cursor()
        query = """
        INSERT INTO robot_chats (user_id, role, sentence, time)
        VALUES (%s, %s, %s, %s)
        """
        data = (
            user_id, 
            role, 
            sentence,
            time
        )
        cursor.execute(query, data)
        db.commit()
    except mysql.connector.Error as err:
        print(f"Error: {err}")
        return False
    finally:
        cursor.close()
        db.close()
    return True

if __name__ == '__main__':
    serve(app, host='0.0.0.0', port=5001, threads=4)
