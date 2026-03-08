import sys
import json

from analyzers.loc import count_lines
from analyzers.complexity import analyze_complexity
from analyzers.git_metrics import commit_metrics

repo_path = sys.argv[1]
extensions = sys.argv[2].split(',') if len(sys.argv) > 2 else []

result = {}

result.update(count_lines(repo_path, extensions))
result.update(analyze_complexity(repo_path))
result.update(commit_metrics(repo_path, extensions))

print(json.dumps(result))