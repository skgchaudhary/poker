<?php
session_start();
require_once __DIR__ . '/../connect.php';

/* -----------------------------
   0. User must be logged in
------------------------------ */
if (empty($_SESSION['user'])) {
	header('Location: ' . $BASE . '/join?session=' . urlencode($_GET['session'] ?? ''));
	exit;
}

$currentUser = $_SESSION['user'];

/* -----------------------------
   1. Validate session param
------------------------------ */
if (empty($_GET['session'])) {
	header('Location: ' . $BASE . '/');
	exit;
}

$sessionId = $_GET['session'];

/* -----------------------------
   2. Verify session exists
------------------------------ */
$stmt = $pdo->prepare(
	"SELECT 1 FROM session WHERE id = :session_id LIMIT 1"
);
$stmt->execute(['session_id' => $sessionId]);

if (!$stmt->fetchColumn()) {
	header('Location: ' . $BASE . '/');
	exit;
}

/* -----------------------------
   3. Auto-join user if not exists
------------------------------ */
$stmt = $pdo->prepare(
	"SELECT 1 FROM users
	 WHERE session_id = :session_id
	   AND user = :user
	 LIMIT 1"
);
$stmt->execute([
	'session_id' => $sessionId,
	'user'       => $currentUser
]);

if (!$stmt->fetchColumn()) {
	$insert = $pdo->prepare(
		"INSERT INTO users (session_id, user)
		 VALUES (:session_id, :user)"
	);
	$insert->execute([
		'session_id' => $sessionId,
		'user'       => $currentUser
	]);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
	<title>Planning Poker - <?= htmlspecialchars($sessionId) ?></title>
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.0/css/bootstrap.min.css">
	<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
	<script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.0/js/bootstrap.min.js"></script>
	<style>
		* { box-sizing: border-box; }
		body {
			font-family: 'Segoe UI', Arial, sans-serif;
			background: #0f0f1a;
			color: #e2e8f0;
			margin: 0;
			padding: 24px;
			min-height: 100vh;
		}

		/* Top bar */
		.top-bar {
			display: flex;
			justify-content: space-between;
			align-items: center;
			margin-bottom: 28px;
			flex-wrap: wrap;
			gap: 12px;
		}
		.top-bar-left h2 {
			margin: 0;
			color: #fff;
			font-size: 24px;
			font-weight: 700;
		}
		.top-bar-left p {
			margin: 4px 0 0 0;
			color: #64748b;
			font-size: 14px;
		}
		.top-bar-left strong {
			color: #818cf8;
		}
		.top-bar-right a {
			display: inline-block;
			padding: 8px 16px;
			margin-left: 10px;
			border-radius: 8px;
			text-decoration: none;
			font-weight: 600;
			font-size: 14px;
			transition: all 0.2s;
		}
		.btn-new-session {
			background: #1a1a2e;
			border: 1px solid #2a2a4a;
			color: #818cf8;
		}
		.btn-new-session:hover {
			border-color: #6366f1;
			color: #a5b4fc;
		}
		.btn-sign-out {
			background: rgba(239,68,68,0.1);
			border: 1px solid rgba(239,68,68,0.3);
			color: #f87171;
		}
		.btn-sign-out:hover {
			background: rgba(239,68,68,0.2);
			border-color: #ef4444;
		}

		/* Voting cards */
		.cards-section {
			background: #1a1a2e;
			border: 1px solid #2a2a4a;
			border-radius: 16px;
			padding: 24px;
			margin-bottom: 24px;
		}
		.cards-label {
			color: #94a3b8;
			font-size: 12px;
			font-weight: 600;
			text-transform: uppercase;
			letter-spacing: 1px;
			margin-bottom: 14px;
		}
		.cards-grid {
			display: flex;
			flex-wrap: wrap;
			gap: 10px;
		}
		.story {
			padding: 14px 22px;
			font-size: 18px;
			font-weight: 700;
			border-radius: 10px;
			border: 2px solid #2a2a4a;
			background: #0f0f1a;
			color: #e2e8f0;
			cursor: pointer;
			transition: all 0.2s ease;
			min-width: 56px;
			text-align: center;
		}
		.story:hover {
			border-color: #6366f1;
			background: rgba(99,102,241,0.1);
			color: #fff;
			transform: translateY(-2px);
			box-shadow: 0 4px 12px rgba(99,102,241,0.2);
		}
		.story:active {
			transform: translateY(0);
		}
		.story.selected {
			border-color: #6366f1;
			background: #6366f1;
			color: #fff;
			box-shadow: 0 4px 16px rgba(99,102,241,0.35);
		}

		/* Toggle & users section */
		.users-section {
			background: #1a1a2e;
			border: 1px solid #2a2a4a;
			border-radius: 16px;
			padding: 24px;
		}
		.users-toolbar {
			display: flex;
			align-items: center;
			gap: 12px;
			margin-bottom: 16px;
		}
		#togglePoints {
			padding: 8px 20px;
			font-weight: 600;
			font-size: 14px;
			cursor: pointer;
			border-radius: 8px;
			border: 1px solid #2a2a4a;
			background: #0f0f1a;
			color: #e2e8f0;
			transition: all 0.2s;
		}
		#togglePoints:hover {
			border-color: #6366f1;
			color: #818cf8;
		}
		.btn-refresh {
			width: 38px;
			height: 38px;
			border: 1px solid #2a2a4a;
			background: #0f0f1a;
			color: #94a3b8;
			border-radius: 8px;
			display: inline-flex;
			align-items: center;
			justify-content: center;
			cursor: pointer;
			transition: all 0.2s;
			padding: 0;
		}
		.btn-refresh:hover {
			border-color: #6366f1;
			color: #818cf8;
		}

		/* Table styles (rendered by refresh API) */
		#users table {
			width: 100%;
			border-collapse: separate;
			border-spacing: 0;
			border-radius: 8px;
			overflow: hidden;
		}
		#users th {
			background: #0f0f1a;
			color: #94a3b8;
			font-size: 12px;
			font-weight: 600;
			text-transform: uppercase;
			letter-spacing: 0.5px;
			padding: 10px 14px;
			border-bottom: 1px solid #2a2a4a;
		}
		#users td {
			padding: 10px 14px;
			border-bottom: 1px solid #1e1e30;
			color: #e2e8f0;
		}
		#users tr:last-child td {
			border-bottom: none;
		}
		#users tr:hover td {
			background: rgba(99,102,241,0.04);
		}
	</style>
</head>
<body>

<!-- Top bar -->
<div class="top-bar">
	<div class="top-bar-left">
		<h2><?= htmlspecialchars($sessionId) ?></h2>
		<p>Logged in as <strong><?= htmlspecialchars($currentUser) ?></strong></p>
	</div>
	<div class="top-bar-right">
		<a href="<?= $BASE ?>/" class="btn-new-session">+ New Session</a>
		<a href="<?= $BASE ?>/sign-out" class="btn-sign-out">Sign Out</a>
	</div>
</div>

<!-- Voting cards -->
<div class="cards-section">
	<div class="cards-label">Pick your estimate</div>
	<div class="cards-grid">
		<button class="story" data-value="1" onclick="updateStoryPoints(1)">1</button>
		<button class="story" data-value="2" onclick="updateStoryPoints(2)">2</button>
		<button class="story" data-value="3" onclick="updateStoryPoints(3)">3</button>
		<button class="story" data-value="5" onclick="updateStoryPoints(5)">5</button>
		<button class="story" data-value="8" onclick="updateStoryPoints(8)">8</button>
		<button class="story" data-value="13" onclick="updateStoryPoints(13)">13</button>
		<button class="story" data-value="21" onclick="updateStoryPoints(21)">21</button>
		<button class="story" data-value="34" onclick="updateStoryPoints(34)">34</button>
		<button class="story" data-value="55" onclick="updateStoryPoints(55)">55</button>
		<button class="story" data-value="89" onclick="updateStoryPoints(89)">89</button>
	</div>
</div>

<!-- Users section -->
<div class="users-section">
	<div class="users-toolbar">
		<button id="togglePoints" onclick="toggleShowPoints()">Show Points</button>
		<button class="btn-refresh" onclick="manualRefreshUsers()" title="Refresh">
			<span class="glyphicon glyphicon-refresh" style="font-size:16px;"></span>
		</button>
	</div>
	<div id="users"></div>
</div>

<script>
let showPoints = false;
let selectedPoints = 0;
const BASE = '<?= $BASE ?>';

function buildUrl() {
	let url = BASE + '/api/refresh-users?session=<?= urlencode($sessionId) ?>';
	if (showPoints) {
		url += '&show_points=1';
	}
	return url;
}

function manualRefreshUsers() {
	document.getElementById('users').innerHTML = '<p style="color:#64748b;">Loading...</p>';
	refreshUsers();
}

function refreshUsers() {
	var xhr = new XMLHttpRequest();
	xhr.open('GET', buildUrl(), true);
	xhr.onreadystatechange = function () {
		if (xhr.readyState === 4 && xhr.status === 200) {
			document.getElementById('users').innerHTML = xhr.responseText;
			document.querySelectorAll('.zoomable').forEach(function (el) {
				el.addEventListener('mouseenter', function () {
					document.getElementById(el.getAttribute('line-id')).style.display = "";
				});
				el.addEventListener('mouseleave', function () {
					document.getElementById(el.getAttribute('line-id')).style.display = "none";
				});
			});
		}
	};
	xhr.send();
}

function toggleShowPoints() {
	showPoints = !showPoints;

	document.getElementById('togglePoints').innerText =
		showPoints ? 'Hide Points' : 'Show Points';

	const url = new URL(window.location);
	if (showPoints) {
		url.searchParams.set('show_points', '1');
	} else {
		url.searchParams.delete('show_points');
	}
	window.history.replaceState({}, '', url);

	refreshUsers();
}

function updateStoryPoints(points) {
	// Highlight selected card
	selectedPoints = points;
	document.querySelectorAll('.story').forEach(function(btn) {
		btn.classList.toggle('selected', parseInt(btn.getAttribute('data-value')) === points);
	});

	var xhr = new XMLHttpRequest();
	xhr.open('POST', BASE + '/api/update-points', true);
	xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
	xhr.onload = function () {
		if (xhr.status === 200) {
			refreshUsers();
		}
	};
	xhr.send(
		'session=<?= urlencode($sessionId) ?>' +
		'&story_points=' + encodeURIComponent(points)
	);
}

// Init toggle from URL
(function () {
	const params = new URLSearchParams(window.location.search);
	showPoints = params.has('show_points');
	document.getElementById('togglePoints').innerText =
		showPoints ? 'Hide Points' : 'Show Points';
})();

setInterval(refreshUsers, 5000);
refreshUsers();
</script>

</body>
</html>
