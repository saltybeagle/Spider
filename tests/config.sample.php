<?php
function autoload($class)
{
    $class = str_replace('_', '/', $class);
    if (file_exists(dirname(__FILE__) . '/../src/' . $class . '.php')) {
        include dirname(__FILE__) . '/../src/' . $class . '.php';
    }
}

spl_autoload_register("autoload");

//Default to a site that should be visible to everyone.  add a tests/config.inc.php to override
$GLOBALS['baseurl'] = "http://ucommxsrv2.unl.edu/spider/tests/data/";