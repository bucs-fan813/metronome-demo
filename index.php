<?php
//Show php debug errors
ini_set('display_errors', 1);

//Require composer libraries
require 'vendor/autoload.php';

//Initialize F3
$f3 = \Base::instance();

//Load F3 Config
$f3->config('config.ini');
//$f3->set('debug', k($f3,KRUMO_RETURN));
//Check to see if there is a database name already set in the F3 config or use the (cleaned) SERVER_NAME
// if (!($f3->get('DB')))
// {
//     $site = new \Site($schema_name);
//     if ($site->databaseReady() == true)
//     {
//         $driver = $site->get_db_driver();
//         $host = $site->get_db_hostname();
//         $port = $site->get_db_port();
//         $username = $site->get_db_username();
//         $password = $site->get_db_password();
//         $db = new \DB\SQL("{$driver}:host={$host};port={$port};dbname={$schema_name}", $username, $password);
//         $f3->set('DB', $db);
//         //$site->set_db($db);
//     }
    
//     if ($schema = $f3->get('schema')) {
//         $site->schemaReady($schema);
//     }
    
// }
$f3->run();
