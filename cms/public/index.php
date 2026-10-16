<?php
declare(strict_types=1);
// In Xserver, put a copy of this entry point in public_html and point this
// path to the private twin-cms directory outside public_html (see Deployment).
require dirname(__DIR__).'/app/bootstrap.php';
require CMS_ROOT.'/app/content.php';
require CMS_ROOT.'/app/render.php';
require CMS_ROOT.'/app/admin.php';
require CMS_ROOT.'/app/public.php';
route_request();
