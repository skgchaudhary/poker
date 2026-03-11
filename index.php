<?php
/**
 * Front Controller - Routes all requests to the appropriate handler.
 * No .php files are exposed in the URL.
 */

// Get the request path relative to /poker/
$basePath = rtrim(dirname($_SERVER['SCRIPT_NAME']), '/');
$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$route = '/' . trim(substr($uri, strlen($basePath)), '/');

// Route map
$routes = [
    '/'                     => 'pages/create_session.php',
    '/session'              => 'pages/home.php',
    '/join'                 => 'pages/create_user.php',
    '/sign-out'             => 'pages/sign_out.php',
    '/api/create-session'   => 'api/create_session_db.php',
    '/api/create-user'      => 'api/create_user_db.php',
    '/api/update-points'    => 'api/update_story_points_db.php',
    '/api/refresh-users'    => 'api/refresh_users_status_db.php',
];

if (isset($routes[$route])) {
    require __DIR__ . '/app/' . $routes[$route];
} else {
    http_response_code(404);
    echo '<!DOCTYPE html><html><head><title>404</title></head><body style="background:#0f0f1a;color:#e2e8f0;font-family:Segoe UI,Arial,sans-serif;display:flex;justify-content:center;align-items:center;min-height:100vh;margin:0;"><div style="text-align:center;"><h1 style="font-size:48px;color:#6366f1;margin:0;">404</h1><p style="color:#64748b;">Page not found.</p><a href="' . $basePath . '/" style="color:#818cf8;">Back to home</a></div></body></html>';
}
