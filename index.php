<?php
require_once("core/loader.php");
Application::Uses("com.tools.RegExps");

RewriteManager::addRule('^salir$','/logout');

$app=new dmFramework();
$app->run();
$app->done();

