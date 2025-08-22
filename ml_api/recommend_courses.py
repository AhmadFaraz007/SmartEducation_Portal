import sys
import json
import mysql.connector

student_id = int(sys.argv[1])

# Connect to MySQL (edit credentials if needed)
conn = mysql.connector.connect(
    host="localhost",
    user="root",
    password="",  # your MySQL password
    database="ai_crs"
)
cursor = conn.cursor(dictionary=True)

try:
    # Get student's interests
    cursor.execute("SELECT interests, completed_courses FROM students WHERE id = %s", (student_id,))
    student = cursor.fetchone()

    if not student:
        print(json.dumps([]))
        exit()

    interests = student["interests"].split(",")
    completed = student["completed_courses"].split(",") if student["completed_courses"] else []

    # Find relevant courses matching interests and not completed
    query = """
        SELECT DISTINCT course_code, title 
        FROM courses
        WHERE 
            (""" + " OR ".join(["description LIKE %s OR title LIKE %s"] * len(interests)) + """)
            AND id NOT IN (""" + ",".join(["%s"] * len(completed)) + """)
    """

    params = []
    for keyword in interests:
        keyword = keyword.strip()
        params.append(f"%{keyword}%")
        params.append(f"%{keyword}%")

    for cid in completed:
        params.append(cid.strip())

    cursor.execute(query, tuple(params))
    results = cursor.fetchall()

    print(json.dumps(results))

except Exception as e:
    print(json.dumps([]))  # Optional: log str(e) to a file for debugging

finally:
    cursor.close()
    conn.close()
