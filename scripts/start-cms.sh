#!/bin/sh
set -eu
cd "$(dirname "$0")/.."
# Isolate Docker client cache in a writable path; do not copy credential files.
export DOCKER_CONFIG="${TWIN_DOCKER_CONFIG:-/tmp/twin-docker-config}"
mkdir -p "$DOCKER_CONFIG"
python3 - <<'PY'
from pathlib import Path
import secrets
file=Path('cms/dev/.env')
if not file.exists():
 file.write_text('\n'.join(key+'='+secrets.token_hex(32) for key in ['TWIN_DB_PASSWORD','TWIN_DB_ROOT_PASSWORD','TWIN_AUTH_KEY'])+'\n')
 file.chmod(0o600)
PY
docker compose -p twin-cms -f cms/dev/compose.yaml up -d --build
docker compose -p twin-cms -f cms/dev/compose.yaml exec -T app php bin/console.php migrate
python3 - <<'PY'
from urllib.request import urlopen
with urlopen('http://127.0.0.1:8080/admin/login/',timeout=15) as response:
 assert response.status==200 and '管理画面にログイン' in response.read().decode()
print('PHP + MySQL CMS: login page readiness PASS')
PY
