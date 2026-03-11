<?php
$host = 'sql12.freesqldatabase.com';
$db   = 'sql12819633';
$user = 'sql12819633';
$pass = 'Cme2eMZ6KL';
$charset = 'utf8mb4';

$dsn = "mysql:host=$host;dbname=$db;charset=$charset";

// Base URL for clean routes (e.g. "/poker")
$BASE = rtrim(dirname($_SERVER['SCRIPT_NAME']), '/');

$options = [
	PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
	PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
	PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
	$pdo = new PDO($dsn, $user, $pass, $options);
} catch (PDOException $e) {
	// Never expose DB errors in production
	die($e . ' Database connection failed.');
}
