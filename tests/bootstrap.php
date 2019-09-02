<?php
/**
 * Bootstrapping File for Test Suite
 */

$loader_path = __DIR__ . '/../vendor/autoload.php';
$loader = include $loader_path;
$loader->add('', __DIR__);
