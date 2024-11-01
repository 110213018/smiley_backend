# utils.py

from datetime import datetime
import mysql.connector
import re
from OutsideInfo.weatherInfomation import WeatherForcast

# 使用者設置
userName = "User"

def getUserName(_username):
    global userName
    userName = _username

def getUserLocation():
    return "南投縣"

def getChatHistory(userID):
    chatHistory = []
    try:
        db = mysql.connector.connect(
            host="localhost",
            user="root",
            password="",
            database="smiley"
        )
        cursor = db.cursor()
        cursor.execute("SELECT user_id, role, sentence FROM robot_chats")
        result = cursor.fetchall()
        for data in result:
            if data[0] == userID:
                role = "user" if data[1] == "user" else "assistant"
                content = f"「{userName}」：「{data[2]}」" if role == "user" else data[2]
                chatHistory.append({'role': role, 'content': content})
    except mysql.connector.Error as err:
        print(f"Error fetching chat history: {err}")
    finally:
        cursor.close()
        db.close()
    return chatHistory

def getWeather():
    allCountyWeatherFocast = WeatherForcast()
    userLocationWeather = ""
    forecast = re.sub(r"\s+", "", allCountyWeatherFocast)
    matches = re.findall(r'「(.*?)」', forecast)
    for match in matches:
        if getUserLocation() in match:
            userLocationWeather += match
    return userLocationWeather

def saveToDB_RobotChat(user_id, role, sentence, time):
    try:
        db = mysql.connector.connect(
            host="localhost",
            user="root",
            password="",
            database="smiley"
        )
        cursor = db.cursor()
        query = "INSERT INTO robot_chats (user_id, role, sentence, time) VALUES (%s, %s, %s, %s)"
        cursor.execute(query, (user_id, role, sentence, time))
        db.commit()
    except mysql.connector.Error as err:
        print(f"Error saving chat: {err}")
        return False
    finally:
        cursor.close()
        db.close()
    return True

class AboutTime:
    @staticmethod
    def getCurrentTime():
        now = datetime.now()
        weekdays = ['星期一', '星期二', '星期三', '星期四', '星期五', '星期六', '星期日']
        weekday = weekdays[now.weekday()]
        return f"{now.year}年{now.month}月{now.day}日，{weekday}，{now.hour}點{now.minute}分{now.second}秒"

    @staticmethod
    def getCurrentTime_forSQL():
        return datetime.now().strftime('%Y-%m-%d %H:%M:%S')

    @staticmethod
    def firstMessage():
        hour = datetime.now().hour
        if 6 <= hour < 11:
            return "早安"
        elif 11 <= hour < 14:
            return "午安"
        elif 14 <= hour < 18:
            return "安安"
        else:
            return "晚上好"

def robotName():
    return "小蜜"
assistantName = robotName()

