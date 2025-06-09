import sys
import json
from fuzzywuzzy import fuzz
import jellyfish

# Load JSON data passed from PHP
data = json.loads(sys.argv[1])

# Sample fields
first_name = data['first_name']
last_name = data['last_name']
birth_date = data['birth_date']
compare_to = data['compare_to']  # List of dicts with similar entries

results = []

for person in compare_to:
    fn_score = fuzz.ratio(first_name.lower(), person['first_name'].lower())
    ln_score = fuzz.ratio(last_name.lower(), person['last_name'].lower())
    name_similarity = (fn_score + ln_score) / 2

    jaro = jellyfish.jaro_winkler_similarity(first_name + last_name, person['first_name'] + person['last_name'])

    if name_similarity > 85 or jaro > 0.90:
        results.append({
            'match_id': person['beneficiary_id'],
            'similarity': name_similarity,
            'jaro_winkler': jaro
        })

print(json.dumps(results))
