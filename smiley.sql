-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- 主機： 127.0.0.1
-- 產生時間： 2024-08-20 04:41:35
-- 伺服器版本： 10.4.32-MariaDB
-- PHP 版本： 8.0.30

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- 資料庫： `smiley`
--

-- --------------------------------------------------------

--
-- 資料表結構 `analysis`
--

CREATE TABLE `analysis` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `diary_id` int(11) NOT NULL,
  `date` date NOT NULL,
  `sadness` int(11) NOT NULL,
  `disgust` int(11) NOT NULL,
  `like` int(11) NOT NULL,
  `anger` int(11) NOT NULL,
  `happiness` int(11) NOT NULL,
  `other` int(11) NOT NULL,
  `angel` varchar(50) NOT NULL,
  `monster` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='每一篇日記的文字分析結果';

--
-- 傾印資料表的資料 `analysis`
--

INSERT INTO `analysis` (`id`, `user_id`, `diary_id`, `date`, `sadness`, `disgust`, `like`, `anger`, `happiness`, `other`, `angel`, `monster`) VALUES
(3, 23, 3, '2024-07-15', 0, 0, 0, 0, 100, 0, '', ''),
(5, 23, 8, '2024-08-04', 40, 30, 0, 15, 0, 15, '', ''),
(6, 23, 9, '2024-08-02', 20, 5, 0, 15, 5, 60, '', ''),
(7, 23, 10, '2024-08-01', 0, 5, 30, 5, 25, 35, '', ''),
(9, 23, 12, '2024-07-30', 10, 10, 5, 50, 0, 25, '', ''),
(10, 23, 13, '2024-07-29', 0, 5, 60, 0, 5, 30, '', ''),
(11, 23, 14, '2024-08-07', 35, 20, 4, 10, 6, 25, '', '');

-- --------------------------------------------------------

--
-- 資料表結構 `chats`
--

CREATE TABLE `chats` (
  `id` int(11) NOT NULL COMMENT '每一則聊天訊息的 id',
  `user_id` int(11) NOT NULL COMMENT '訊息發送者',
  `friend_id` int(11) NOT NULL COMMENT '訊息接受者',
  `time` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp() COMMENT '發送訊息時間',
  `content` mediumtext NOT NULL COMMENT '訊息內容'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='聊天訊息列表';

-- --------------------------------------------------------

--
-- 資料表結構 `comments`
--

CREATE TABLE `comments` (
  `id` int(11) NOT NULL COMMENT '每一條評論的 id',
  `user_id` int(11) NOT NULL COMMENT '發送評論的人的 id',
  `post_id` int(11) NOT NULL COMMENT '評論發在哪個貼文',
  `emoji_id` int(11) NOT NULL COMMENT '評論發送鍵 (表情貼) 的 id',
  `content` mediumtext DEFAULT NULL COMMENT '評論，因為可以只按表情貼，所以可為 null'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='貼文評論';

-- --------------------------------------------------------

--
-- 資料表結構 `diaries`
--

CREATE TABLE `diaries` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `content` mediumtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `date` date NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- 傾印資料表的資料 `diaries`
--

INSERT INTO `diaries` (`id`, `user_id`, `content`, `date`) VALUES
(1, 28, '今天過得好開心~久違的跟朋友出去玩。', '2024-06-20'),
(2, 22, '她說：我是這世界上唯一懂她的人。 我們說著很多只有我們彼此知道的小秘密 只有當過那個人女朋友才會懂的事情  沒有討人厭的勾心鬥角 也沒有爭奪男人的狗血劇情 我們成為了無話不談的好姐妹 她絲毫不忌諱那些我們的過去式 而我也是真心祝福著他們的現在進行式  “他們能在一起，實在太好了呢～” 這是我內心唯一的想法 我真的覺得他們好適合彼此 我也很喜歡這個女孩  而天真可愛的她 有什麼事都會跑來問我 不管是親情友情還是愛情 就連性事也能討論 買東西穿衣服也會尋求我意見 甚至用我的會員折扣結帳  不管旁人再怎麼難以置信 我們就是這樣的相處模式 欣賞著彼此 喜歡著彼此 幫助著彼此 感覺 少了一個男朋友但多了一個女朋友 😂  原來前女友的存在 也可以如此這般和平友善 真的是連我自己都意想不到的發展   她是我前男友大學補習班打工的學生 感覺得出來我前男友特別疼她 在考前會特別到麥當勞個別教她 也常常一起出去吃飯 還會送飲料到她家 這些我都是知情的 也會陪著一起去 而我當時覺得這個妹妹好懂事好成熟好穩重 是真的也滿喜歡她的 後來 我們和平分手後沒多久他們就在一起了 我完全不意外 也覺得他們很適合彼此  五年了 我們還是依舊保持著聯絡 我也已經結婚有自己的家庭與小孩了  希望我的故事分享給有緣看到的人 可以一起成為更好的人 當個優質的前女友 也當個沒有負面情緒的現任女友  如果當事人有看到 那就讓我順便跟妳表白吧哈哈 我真的超喜歡妳的啦～', '2024-07-02'),
(3, 25, '剛才、本人只是去上個廁所，一出來就看到女友縮在床上抱著被子，我以為她又身體不舒服了，因為她剛結束生理期。  我一過去，就看到她哭了、還帶有啜泣的那種。  我趕緊問她怎麼了是不是不舒服、肚子痛還是出了什麼事。  結果她哭了好一會，終於稍微平靜一下，她跟我說  我想吃蛋糕 我想吃蛋糕 我想吃蛋糕  對、她因為想吃蛋糕想吃到哭。😀', '2024-07-07'),
(4, 23, '在臉書看到這隻可憐的狗狗 為什麼拍懾的當下不先救救牠呢？  不是先救狗狗 再請大家捐款救救牠嗎？難道拍攝與捐款比救援重要？ 看了好難過 希望大家的捐款可以真正的幫助這些可憐的狗狗們..', '2024-07-08'),
(5, 24, '嗚嗚 今天在廁所遇到壁虎這可怕的生物， 我一直覺得壁虎比蟑螂噁心🤮 蟑螂打死了就像餅乾一樣碎掉的感覺，我覺得還行。但是壁虎是軟軟的，跟青蛙類似的觸感覺得超噁心，無法接受啊！ ————————————————— 哀...早知道就去買超強力的殺蟲劑了', '2024-07-10'),
(6, 28, '今天跟爸媽久違的一起出門吃晚餐~ 吃的竟然還是我最愛的那一間義大利麵餐廳! 嗚嗚 吃美食真的好幸福🥰', '2024-07-11'),
(7, 26, '我的前任 離開我 選擇了一個家境好的名校女孩  我的朋友勸過我放下他  但其實他背叛我的時候 我就頭也不回的離開了  我想我真正的難過還是跟原生家庭有關  -  我算是可以唸書的小孩， 不是頂聰明， 但成績也不差， 大約到國中還能校排前20。  但我家一直有兩個問題： 就是窮，還有言語及肢體暴力  窮，所以除了學校以外，我沒有其他資源，這沒關係。  但我吃飯是要看父母臉色的， 以民國98-104年的物價來說， 我整個中學時期每月有5000生活費， 含所有吃喝穿用。  偶爾父親沒工作會拿不到這5000  這是我需要腆著臉找媽媽蹭飯， 她有時給，有時不給，飯桌上也需要接受她的歇斯底里。  經常在他們的腳步聲接近時，我就開始戒備，害怕挨打。至於原因，有時候是因為排水孔有頭髮。  -  後來我填了縣裡的第二志願，因為離家比較近。但還是足足有10公里遠。  為了省車費，高中時期我每天騎20公里單車通勤，腳踏車常掉鏈，我也常遲到，累積不少警告，別問哪家品質那麼差，那是我在二手店用1000塊買的。  因此，我需要更多食物，與睡眠，但家裡時常有紛爭，比方突然關燈嚇人，砸東西，莫名的爭吵與尖叫，所以我經常睡不飽，上課也經常不自覺睡著。  順帶一提，瞌睡及遲到問題讓班導很抓狂，她一直很疑惑我為什麼不買新腳踏車，拿這個當遲到藉口，我也不願意多解釋。  就這麼到高三，高三的時候，我想，要麼死，要嘛搬走，我在學校附近租了5000的套房想拼拼學測，但為了房租，我開始打工。  那時我的時薪是120。  就在我以為一切要稍微正常的時候， 我爸因為長期工作傷害，病倒了。  住到學校附近的醫院，我需要給他送飯，雖然是用他的錢買，但是是我的時間。  再後來，我爸給的5000生活費徹底斷了。因為他動完手術幾乎就算殘廢了，那是我學測前一個月。  -  我因為分數不高，填到外縣市的私校，因為親戚在那，他們表示願意爾偶幫我，但也只是偶爾蹭飯。  我還記得大學面試之前，我為了報名費，交通費，服裝費，省吃儉用了很久。  私校學費不便宜，我只能學貸，要辦貸款的時候，我爸媽卻因為感情不好，不願意共同出面當保證人，最後是我用求的，用印鑑證明的方式才過。  到了大學，我的腦袋沒一刻不在為錢而焦慮，所以我開始打工，又為了打工買了一台二手機車。  大概一年半後，我就休學了。 大學沒畢業這件事，也成為我揮之不去的心魔。  接觸社會後我碰過飲料店、銷售、業務、甚至還有，兩個禮拜的酒店小姐。  我開始了正常的人生。  我以為這樣我就都忘了，都過去了！  但在我的前任離開我，轉投另一個女孩身邊的時候，  我發現，我什麼都記得，只是太痛苦，所以被我塵封了。  2024/06/13 03:10 願我以後能過得更好更幸福，即便很困難。', '2024-07-13'),
(35, 23, 'hello 🤗', '2024-08-14'),
(36, 23, 'my name is 😊', '2024-08-15'),
(40, 23, 'hello hoho', '2024-08-18'),
(41, 23, 'today is a beautiful day 💞🌞', '2024-08-19');

-- --------------------------------------------------------

--
-- 資料表結構 `emojis`
--

CREATE TABLE `emojis` (
  `id` int(11) NOT NULL,
  `emoji` varchar(255) NOT NULL COMMENT 'emoji 圖片位址'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='發貼文評論時的表情貼';

-- --------------------------------------------------------

--
-- 資料表結構 `friends`
--

CREATE TABLE `friends` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `friend_id` int(11) NOT NULL,
  `status` tinyint(1) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='朋友列表';

--
-- 傾印資料表的資料 `friends`
--

INSERT INTO `friends` (`id`, `user_id`, `friend_id`, `status`) VALUES
(14, 39, 23, 1),
(15, 39, 24, 0),
(17, 24, 23, 1),
(23, 22, 26, 0),
(25, 22, 25, 1),
(26, 24, 22, 1);

-- --------------------------------------------------------

--
-- 資料表結構 `informs`
--

CREATE TABLE `informs` (
  `id` int(11) NOT NULL COMMENT '每一條通知的 id',
  `user_id` int(11) NOT NULL COMMENT '被通知者',
  `friend_id` int(11) NOT NULL COMMENT '留言者/請求加好友者/允許加好友者',
  `inform` mediumtext NOT NULL COMMENT '通知內容',
  `date` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp() COMMENT '通知日期與時間'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='通知中心';

-- --------------------------------------------------------

--
-- 資料表結構 `posts`
--

CREATE TABLE `posts` (
  `id` int(11) NOT NULL COMMENT '每一則貼文的 id',
  `user_id` int(11) NOT NULL COMMENT '發送貼文的人',
  `text_color` varchar(11) NOT NULL COMMENT '貼文顏色',
  `background_color` varchar(11) NOT NULL,
  `monster` varchar(50) DEFAULT NULL COMMENT '情緒小怪獸，可為null',
  `angel` varchar(50) DEFAULT NULL COMMENT '情緒小天使，可為 null',
  `title` varchar(20) NOT NULL COMMENT '貼文標題(大小暫定 10)',
  `date` date NOT NULL COMMENT '貼文發布日期',
  `content` tinytext NOT NULL COMMENT '貼文文章\n'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='貼文';

--
-- 傾印資料表的資料 `posts`
--

INSERT INTO `posts` (`id`, `user_id`, `text_color`, `background_color`, `monster`, `angel`, `title`, `date`, `content`) VALUES
(9, 39, '0xffffff53', '0xff222222', 'monster_3.png', '', 'hi', '2024-08-17', 'hiiiiii'),
(15, 24, '0xffeca8a4', '0xff374295', 'monster_1.png', '', 'happy', '2024-08-18', 'happy 😊'),
(16, 24, '0xffeca8a4', '0xff374295', '', 'monster_2.png', 'happy', '2024-08-15', 'happy 😊'),
(17, 23, '0xff4285f4', '0xffa1e0e4', '', 'monster_1.png', 'hi', '2024-08-14', 'hiiiiii'),
(31, 23, '0xff6b6c39', '0xffa7ba89', '', 'monster_3.png', 'happy', '2024-08-17', 'happy 😊'),
(32, 23, '0xff6b6c39', '0xffa7ba89', 'monster_4.png', '', 'happy', '2024-08-18', 'happy 😊'),
(36, 23, '0xffffffff', '0xffcd95bc', '', 'monster_2.png', 'sunny', '2024-08-19', 'hohoho');

-- --------------------------------------------------------

--
-- 資料表結構 `robot_chats`
--

CREATE TABLE `robot_chats` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `time` datetime NOT NULL,
  `robot_content` text NOT NULL,
  `user_content` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- 資料表結構 `trigger_bert`
--

CREATE TABLE `trigger_bert` (
  `id` int(11) NOT NULL,
  `trigger_time` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- 傾印資料表的資料 `trigger_bert`
--

INSERT INTO `trigger_bert` (`id`, `trigger_time`) VALUES
(0, '2024-07-16 10:55:32');

-- --------------------------------------------------------

--
-- 資料表結構 `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL COMMENT '每一個使用者的 id',
  `firebase_user_id` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `name` varchar(255) NOT NULL COMMENT 'user name',
  `photo` varchar(255) NOT NULL COMMENT 'user 大頭貼'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='使用者列表';

--
-- 傾印資料表的資料 `users`
--

INSERT INTO `users` (`id`, `firebase_user_id`, `name`, `photo`) VALUES
(22, '5g4SccT1sUej1hdVbsQ6wwZ1Mcy2', 'aa', 'default_avatar_1.png'),
(23, 'HYWb1QcyovXFOf326oNu6WQa2qr2', 'bbb', '1000000034.jpg'),
(24, 'XL9XfIFe8EevNOk6VL9Bm5SzFyU2', 'ccc', 'default_avatar.png'),
(25, 'oHC1OWt7t0f1YcAdezJxEuPCeBK2', 'ddd', '1000000035.jpg'),
(26, '6iDUHcdUwzcPyxSO1G1h7vvSKrk1', 'eee', 'default_avatar.png'),
(28, 'cdcfJaXZDxNHHCdBFNrlcAn4hvI2', 'fff', 'default_avatar.png'),
(30, 'Wd5lLyZTZThAqLySxqQNjWOWy3A2', 'rr', 'default_avatar_4.png'),
(31, 'PGy6u8VsJkhEJz2hA4ps9kFsN2h1', 'ttt', 'default_avatar_2.png'),
(39, 'vuiFiuM0ZgSTrZ9p3MiPZn2Apg83', 'ww', '1000000034.jpg'),
(43, 'N5aKoenVsyRFq8vdgwi3KXD26yS2', 'ppp', 'default_avatar_4.png'),
(44, 'omghoS8ZLIYDI2lX2583QeRKJ0P2', 'ii', '1000000033.jpg'),
(46, 'raqC1DEPcARidayEs5S6ETZwGT22', 'qq', 'default_avatar_5.png'),
(47, 'gyze3uwjTmaLFMF4Kmkc7mkDILr1', 'vv', 'default_avatar_4.png');

--
-- 已傾印資料表的索引
--

--
-- 資料表索引 `analysis`
--
ALTER TABLE `analysis`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `id` (`id`);

--
-- 資料表索引 `chats`
--
ALTER TABLE `chats`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `id` (`id`);

--
-- 資料表索引 `comments`
--
ALTER TABLE `comments`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `id` (`id`);

--
-- 資料表索引 `diaries`
--
ALTER TABLE `diaries`
  ADD PRIMARY KEY (`id`);

--
-- 資料表索引 `emojis`
--
ALTER TABLE `emojis`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `id` (`id`);

--
-- 資料表索引 `friends`
--
ALTER TABLE `friends`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `id` (`id`);

--
-- 資料表索引 `informs`
--
ALTER TABLE `informs`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `id` (`id`);

--
-- 資料表索引 `posts`
--
ALTER TABLE `posts`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `id` (`id`);

--
-- 資料表索引 `robot_chats`
--
ALTER TABLE `robot_chats`
  ADD PRIMARY KEY (`id`);

--
-- 資料表索引 `trigger_bert`
--
ALTER TABLE `trigger_bert`
  ADD PRIMARY KEY (`id`);

--
-- 資料表索引 `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `id` (`id`);

--
-- 在傾印的資料表使用自動遞增(AUTO_INCREMENT)
--

--
-- 使用資料表自動遞增(AUTO_INCREMENT) `analysis`
--
ALTER TABLE `analysis`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- 使用資料表自動遞增(AUTO_INCREMENT) `chats`
--
ALTER TABLE `chats`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT COMMENT '每一則聊天訊息的 id';

--
-- 使用資料表自動遞增(AUTO_INCREMENT) `comments`
--
ALTER TABLE `comments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT COMMENT '每一條評論的 id';

--
-- 使用資料表自動遞增(AUTO_INCREMENT) `diaries`
--
ALTER TABLE `diaries`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=42;

--
-- 使用資料表自動遞增(AUTO_INCREMENT) `emojis`
--
ALTER TABLE `emojis`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- 使用資料表自動遞增(AUTO_INCREMENT) `friends`
--
ALTER TABLE `friends`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=27;

--
-- 使用資料表自動遞增(AUTO_INCREMENT) `informs`
--
ALTER TABLE `informs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT COMMENT '每一條通知的 id';

--
-- 使用資料表自動遞增(AUTO_INCREMENT) `posts`
--
ALTER TABLE `posts`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT COMMENT '每一則貼文的 id', AUTO_INCREMENT=37;

--
-- 使用資料表自動遞增(AUTO_INCREMENT) `robot_chats`
--
ALTER TABLE `robot_chats`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- 使用資料表自動遞增(AUTO_INCREMENT) `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT COMMENT '每一個使用者的 id', AUTO_INCREMENT=48;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
