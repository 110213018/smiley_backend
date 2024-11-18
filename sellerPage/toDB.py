import mysql.connector
# Function to get a database connection
def get_db_connection():
    return mysql.connector.connect(
        host="localhost",
        user="root",
        password="",
        database="smiley"
    )

# Function to fetch the latest 30 messages
def fetch_latest_messages(sender_id, receiver_id):
    try:
        conn = get_db_connection()
        cursor = conn.cursor(dictionary=True)
        cursor.execute('''
            SELECT * FROM messages
            WHERE (sender_id = %s AND receiver_id = %s) OR (sender_id = %s AND receiver_id = %s)
            ORDER BY timestamp ASC LIMIT 30
        ''', (sender_id, receiver_id, receiver_id, sender_id))
        messages = cursor.fetchall()
        # print(messages)
        return messages
    finally:
        cursor.close()
        conn.close()

# Function to save a new message
def save_message(sender_id, receiver_id, message):
    try:
        conn = get_db_connection()
        cursor = conn.cursor()
        cursor.execute('''
            INSERT INTO messages (sender_id, receiver_id, message, is_read)
            VALUES (%s, %s, %s, %s)
        ''', (sender_id, receiver_id, message, False))
        conn.commit()
    finally:
        cursor.close()
        conn.close()

# Function to mark messages as read
def mark_messages_as_read(sender_id, receiver_id):
    try:
        conn = get_db_connection()
        cursor = conn.cursor()
        cursor.execute('''
            UPDATE messages
            SET is_read = TRUE
            WHERE sender_id = %s AND receiver_id = %s AND is_read = FALSE
        ''', (sender_id, receiver_id))
        conn.commit()
    finally:
        cursor.close()
        conn.close()
