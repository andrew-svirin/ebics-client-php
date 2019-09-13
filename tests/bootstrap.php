<?php
/**
 * @file Bootstrapping File for Test Suite
 */

use Composer\Autoload\ClassLoader;
use function Composer\Autoload\includeFile;

$loader_path = __DIR__ . '/../vendor/autoload.php';
/* @var $loader ClassLoader */
$loader = include $loader_path;
$loader->add('', __DIR__);

includeFile(__DIR__ . '/common/EbicsTestCase.php');
