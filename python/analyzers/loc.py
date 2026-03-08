from pathlib import Path

def is_code_file(filename, extensions):
    if not extensions:
        return True
    ext = filename.rsplit('.', 1)[-1].lower() if '.' in filename else ''
    return ext in extensions

def count_lines(repo_path, extensions=None):
    if extensions is None:
        extensions = []

    total = 0
    files = 0

    for file in Path(repo_path).rglob("*"):
        if file.is_file() and is_code_file(file.name, extensions):
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