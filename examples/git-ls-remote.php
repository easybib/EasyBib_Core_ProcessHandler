<?php
use EasyBib\Core\ProcessHandler;

require_once dirname(__DIR__) . '/library/EasyBib/Core/ProcessHandler.php';

$repoExists = false;
try {
    $p = new ProcessHandler("git ls-remote https://github.com/ulfharn/chef-openntpd >/dev/null 2>&1", __DIR__);
    $p->execute(false);
    $repoExists = true;
} catch (\Exception $e) {
}

var_dump($repoExists);
