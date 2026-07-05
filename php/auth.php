<?php
require_once 'config.php';

$action = $_POST['action'] ?? $_GET['action'] ?? '';

switch ($action) {
    case 'register':
        register();
        break;
    case 'login':
        login();
        break;
    case 'logout':
        logout();
        break;
    case 'check':
        checkSession();
        break;
    default:
        jsonResponse(['error' => 'Invalid action'], 400);
}

function register() {
    $name = sanitize($_POST['name'] ?? '');
    $email = sanitize($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if (empty($name) || empty($email) || empty($password)) {
        jsonResponse(['error' => 'All fields are required'], 400);
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        jsonResponse(['error' => 'Invalid email address'], 400);
    }

    if (strlen($password) < 6) {
        jsonResponse(['error' => 'Password must be at least 6 characters'], 400);
    }

    $db = getDB();

    // Check if email exists
    $stmt = $db->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->execute([$email]);
    if ($stmt->fetch()) {
        jsonResponse(['error' => 'Email already registered'], 400);
    }

    // Create user
    $hash = password_hash($password, PASSWORD_BCRYPT);
    $stmt = $db->prepare("INSERT INTO users (name, email, password) VALUES (?, ?, ?)");
    $stmt->execute([$name, $email, $hash]);
    $userId = $db->lastInsertId();

    // Auto-login
    $_SESSION['user_id'] = $userId;
    $_SESSION['user_name'] = $name;
    $_SESSION['user_email'] = $email;

    jsonResponse(['success' => true, 'name' => $name, 'message' => 'Account created!']);
}

function login() {
    $email = sanitize($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if (empty($email) || empty($password)) {
        jsonResponse(['error' => 'Email and password required'], 400);
    }

    $db = getDB();
    $stmt = $db->prepare("SELECT id, name, email, password FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    if (!$user || !password_verify($password, $user['password'])) {
        jsonResponse(['error' => 'Invalid email or password'], 401);
    }

    $_SESSION['user_id'] = $user['id'];
    $_SESSION['user_name'] = $user['name'];
    $_SESSION['user_email'] = $user['email'];

    jsonResponse(['success' => true, 'name' => $user['name']]);
}

function logout() {
    session_destroy();
    jsonResponse(['success' => true]);
}

function checkSession() {
    if (isLoggedIn()) {
        jsonResponse([
            'loggedIn' => true,
            'name' => $_SESSION['user_name'],
            'email' => $_SESSION['user_email']
        ]);
    } else {
        jsonResponse(['loggedIn' => false]);
    }
}
?>
