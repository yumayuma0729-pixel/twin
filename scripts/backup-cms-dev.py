#!/usr/bin/env python3
"""Private local-development backup; never prints SQL or credentials."""
from pathlib import Path
from datetime import datetime, timezone
import gzip, subprocess
root=Path(__file__).resolve().parents[1]
out=root/'cms/storage/backups'/datetime.now(timezone.utc).strftime('%Y%m%dT%H%M%SZ')
out.mkdir(parents=True,exist_ok=False)
out.chmod(0o700)
dump=subprocess.run(['docker','exec','twin-cms-db-1','sh','-c','MYSQL_PWD="$MARIADB_PASSWORD" mariadb-dump --single-transaction --hex-blob -u"$MARIADB_USER" "$MARIADB_DATABASE"'],check=True,capture_output=True).stdout
with gzip.open(out/'database.sql.gz','wb') as stream:stream.write(dump)
media=subprocess.run(['docker','exec','twin-cms-app-1','tar','-czf','-','-C','/srv/twin/storage','media'],check=True,capture_output=True).stdout
(out/'media.tar.gz').write_bytes(media)
for file in out.iterdir():file.chmod(0o600)
print(out)
