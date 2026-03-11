<?php
session_start();
require_once __DIR__ . '/../connect.php';

header('Content-Type: application/json; charset=utf-8');

// 0. User must be logged in
if (empty($_SESSION['user'])) {
	http_response_code(401);
	echo json_encode(['status' => 'error', 'message' => 'Not logged in']);
	exit;
}

// 1. Validate POST params
if (
	empty($_POST['session']) ||
	!isset($_POST['story_points'])
) {
	http_response_code(400);
	echo json_encode(['status' => 'error', 'message' => 'Missing parameters']);
	exit;
}

$sessionId   = trim($_POST['session']);
$storyPoints = (int) $_POST['story_points'];
$userName    = $_SESSION['user'];

// Optional: prevent negative points
if ($storyPoints < 0) {
	http_response_code(400);
	echo json_encode(['status' => 'error', 'message' => 'Invalid story points']);
	exit;
}

// 2. Update story points for this user + session
$stmt = $pdo->prepare(
	"UPDATE users
	 SET story_points = :story_points
	 WHERE session_id = :session_id
	   AND user = :user
	 LIMIT 1"
);

$stmt->execute([
	'story_points' => $storyPoints,
	'session_id'   => $sessionId,
	'user'         => $userName
]);

// 3. Check if row was updated
if ($stmt->rowCount() === 0) {
	http_response_code(404);
	echo json_encode(['status' => 'error', 'message' => 'User not found in session']);
	exit;
}

// 4. Success
echo json_encode([
	'status' => 'success',
	'user' => $userName,
	'story_points' => $storyPoints
]);
