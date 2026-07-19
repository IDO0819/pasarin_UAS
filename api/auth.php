<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
/**
 * REST API: Auth
 *
 * POST /api/auth.php  body: { action: "login", email, password }
 * POST /api/auth.php  body: { action: "register", username, email, password }
 * POST /api/auth.php  body: { action: "logout" }
 */

require_once __DIR__ . '/../config/config.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    jsonResponse(['success' => false, 'message' => 'Method tidak diizinkan'], 405);
}

$input  = getJsonInput();
$action = $input['action'] ?? '';
$pdo    = getDBConnection();

switch ($action) {
    case 'register':
        $username = sanitize($input['username'] ?? '');
        $email    = filter_var(trim($input['email'] ?? ''), FILTER_VALIDATE_EMAIL);
        $password = $input['password'] ?? '';

        if (!$username || !$email || strlen($password) < 8) {
            jsonResponse(['success' => false, 'message' => 'Data tidak valid. Password minimal 8 karakter dan email harus benar.'], 422);
        }

        // Cek email unik
        $stmt = $pdo->prepare('SELECT id FROM users WHERE email = ?');
        $stmt->execute([$email]);
        if ($stmt->fetch()) {
            jsonResponse(['success' => false, 'message' => 'Email sudah terdaftar'], 409);
        }

        $hashed = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $pdo->prepare('INSERT INTO users (username, email, password, role, created_at) VALUES (?, ?, ?, "customer", NOW())');
        $stmt->execute([$username, $email, $hashed]);

        jsonResponse(['success' => true, 'message' => 'Registrasi berhasil, silakan login']);
        break;

    case 'login':
        $email    = filter_var(trim($input['email'] ?? ''), FILTER_VALIDATE_EMAIL);
        $password = $input['password'] ?? '';

        if (!$email || !$password) {
            jsonResponse(['success' => false, 'message' => 'Email dan password wajib diisi'], 422);
        }

        $stmt = $pdo->prepare('SELECT id, username, email, password, role FROM users WHERE email = ?');
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if (!$user || !password_verify($password, $user['password'])) {
            jsonResponse(['success' => false, 'message' => 'Email atau password salah'], 401);
        }

        // Set session
        $_SESSION['user_id']  = $user['id'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['role']     = $user['role'];

        jsonResponse(['success' => true, 'message' => 'Login berhasil', 'role' => $user['role']]);
        break;

    case 'logout':
        session_unset();
        session_destroy();
        jsonResponse(['success' => true, 'message' => 'Logout berhasil']);
        break;

    default:
        jsonResponse(['success' => false, 'message' => 'Aksi tidak dikenali'], 400);
}
