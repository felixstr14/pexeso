<?php
require '../vendor/autoload.php';

// Using Medoo namespace
use Medoo\Medoo;


header('Access-Control-Allow-Origin: *');
// Initialize

$database = new Medoo([
	'database_type' => 'MariaDB',
	'database_name' => 'pexeso',
	'server' => 'localhost',
	'username' => 'root',
	'password' => ''
]);

// Enjoy
$data = $database->get('ulozene_hry', 'data', ['ORDER' => ['id' => 'DESC']]);


$data = json_decode($data);
echo $data->savedGameArr;

exit();

