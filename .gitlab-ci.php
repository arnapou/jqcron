#!/usr/bin/env php
<?php

namespace Arnapou\PhpUnit;

try {
    flushBuffers();
    dlPhpUnitPhar('phpunit.phar', phpUnitVersion());
    dlUrl('composer-setup.php', 'https://getcomposer.org/installer');
    system(PHP_BINARY . ' ' . escapeshellarg(__DIR__ . '/composer-setup.php'));
} catch (\Exception $exception) {
    exit(1);
} catch (\Error $error) {
    exit(1);
}

/**
 * cf. matrix https://phpunit.de/supported-versions.html
 *
 * PHPUnit 8    PHP 7.2, 7.3, 7.4         February 1, 2019    Support ends on February 5, 2021
 * PHPUnit 7    PHP 7.1, 7.2, 7.3         February 2, 2018    Support ends on February 7, 2020
 * PHPUnit 6    PHP 7.0, 7.1, 7.2         February 3, 2017    Support ended on February 1, 2019
 * PHPUnit 5    PHP 5.6, 7.0, 7.1         October 2, 2015     Support ended on February 2, 2018
 * PHPUnit 4    PHP 5.3, 5.4, 5.5, 5.6    March 7, 2014       Support ended on February 3, 2017
 */
function phpUnitVersion()
{
    if (PHP_VERSION_ID < 50600) {
        return 4;
    }
    if (PHP_VERSION_ID < 70000) {
        return 5;
    }
    if (PHP_VERSION_ID < 70100) {
        return 6;
    }
    if (PHP_VERSION_ID < 70200) {
        return 7;
    }
    return 8;
}

function dlPhpUnitPhar($filename, $version)
{
    dlUrl($filename, "https://phar.phpunit.de/phpunit-$version.phar");
}

function dlUrl($filename, $url)
{
    file_put_contents($filename, file_get_contents($url));
}

function flushBuffers()
{
    $n = 10;
    while (ob_get_level() && $n--) {
        ob_end_flush();
    }
}
