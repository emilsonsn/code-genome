from pydriller import Repository

def is_code_file(filename, extensions):
    if not extensions:
        return True
    ext = filename.rsplit('.', 1)[-1].lower() if '.' in filename else ''
    return ext in extensions

def commit_metrics(repo_path, extensions=None):
    if extensions is None:
        extensions = []

    commits = 0
    authors = set()
    file_changes = {}

    try:
        for commit in Repository(repo_path).traverse_commits():
            commits += 1
            authors.add(commit.author.name)

            for mod in commit.modified_files:
                if is_code_file(mod.filename, extensions):
                    file_changes[mod.filename] = file_changes.get(mod.filename, 0) + 1

    except:
        return {
            "total_commits": 0,
            "contributors": 0,
            "hotspot_files": []
        }

    hotspots = sorted(
        file_changes.items(),
        key=lambda x: x[1],
        reverse=True
    )[:5]

    hotspots = [
        {"file": file, "changes": changes}
        for file, changes in hotspots
    ]

    return {
        "total_commits": commits,
        "contributors": len(authors),
        "hotspot_files": hotspots
    }