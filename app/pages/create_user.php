<?php
require_once __DIR__ . '/../connect.php';

// Validate session parameter
if (empty($_GET['session'])) {
	header('Location: ' . $BASE . '/');
	exit;
}

$sessionId = $_GET['session'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
	<title>Join Session - Planning Poker</title>
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<style>
		* { box-sizing: border-box; }
		body {
			font-family: 'Segoe UI', Arial, sans-serif;
			background: #0f0f1a;
			color: #e2e8f0;
			margin: 0;
			padding: 0;
			min-height: 100vh;
			display: flex;
			justify-content: center;
			align-items: center;
		}
		.container-card {
			background: #1a1a2e;
			border: 1px solid #2a2a4a;
			border-radius: 16px;
			padding: 40px;
			width: 100%;
			max-width: 440px;
			box-shadow: 0 8px 32px rgba(0,0,0,0.4);
		}
		h2 {
			margin: 0 0 8px 0;
			color: #fff;
			font-size: 26px;
			font-weight: 700;
		}
		.session-badge {
			display: inline-block;
			background: #0f0f1a;
			border: 1px solid #2a2a4a;
			border-radius: 6px;
			padding: 4px 12px;
			color: #818cf8;
			font-size: 14px;
			font-weight: 600;
			margin-bottom: 24px;
		}
		label {
			display: block;
			color: #94a3b8;
			font-size: 14px;
			margin-bottom: 6px;
			font-weight: 500;
		}
		input[type="text"] {
			width: 100%;
			padding: 12px 16px;
			background: #0f0f1a;
			border: 1px solid #2a2a4a;
			border-radius: 8px;
			color: #e2e8f0;
			font-size: 16px;
			outline: none;
			transition: border-color 0.2s;
		}
		input[type="text"]:focus {
			border-color: #6366f1;
			box-shadow: 0 0 0 3px rgba(99,102,241,0.15);
		}
		button[type="submit"] {
			width: 100%;
			padding: 12px;
			margin-top: 16px;
			background: #6366f1;
			color: #fff;
			border: none;
			border-radius: 8px;
			font-size: 16px;
			font-weight: 600;
			cursor: pointer;
			transition: background 0.2s;
		}
		button[type="submit"]:hover {
			background: #4f46e5;
		}
		.back-link {
			display: block;
			text-align: center;
			margin-top: 20px;
			color: #64748b;
			font-size: 14px;
			text-decoration: none;
		}
		.back-link:hover {
			color: #818cf8;
		}
	</style>
</head>
<body>

<div class="container-card">
	<h2>Join Session</h2>
	<div class="session-badge"><?= htmlspecialchars($sessionId) ?></div>

	<form method="post" action="<?= $BASE ?>/api/create-user">
		<input type="hidden" name="session_id" value="<?= htmlspecialchars($sessionId) ?>">

		<label for="user">Your Name</label>
		<input type="text" id="user" name="user" required maxlength="100" placeholder="Enter your name" autofocus>

		<button type="submit">Join</button>
	</form>

	<a href="<?= $BASE ?>/" class="back-link">Back to sessions</a>
</div>

</body>
</html>
