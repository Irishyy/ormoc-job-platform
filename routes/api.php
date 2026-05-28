<?php

// API router for login/signup actions.
// Expected calls from frontend:
//   POST /routes/api.php?action=manual_login
//   POST /routes/api.php?action=manual_signup
//   POST /routes/api.php?action=oauth_login

require_once __DIR__ . '/../controllers/AuthController.php';

header('Content-Type: application/json; charset=utf-8');

$action = $_GET['action'] ?? '';

// Read JSON body sent by axios
$raw = file_get_contents('php://input');
$data = [];
if (!empty($raw)) {
    $decoded = json_decode($raw, true);
    if (is_array($decoded)) {
        $data = $decoded;
    }
}

$controller = new AuthController();

switch ($action) {
    case 'oauth_login':
        $controller->handleGoogleLogin($data);
        break;

    case 'manual_signup':
        $controller->handleManualSignUp($data);
        break;

    case 'manual_login':
        $controller->handleManualLogin($data);
        break;

    default:
        echo json_encode([
            'status' => 'error',
            'message' => 'Unknown action.'
        ]);
        break;
}

