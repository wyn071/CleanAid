import pandas as pd
import json
import sys
import math

# Get file path from argument
if len(sys.argv) < 2:
    print(json.dumps({"error": "Missing file path"}))
    sys.exit()

file_path = sys.argv[1]

# Read file
try:
    df = pd.read_csv(file_path)
except Exception as e:
    print(json.dumps({"error": str(e)}))
    sys.exit()

# Strip strings
df = df.applymap(lambda x: x.strip() if isinstance(x, str) else x)

# Record count
total_records = len(df)

# Find missing data rows
missing_rows = df[df.isnull().any(axis=1)].to_dict(orient='records')

# Dummy placeholders for demonstration:
exact_duplicates = []
fuzzy_duplicates = []
sounds_like_duplicates = []

# Example logic (you can replace with your real algorithms)
# Let's say rows 0, 2, 10 are fuzzy duplicates
fuzzy_duplicates = [
    {"row1_index": 0, "row2_index": 2, "similarity": 97},
    {"row1_index": 0, "row2_index": 10, "similarity": 92},
    {"row1_index": 2, "row2_index": 10, "similarity": 92},
    {"row1_index": 3, "row2_index": 4, "similarity": 91},
    {"row1_index": 6, "row2_index": 7, "similarity": 99},
]

# Sounds-like demo
sounds_like_duplicates = [
    {"row1_index": 0, "row2_index": 2, "phonetic_code": "ABC"},
    {"row1_index": 3, "row2_index": 4, "phonetic_code": "XYZ"},
]

# Summary
result = {
    "summary": {
        "total_records": total_records,
        "missing_count": len(missing_rows),
        "exact_duplicates_count": len(exact_duplicates),
        "fuzzy_duplicates_count": len(fuzzy_duplicates),
        "sounds_like_count": len(sounds_like_duplicates)
    },
    "missing_data": missing_rows,
    "exact_duplicates": exact_duplicates,
    "fuzzy_duplicates": fuzzy_duplicates,
    "sounds_like_duplicates": sounds_like_duplicates
}

# ✅ Sanitize NaN, inf, -inf so PHP's json_decode won't fail
def sanitize(obj):
    if isinstance(obj, dict):
        return {k: sanitize(v) for k, v in obj.items()}
    elif isinstance(obj, list):
        return [sanitize(v) for v in obj]
    elif isinstance(obj, float) and (math.isnan(obj) or math.isinf(obj)):
        return None
    return obj

clean_output = sanitize(result)
print(json.dumps(clean_output))
