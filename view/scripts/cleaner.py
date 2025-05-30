import sys
import json
from fuzzywuzzy import fuzz
import jellyfish

# Input from PHP
data = json.loads(sys.argv[1])
target = data['target']
compare_to = data['compare_to']

results = []

def full_name(entry):
    return f"{entry['first_name']} {entry.get('middle_name', '')} {entry['last_name']} {entry.get('ext_name', '')}".strip()

target_name = full_name(target)

for person in compare_to:
    person_name = full_name(person)

    # Fuzzy match (name)
    fuzz_score = fuzz.token_set_ratio(target_name.lower(), person_name.lower())

    # Jaro-Winkler (name)
    jw_score = jellyfish.jaro_winkler_similarity(target_name.lower(), person_name.lower())

    # Birthdate match boost
    birth_match = target['birth_date'] == person['birth_date']

    if fuzz_score > 85 or jw_score > 0.90:
        results.append({
            'match_id': person['beneficiary_id'],
            'similarity': fuzz_score,
            'jaro_winkler': round(jw_score, 3),
            'birth_match': birth_match
        })

print(json.dumps(results))
