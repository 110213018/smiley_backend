import mysql.connector

def save_db_analysis(user_id, date, statistics, angel, monster):
    try:
        db = mysql.connector.connect(
            host="localhost",
            user="root",
            password="",
            database="smiley"
        )
        cursor = db.cursor()

        query = """
        INSERT INTO analysis (user_id, date, sadness, disgust, `like`, anger, happiness, other, angel, monster)
        VALUES (%s, %s, %s, %s, %s, %s, %s, %s, %s, %s)
        """
        data = (
            user_id, 
            date, 
            statistics[0],  # sadness  
            statistics[1],  # disgust  
            statistics[2],  # like  
            statistics[3],  # anger  
            statistics[4],  # happiness  
            statistics[5],   # other
            angel,
            monster  
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
