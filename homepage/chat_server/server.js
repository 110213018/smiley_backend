const express = require('express');
const http = require('http');
const socketIo = require('socket.io');
const mysql = require('mysql');

const app = express();
const server = http.createServer(app);
const io = socketIo(server);

// 連接 MySQL 資料庫
const db = mysql.createConnection({
  host: 'localhost', // 你的 MySQL 資料庫主機
  user: 'root',      // 你的 MySQL 資料庫用戶名
  password: '', // 你的 MySQL 資料庫密碼
  database: 'smiley', // 你的資料庫名稱
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

  // 監聽來自用戶的消息
  socket.on('send_message', data => {
    const { user, message } = data;

    // 將消息存儲到 MySQL
    const query = 'INSERT INTO messages (user, message) VALUES (?, ?)';
    db.query(query, [user, message], (err, result) => {
      if (err) {
        console.error('無法存儲消息:', err);
        return;
      }

      // 向 homeChat 命名空間中的所有客戶端廣播消息
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
