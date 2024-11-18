# app.py
from flask import Flask, request, jsonify
from flask_cors import CORS
from toDB import fetch_latest_messages, save_message, mark_messages_as_read

app = Flask(__name__)
CORS(app)


@app.route('/get_messages', methods=['POST'])
def get_messages():
    data = request.get_json()
    sender_id = data.get('sender_id')
    receiver_id = data.get('receiver_id')
    messages = fetch_latest_messages(sender_id, receiver_id)
    # print(messages)

    if not messages:
        return jsonify({"message": "No chat history", "data": []}) 
    
    return jsonify({"message": "Success", "data": messages})



@app.route('/send_message', methods=['POST'])
def send_message():
    data = request.get_json()
    sender_id = data.get('sender_id')
    receiver_id = data.get('receiver_id')
    message = data.get('message')
    save_message(sender_id, receiver_id, message)
    return jsonify({'status': '訊息已傳送'})


@app.route('/mark_as_read', methods=['POST'])
def mark_as_read():
    data = request.get_json()
    sender_id = data['sender_id']
    receiver_id = data['receiver_id']
    mark_messages_as_read(sender_id, receiver_id)
    return jsonify({'status': '訊息已標記為已讀'})

@app.route('/')
def home():
    return "chat API is running."

if __name__ == '__main__':
    app.run(host='0.0.0.0', port=5003, threaded=True) 
