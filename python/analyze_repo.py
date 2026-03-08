import sys
import json

from analyzers.loc import count_lines
from analyzers.file_metrics import largest_files
from analyzers.complexity import analyze_complexity
from analyzers.git_metrics import commit_metrics

repo_path = sys.argv[1]

result = {}

result.update(count_lines(repo_path))
result.update(largest_files(repo_path))
result.update(analyze_complexity(repo_path))
result.update(commit_metrics(repo_path))

print(json.dumps(result))