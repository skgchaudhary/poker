<?php
session_start();
require_once __DIR__ . '/../connect.php';

// Auth check
if (empty($_SESSION['user'])) {
	http_response_code(401);
	exit('Unauthorized');
}

// Validate session
if (empty($_GET['session'])) {
	http_response_code(400);
	exit('Session missing');
}

$sessionId  = $_GET['session'];
$showPoints = isset($_GET['show_points']);

// Verify session exists
$stmt = $pdo->prepare(
	"SELECT 1 FROM session WHERE id = :session_id LIMIT 1"
);
$stmt->execute(['session_id' => $sessionId]);

if (!$stmt->fetchColumn()) {
	http_response_code(404);
	exit('Invalid session');
}

// Fetch users
$stmt = $pdo->prepare(
	"SELECT user, story_points
	 FROM users
	 WHERE session_id = :session_id
	 ORDER BY story_points ASC"
);
$stmt->execute(['session_id' => $sessionId]);

$users = $stmt->fetchAll();

if (!$users) {
	echo "<p style='color:#64748b;'>No users in this session.</p>";
	exit;
}

// Superhero mapping
$heroMap = [
	0  => 'The Watcher 👀',
	1  => 'Makkari ⚪',
	2  => 'Sonic the Hedgehog 🌀',
	3  => 'Quicksilver ⚡',
	5  => 'Iron Man 🤖',
	8  => 'Dr. Strange 🔮',
	13 => 'Thanos 🟣',
	21 => 'Hulk 💚',
	34 => 'Thor ⚡',
	55 => 'Galactus 🌌',
	89 => 'Arishem 🔴'
];
$heroPunchlines = [
	0  => 'Saw everything. Bound by `vows of not to intervene`.',
	1  => "Estimated before the ticket finished loading.",
	2  => "Confidence powered by vibes, not requirements.",
	3  => "Fast enough to create bugs at light speed.",
	5  => "Knows this will hurt — still agreed anyway.",
	8  => "Has already seen this fail in 14,000,605 timelines.",
	13 => "Half the sprint must die for balance.",
	21 => 'Big enough to hurt. Small enough to underestimate.',
	34 => 'Heavy, loud, and definitely not landing this sprint.',
	55 => 'Consumes sprints to survive.',
	89 => 'Judges the system. Decides its fate.'
];
$heroImage = [
	0  => "https://images.squarespace-cdn.com/content/v1/5133bc80e4b0c6fb04dcd6c4/c3fdd780-4063-4095-884e-860f5bec4728/utatu_the_watcher_e27_web.jpg",
	1  => "https://sm.ign.com/ign_in/screenshot/default/e-ihokewuauopbk_vhzt.jpg",
	2  => "https://freepngimg.com/download/sonic_the_hedgehog/20905-6-sonic-the-hedgehog-transparent.png",
	3  => "https://static.wikia.nocookie.net/marvelcinematicuniverse/images/1/1e/Quicksilver_Infobox.jpg",
	5  => "https://i.pinimg.com/474x/48/bc/22/48bc22c2e7a0e07f2638f15d485d9b86.jpg",
	8  => "https://i.makeagif.com/media/12-03-2018/IGyZk_.gif",
	13 => "https://media4.giphy.com/media/v1.Y2lkPTc5MGI3NjExbGM3cmk0Z3poMms2a3h2Z2htMWp6bDl3ajBuOHR0OHM1cnJraXB3OCZlcD12MV9pbnRlcm5hbF9naWZfYnlfaWQmY3Q9Zw/xUOxeZn47mrdabqDNC/giphy.gif",
	21 => 'https://www.thefactsite.com/wp-content/uploads/2021/05/the-hulk-facts.jpg',
	34 => 'https://upload.wikimedia.org/wikipedia/en/3/3c/Chris_Hemsworth_as_Thor.jpg',
	55 => 'https://cdn.mos.cms.futurecdn.net/v2/t:0,l:240,cw:1440,ch:1080,q:80,w:1440/CdstFQgZarSLPZVrJWvWjU.jpg',
	89 => 'https://fictionhorizon.com/wp-content/uploads/2021/10/arishem-the-judge-150x150.jpg'
];

// Variables for average
$totalPoints = 0;
$bidCount    = 0;

// Output table
echo "<table>";

// Table header
echo "<tr>
		<th style='text-align:left;'>User</th>
		<th>Points</th>";

if ($showPoints) {
	echo "<th>Story Type</th>";
}

echo "</tr>";

$i = 1;
foreach ($users as $user) {
	$points = (int)$user['story_points'];

	echo "<tr>";
	echo "<td style='text-align:left;font-weight:500;'>" . ucfirst(trim(htmlspecialchars($user['user']))) . "</td>";
	echo "<td align='center'>";

	if ($points === 0) {
		echo "<span style='color:#f87171;font-weight:500;'>No Call</span>";
	} else {
		if ($showPoints) {
			echo "<span style='color:#a5b4fc;font-weight:700;'>{$points}</span>";
			$totalPoints += $points;
			$bidCount++;
		} else {
			echo "<span style='color:#4ade80;font-weight:500;'>Bet Placed</span>";
		}
	}

	echo "</td>";

	// Type column (only when show_points ON)
	if ($showPoints) {
		echo "<td class='zoomable' align='center' style='font-size:12px;cursor:pointer;' line-id='line-{$i}'>";
		echo "<b style='color:#fbbf24;'>{$heroMap[$points]}</b>";
		echo "
			<div id='line-{$i}' style='display:none;background:#0f0f1a;border:1px solid #2a2a4a;border-radius:8px;padding:10px;margin-top:8px;'>
				<img src='{$heroImage[$points]}' width='100' height='100' alt='img' style='border-radius:6px;'/>
				<br>
				<i style='color:#94a3b8;font-size:11px;'>{$heroPunchlines[$points]}</i>
			</div>
		";
		echo "</td>";
	}
	$i++;
	echo "</tr>";
}

// Average row
if ($showPoints && $bidCount > 0) {
	$average = round($totalPoints / $bidCount, 2);

	echo "<tr style='font-weight:bold;'>";
	echo "<td align='right' style='color:#94a3b8;'>Average</td>";
	echo "<td align='center' style='color:#4ade80;font-size:18px;'>{$average}</td>";
	echo "<td></td>";
	echo "</tr>";
}

echo "</table>";
