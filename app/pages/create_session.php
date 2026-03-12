<?php
require_once __DIR__ . '/../connect.php';

// Fetch last 10 sessions
$stmt = $pdo->query(
	"SELECT id, created
	 FROM session
	 ORDER BY created DESC
	 LIMIT 10"
);
$sessions = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
	<title>Create Session - Planning Poker</title>
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.0/css/bootstrap.min.css">
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
			align-items: flex-start;
			padding-top: 60px;
		}
		.container-card {
			background: #1a1a2e;
			border: 1px solid #2a2a4a;
			border-radius: 16px;
			padding: 40px;
			width: 100%;
			max-width: 520px;
			box-shadow: 0 8px 32px rgba(0,0,0,0.4);
		}
		h2 {
			margin: 0 0 24px 0;
			color: #fff;
			font-size: 28px;
			font-weight: 700;
		}
		h3 {
			color: #94a3b8;
			font-size: 16px;
			font-weight: 600;
			margin: 30px 0 12px 0;
			text-transform: uppercase;
			letter-spacing: 0.5px;
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
		button[type="submit"], .btn-primary-dark {
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
		button[type="submit"]:hover, .btn-primary-dark:hover {
			background: #4f46e5;
		}
		.error-msg {
			background: rgba(239,68,68,0.15);
			border: 1px solid #ef4444;
			border-radius: 8px;
			padding: 12px 16px;
			margin-bottom: 20px;
			color: #fca5a5;
		}
		.error-msg a {
			color: #818cf8;
			font-weight: 600;
		}
		.session-list ul {
			list-style: none;
			padding: 0;
			margin: 0;
		}
		.session-list li {
			padding: 10px 14px;
			margin: 4px 0;
			background: #0f0f1a;
			border: 1px solid #2a2a4a;
			border-radius: 8px;
			display: flex;
			justify-content: space-between;
			align-items: center;
			transition: border-color 0.2s;
		}
		.session-list li:hover {
			border-color: #6366f1;
		}
		.session-list a {
			color: #818cf8;
			text-decoration: none;
			font-weight: 600;
			font-size: 15px;
		}
		.session-list a:hover {
			color: #a5b4fc;
			text-decoration: underline;
		}
		.session-list small {
			color: #64748b;
			font-size: 12px;
		}
		.empty-msg {
			color: #64748b;
			font-style: italic;
		}
		.divider {
			border: none;
			border-top: 1px solid #2a2a4a;
			margin: 28px 0 0 0;
		}
	</style>
</head>
<body>

<div class="container-card">
	<h2>Create Session</h2>

	<?php
	if (isset($_GET['error']) && $_GET['error'] === 'exists') {
		$existingSession = htmlspecialchars($_GET['session'] ?? '');
		echo "<div class='error-msg'>
			Session ID already exists.
			<a href='{$BASE}/session?session={$existingSession}'>Click here to join</a>
		</div>";
	}
	?>

	<form method="post" action="<?= $BASE ?>/api/create-session">
		<label for="session_id">Session ID</label>
		<input type="text" id="session_id" name="session_id" required maxlength="100" placeholder="e.g. XMS-1234, sprint-42">
		<button type="submit">Create Session</button>
	</form>

	<hr class="divider">

	<div class="session-list">
		<h3>Recent Sessions</h3>

		<?php if (empty($sessions)): ?>
			<p class="empty-msg">No sessions created yet.</p>
		<?php else: ?>
			<ul>
				<?php foreach ($sessions as $s): ?>
					<li>
						<a href="<?= $BASE ?>/session?session=<?= urlencode($s['id']) ?>" target="_blank">
							<?= htmlspecialchars($s['id']) ?>
						</a>
						<small><?= htmlspecialchars($s['created']) ?></small>
					</li>
				<?php endforeach; ?>
			</ul>
		<?php endif; ?>
	</div>
</div>

</body>
</html>
