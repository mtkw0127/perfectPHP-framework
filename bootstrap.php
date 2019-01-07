<?php
require 'core/ClassLoader.php';
require 'env.php';

ini_set('display_errors', 'On');
$loader = new ClassLoader();
$loader->registerDir(dirname(__FILE__).'/core');
$loader->registerDir(dirname(__FILE__).'/models');
$loader->register();