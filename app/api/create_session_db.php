<?php
require_once __DIR__ . '/../connect.php';

// Validate input
if (empty($_POST['session_id'])) {
	header('Location: ' . $BASE . '/');
	exit;
}

$sessionId = trim($_POST['session_id']);

try {
	$stmt = $pdo->prepare(
		"INSERT INTO session (id) VALUES (:session_id)"
	);
	$stmt->execute([
		'session_id' => $sessionId
	]);

	// Success → redirect to session
	header('Location: ' . $BASE . '/session?session=' . urlencode($sessionId));
	exit;

} catch (PDOException $e) {

	// MySQL duplicate entry error code
	if ($e->getCode() === '23000') {
		header('Location: ' . $BASE . '/?error=exists&session=' . urlencode($sessionId));
		exit;
	}

	// Unknown DB error
	throw $e;
}
