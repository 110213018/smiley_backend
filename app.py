from flask import Flask, request, jsonify
import predict
import model_db
from datetime import datetime
import numpy as np
import toDB

app = Flask(__name__)

@app.route('/analyze', methods=['POST'])
def analyze():
    try:
        data = request.get_json()
        if data is None:
            return jsonify({"error": "Invalid JSON"}), 400

        uid = data.get('uid')
        article = data.get('article')
        if not uid:
            return jsonify({"error": "uid must be provided"}), 400
        if not article:
            return jsonify({"error": "article must be provided"}), 400

        # 回傳收到的東西
        received_data = {"uid": uid, "article": article}
        
        # 斷句
        listData = predict.split(article)
        
        # 分析情緒
        finalpredict = predict.predict(listData)

        # 傳換為數據
        statistics = predict.stats(finalpredict) 

        #現在時間
        current_date = datetime.now().strftime('%Y-%m-%d')

        # 如果 finalpredict 是 ndarray，先将其转换为列表
        if isinstance(finalpredict, np.ndarray):
            finalpredict = finalpredict.tolist()
        
        if not toDB.save_db_analysis (uid, current_date, statistics):
            return jsonify({"error": "Failed to save data to database"}), 500

        # 回傳以上所有階段性成果
        return jsonify({
            # "received_data": received_data, 
            # "listData": listData, 
            "finalpredict": finalpredict,
            "statistics":statistics,
            # "current_date":current_date
        }), 200

    except Exception as e:
        return jsonify({"error": str(e)}), 500

@app.route('/')
def home():
    return "Emotion Analysis API is running."

if __name__ == '__main__':
    app.run(debug=True)