<?php
// Front controller — the only PHP entry point at the web root.
// All application code lives in /app; web-served assets stay in /assets and /libs.
define('WEB_ROOT', __DIR__);
define('APP_PATH', __DIR__ . '/app');
require APP_PATH . '/router.php';
