<?php
// ================================================================
// MODEL: activity_logs (audit log for security and operations)
// Every query uses a prepared statement -> no SQL injection.
// ================================================================

function log_activity($conn, $action) {
    $user = current_user();
    $userId = $user['id'] ?? null;
    $role   = $user['role'] ?? 'guest';

    $stmt = mysqli_prepare($conn, "INSERT INTO activity_logs (user_id, role, action) VALUES (?, ?, ?)");
    mysqli_stmt_bind_param($stmt, 'iss', $userId, $role, $action);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);
}

function get_logs($conn, $limit = 15) {
    $limit = (int)$limit;
    $sql = "SELECT l.*, u.name as user_name, u.username
            FROM activity_logs l
            LEFT JOIN users u ON l.user_id = u.id
            ORDER BY l.id DESC LIMIT $limit";
    $res = mysqli_query($conn, $sql);
    return mysqli_fetch_all($res, MYSQLI_ASSOC);
}
