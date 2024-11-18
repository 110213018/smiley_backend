const express = require('express');
const http = require('http');
const socketIo = require('socket.io');
const cors = require('cors'); // 引入 cors 模塊
const mysql = require('mysql');

const app = express();
app.use(cors()); // 使用 cors 中間件

const server = http.createServer(app);
const io = socketIo(server, {
  cors: {
    origin: "*", // 允許來自所有來源的請求，你可以根據需要限制來源
    methods: ["GET", "POST"]
  }
});

// 連接 MySQL 資料庫
const db = mysql.createConnection({
  host: 'localhost',
  user: 'root',
  password: '',
  database: 'smiley',
});

db.connect(err => {
  if (err) {
    console.error('無法連接到資料庫:', err);
    return;
  }
  console.log('已成功連接到 MySQL');
});

// 使用命名空間 /homeChat
const homeChat = io.of('/homeChat');

homeChat.on('connection', socket => {
  console.log('用戶已連接到 homeChat 命名空間:', socket.id);

  socket.on('send_message', data => {
    const { user, message } = data;

    // 將消息存儲到 MySQL
    const query = 'INSERT INTO home_chat (user_id, context) VALUES (?, ?)';
    db.query(query, [user, message], (err, result) => {
      if (err) {
        console.error('無法存儲消息:', err);
        return;
      }

      homeChat.emit('receive_message', { user, message });
    });
  });

  socket.on('disconnect', () => {
    console.log('用戶已斷開:', socket.id);
  });
});

const PORT = 3000;
server.listen(PORT, () => {
  console.log(`服務器正在運行，端口號: ${PORT}`);
});
