import glob, os

res = []
for path in sorted(glob.glob('src/app/Views/*.php')):
    try:
        with open(path, encoding='utf-8', errors='ignore') as f:
            for i, l in enumerate(f, 1):
                if '<form' in l.lower() and 'data-validate' not in l:
                    res.append((path, i, l.rstrip()))
    except Exception:
        pass

print(len(res))
for r in res:
    print(r[0] + ':' + str(r[1]) + ':' + r[2])
