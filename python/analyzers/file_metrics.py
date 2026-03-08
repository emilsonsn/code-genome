from pathlib import Path

def largest_files(repo_path):
    files = []

    for file in Path(repo_path).rglob("*"):
        if file.is_file():
            try:
                with open(file, encoding="utf-8", errors="ignore") as f:
                    lines = sum(1 for _ in f)

                files.append({
                    "file": str(file.name),
                    "lines": lines
                })
            except:
                pass

    files.sort(key=lambda x: x["lines"], reverse=True)

    return {
        "largest_files_by_loc": files[:5]
    }