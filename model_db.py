import mysql.connector

def get_diary_content(diary_id):
    db = mysql.connector.connect(
        host="localhost",
        user="root",
        password="",
        database="smiley"
    )

    cursor = db.cursor()
    query = "SELECT content FROM diaries WHERE id = %s"
    cursor.execute(query, (diary_id,))
    result = cursor.fetchone()
    cursor.close()
    db.close()
    
    if result:
        return result[0]
    else:
        return None

def save_db_analysis(date, emotion_counts, diary_id):
    db = mysql.connector.connect(
        host="localhost",
        user="root",
        password="",
        database="smiley"
    )

    cursor = db.cursor()
    query = """
    INSERT INTO analysis (diary_id, date, sadness, disgust, `like`, anger, happiness, other)
    VALUES (%s, %s, %s, %s, %s, %s, %s, %s)
    """
    data = (diary_id, date, emotion_counts[2], emotion_counts[3], emotion_counts[1], emotion_counts[4], emotion_counts[5], emotion_counts[0])
    cursor.execute(query, data)
    db.commit()
    cursor.close()
    db.close()

def save_db_diaries_mon_ang(diary_id, monster_id, angel_id):
    db = mysql.connector.connect(
        host="localhost",
        user="root",
        password="",
        database="smiley"
    )

    cursor = db.cursor()
    query = """
    INSERT INTO diaries_mon_ang (diary_id, monster_id, angel_id)
    VALUES (%s, %s, %s)
    """
    data = (diary_id, monster_id, angel_id)
    cursor.execute(query, data)
    db.commit()
    cursor.close()
    db.close()

    