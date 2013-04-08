<?php
function autoload($class)
{
    $class = str_replace('_', '/', $class);
    if (file_exists(dirname(__FILE__) . '/../src/' . $class . '.php')) {
        include dirname(__FILE__) . '/../src/' . $class . '.php';
    }
}

spl_autoload_register("autoload");