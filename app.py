from flask import Flask, request, jsonify
import predict
import model_db
from datetime import datetime
import numpy as np
import toDB
import chooseEmoji
import clientData # 商家頁面用的用戶圖表
from waitress import serve
from flask_cors import CORS
from waitress import serve
app = Flask(__name__)
CORS(app)


@app.route('/clientData', methods=['POST'])
def client_data():
    try:
        data = request.get_json()
        if data is None:
            return jsonify({"error": "Invalid JSON"}), 400
        uid = data.get('uid')

        if not uid:
            return jsonify({"error": "uid must be provided"}), 400

        # 回傳收到的東西
        received_data = {"uid": uid}
        
        # 計算日期範圍
        start_date, end_date = clientData.calculate_date_range()

        # 週分析
        result = clientData.process_emotion_data(uid, start_date, end_date)

        # 日分析
        dailyData = clientData.daily_analysis(uid, datetime.now())

        #最高情緒
        highestEmotion = clientData.calculate_highest_emotion(dailyData)

        # 回傳以上所有階段性成果
        return jsonify({
            "received_data": received_data, 
            "weeklyData":result,
            "dailyData":dailyData,
            "highestEmotion":highestEmotion,
        }), 200

    # except Exception as e:
    #     return jsonify({
    #         "received_data": received_data, 
    #         "error": str(e)
    #         }), 500
    except Exception as e:
        # 打印完整的錯誤信息到伺服器日誌
        app.logger.error(f"An error occurred: {str(e)}")
        
        return jsonify({
            "received_data": received_data, 
            "error": str(e)
        }), 500

@app.route('/analyze', methods=['POST'])
def analyze():
    try:
        data = request.get_json()
        if data is None:
            return jsonify({"error": "Invalid JSON"}), 400
        uid = data.get('uid')
        article = data.get('article')
        if not article or not isinstance(article, str) or len(article.strip()) == 0:
            return jsonify({"error": "Invalid or empty article provided"}), 400 

        if not uid:
            return jsonify({"error": "uid must be provided"}), 400
        if not article:
            return jsonify({"error": "article must be provided"}), 400

        # 回傳收到的東西
        received_data = {"uid": uid, "article": article}
        
        # 斷句
        listData = predict.split(article)
        if not listData or len(listData) == 0:
            return jsonify({"error": "Article could not be processed"}), 400
        app.logger.info(f"listData: {listData}")
        # 分析情緒
        finalpredict = predict.predict(listData)

        # 傳換為數據
        statistics = predict.stats(finalpredict) 
      
        # #選出圖片種類
        angelType, monsterType = chooseEmoji.compare(statistics)
        
        # #選出圖片檔名
        angel, monster = chooseEmoji.choose(angelType, monsterType)
    
        # #現在時間
        current_date = datetime.now().strftime('%Y-%m-%d')

        # 如果 finalpredict 是 ndarray，先将其转换为列表
        if isinstance(finalpredict, np.ndarray):
            finalpredict = finalpredict.tolist()
        
        if not toDB.save_db_analysis (uid, current_date, statistics, angel, monster):
            return jsonify({"error": "Failed to save data to database"}), 500

        # 回傳以上所有階段性成果
        return jsonify({
            "received_data": received_data, 
            # "listData": listData, 
            "finalpredict": finalpredict,
            "statistics":statistics,
            "angel":angel, 
            "monster": monster,
            "current_date":current_date
        }), 200

    # except Exception as e:
    #     return jsonify({
    #         "received_data": received_data, 
    #         "error": str(e)
    #         }), 500
    except Exception as e:
        # 打印完整的錯誤信息到伺服器日誌
        app.logger.error(f"An error occurred: {str(e)}")
        
        return jsonify({
            "received_data": received_data, 
            "error": str(e)
        }), 500

@app.route('/')
def home():
    return "Emotion Analysis API is running."


if __name__ == '__main__':
    serve(app, host='163.22.32.24', port=5000, threads=4)