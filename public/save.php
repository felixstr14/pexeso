<?php
require '../vendor/autoload.php';

// Using Medoo namespace
use Medoo\Medoo;


header('Access-Control-Allow-Origin: *');

$database = new Medoo([
	'database_type' => 'MariaDB',
	'database_name' => 'pexeso',
	'server' => 'localhost',
	'username' => 'root',
	'password' => ''
]);

// Enjoy
$database->insert('ulozene_hry', [
	'data' => json_encode($_POST)
]);

