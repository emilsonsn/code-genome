from pathlib import Path

def count_lines(repo_path):
    total = 0
    files = 0

    for file in Path(repo_path).rglob("*"):
        if file.is_file():
            try:
                with open(file, encoding="utf-8", errors="ignore") as f:
                    lines = sum(1 for _ in f)
                    total += lines
                    files += 1
            except:
                pass

    return {
        "total_lines_of_code": total,
        "files_analyzed": files
    }