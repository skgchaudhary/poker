<?php
session_start();
require_once __DIR__ . '/../connect.php';

// Validate input
if (empty($_POST['session_id']) || empty($_POST['user'])) {
	header('Location: ' . $BASE . '/');
	exit;
}

$sessionId = trim($_POST['session_id']);
$userName  = trim($_POST['user']);

// 1. Check if user already exists in this session
$stmt = $pdo->prepare(
	"SELECT 1
	 FROM users
	 WHERE session_id = :session_id
	   AND user = :user
	 LIMIT 1"
);

$stmt->execute([
	'session_id' => $sessionId,
	'user'       => $userName
]);

// 2. If exists → just set PHP session & redirect
if ($stmt->fetchColumn()) {
	$_SESSION['user'] = $userName;
	header('Location: ' . $BASE . '/session?session=' . urlencode($sessionId));
	exit;
}

// 3. Else → insert user
$stmt = $pdo->prepare(
	"INSERT INTO users (session_id, user)
	 VALUES (:session_id, :user)"
);

$stmt->execute([
	'session_id' => $sessionId,
	'user'       => $userName
]);

// 4. Store user in PHP session
$_SESSION['user'] = $userName;

// 5. Redirect back to session page
header('Location: ' . $BASE . '/session?session=' . urlencode($sessionId));
exit;
