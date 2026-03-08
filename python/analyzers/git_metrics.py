from pydriller import Repository

def commit_metrics(repo_path):

    commits = 0
    authors = set()
    file_changes = {}

    try:
        for commit in Repository(repo_path).traverse_commits():
            commits += 1
            authors.add(commit.author.name)

            for mod in commit.modified_files:
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