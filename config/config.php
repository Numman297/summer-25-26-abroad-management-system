<?php
// ================================================================
// CONFIG - database connection, session setup and app settings
// Everything in the project starts from here.
// ================================================================

/* ---------- 1. Database settings ---------- */
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'abroad_db');

/* ---------- 2. App settings ---------- */
define('APP_NAME',     'AbroadHub');
define('CURRENCY',     '$');
define('COMMISSION_RATE', 10); // Platform cut (%) on consultancy bookings
define('SESSION_TIMEOUT', 1800); // auto logout after 30 minutes of no activity

/* ---------- 3. Start a hardened session ---------- */
// SECURITY: the cookie cannot be read by JavaScript (httponly) and is not
// sent on cross-site requests (samesite), which blocks most XSS/CSRF tricks.
if (session_status() === PHP_SESSION_NONE) {
    session_set_cookie_params([
        'lifetime' => 0,
        'path'     => '/',
        'httponly' => true,
        'samesite' => 'Lax',
    ]);
    session_start();
}

/* ---------- 4. Connect to MySQL (procedural mysqli) ---------- */
$conn = mysqli_connect(DB_HOST, DB_USER, DB_PASS, DB_NAME);
if (!$conn) {
    die('Database connection failed. Did you import database.sql? Details: '
        . mysqli_connect_error());
}
mysqli_set_charset($conn, 'utf8mb4');

/* ---------- 5. Create the default admin the first time the app runs ---------- */
// Runs only when there is no admin yet. Login: admin / password123
$check = mysqli_query($conn, "SELECT id FROM users WHERE role = 'admin' LIMIT 1");
if ($check && mysqli_num_rows($check) === 0) {
    $hash = password_hash('password123', PASSWORD_DEFAULT);
    $stmt = mysqli_prepare($conn,
        "INSERT INTO users (name, email, contact, username, password, role, status)
         VALUES ('System Administrator', 'admin@abroad.com', '+8801711000001', 'admin', ?, 'admin', 'active')");
    mysqli_stmt_bind_param($stmt, 's', $hash);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);
}
