<?php

include 'config/credentials.php';
include 'vendor/autoload.php';

use Illuminate\Database\Capsule\Manager as Capsule; 
// creates database connection
$capsule = new Capsule();
$capsule->addConnection([
    "driver" => "mysql",
    "host" => $db_host,
    "database" => $db_name,
    "username" => $db_user,
    "password" => $db_pass,
    "charset" => "utf8",
    "collation" => "utf8_general_ci",
    "prefix" => ""
]);
$capsule->setAsGlobal();
$capsule->bootEloquent();