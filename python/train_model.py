import pandas as pd
from sklearn.linear_model import LogisticRegression
import pickle

# Step 1: Create dummy student data
data = {
    'cgpa': [2.5, 3.0, 3.5, 2.8, 3.9, 3.2],
    'interest_level': [7, 8, 9, 5, 10, 6],
    'recommended': [0, 1, 1, 0, 1, 1]  # 1 = recommended, 0 = not recommended
}
df = pd.DataFrame(data)

# Step 2: Split into features (X) and labels (y)
X = df[['cgpa', 'interest_level']]
y = df['recommended']

# Step 3: Train a simple model
model = LogisticRegression()
model.fit(X, y)

# Step 4: Save the model as .pkl file
with open('course_recommendation_model.pkl', 'wb') as f:
    pickle.dump(model, f)

print("✅ Model trained and saved as 'course_recommendation_model.pkl'")
