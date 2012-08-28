<?php
use EasyBib\Core\ProcessHandler;

require_once dirname(__DIR__) . '/library/EasyBib/Core/ProcessHandler.php';

try {
    $p = new ProcessHandler("echo 'hello world';", __DIR__);
    $p->execute();
    $p->echoShell("Great succcess");

    $p->setCommand("/this/really/does/not/exist")->execute();

} catch (\Exception $e) {
    $p->echoShell($e, true);
}
