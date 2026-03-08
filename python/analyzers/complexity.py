import subprocess
import json

def analyze_complexity(repo_path):
    try:
        result = subprocess.run(
            ["radon", "cc", repo_path, "-j"],
            capture_output=True,
            text=True
        )

        data = json.loads(result.stdout)

        worst = []

        for file, functions in data.items():
            for func in functions:
                worst.append({
                    "file": file,
                    "name": func["name"],
                    "complexity": func["complexity"]
                })

        worst.sort(key=lambda x: x["complexity"], reverse=True)

        return {
            "most_complex_functions": worst[:5]
        }

    except:
        return {
            "most_complex_functions": []
        }