<?php
//Show php debug errors
ini_set('display_errors', 1);

//Require composer libraries
require 'vendor/autoload.php';

//Initialize F3
$f3 = \Base::instance();

//Load F3 Config
$f3->config('config.ini');

//Run F3
$f3->run();
