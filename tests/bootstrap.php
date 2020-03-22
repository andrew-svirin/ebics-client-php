<?php
/**
 * @file Bootstrapping File for Test Suite
 */

$envs_path = __DIR__ . '/../envs.local.php';
if (is_file($envs_path))
{
   include $envs_path;
}

require_once __DIR__ . '/../vendor/autoload.php';
