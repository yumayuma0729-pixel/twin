#!/usr/bin/env python3
"""Build a secret-free Xserver package. Runtime data and local.php are excluded."""
from pathlib import Path
from zipfile import ZipFile, ZIP_DEFLATED
root=Path(__file__).resolve().parents[1]
out=root/'dist/twin-assist-xserver.zip'
out.parent.mkdir(exist_ok=True)
with ZipFile(out,'w',ZIP_DEFLATED) as archive:
 for source in sorted((root/'public').rglob('*')):
  if source.is_file():archive.write(source,Path('public_html')/source.relative_to(root/'public'))
 for folder in ['app','bin','database']:
  for source in sorted((root/'cms'/folder).rglob('*')):
   if source.is_file():archive.write(source,Path('twin-cms')/source.relative_to(root/'cms'))
 archive.write(root/'cms/config/example.php','twin-cms/config/example.php')
 archive.writestr('twin-cms/storage/.gitkeep','')
 for source in sorted((root/'cms/public').iterdir()):
  if not source.is_file():continue
  if source.name=='index.php':
   text=source.read_text().replace("require dirname(__DIR__).'/app/bootstrap.php';","require dirname(__DIR__).'/twin-cms/app/bootstrap.php';")
   archive.writestr('public_html/index.php',text)
  else:archive.write(source,Path('public_html')/source.name)
 archive.write(root/'docs/CMS_Deployment.md','CMS_Deployment.md')
 archive.write(root/'docs/CMS_Manual.md','CMS_Manual.md')
with ZipFile(out) as archive:
 names=archive.namelist()
 assert not any(name.endswith('local.php') or name.endswith('.env') or '/backups/' in name or '/logs/' in name for name in names)
 assert 'public_html/index.php' in names and 'twin-cms/app/bootstrap.php' in names
print(out)
