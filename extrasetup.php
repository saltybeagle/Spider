<?php
/**
 * This file is used to provide extra files/packages outside package.xml
 */
/**
 * for example:
if (basename(__DIR__) == 'trunk') {
    $extrafiles = array(
        new \pear2\Pyrus\Package(__DIR__ . '/../../HTTP_Request/trunk/package.xml'),
        new \pear2\Pyrus\Package(__DIR__ . '/../../sandbox/Console_CommandLine/trunk/package.xml'),
        new \pear2\Pyrus\Package(__DIR__ . '/../../MultiErrors/trunk/package.xml'),
        new \pear2\Pyrus\Package(__DIR__ . '/../../Exception/trunk/package.xml'),
    );
} else {
    $extrafiles = array(
        new \pear2\Pyrus\Package(__DIR__ . '/../HTTP_Request/package.xml'),
        new \pear2\Pyrus\Package(__DIR__ . '/../sandbox/Console_CommandLine/package.xml'),
        new \pear2\Pyrus\Package(__DIR__ . '/../MultiErrors/package.xml'),
        new \pear2\Pyrus\Package(__DIR__ . '/../Exception/package.xml'),
    );
}
*/
