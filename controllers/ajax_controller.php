<?php
// ================================================================
// CONTROLLER: AJAX JSON endpoints
// ================================================================

function ajax_controller($conn) {
    $action = $_GET['action'] ?? '';

    // Public endpoint for live registration username availability check
    if ($action === 'check_username') {
        $username = trim($_GET['username'] ?? '');
        if (strlen($username) < 3) {
            json_out(['ok' => false, 'message' => 'Too short (min 3 characters)']);
        }
        if (!preg_match('/^[A-Za-z0-9_]{3,20}$/', $username)) {
            json_out(['ok' => false, 'message' => '3-20 letters/numbers only']);
        }
        if (username_exists($conn, $username)) {
            json_out(['ok' => false, 'message' => 'Username is already taken']);
        }
        json_out(['ok' => true, 'message' => 'Username is available']);
    }

    if (!is_logged_in()) {
        json_out(['error' => 'Unauthorized'], 401);
    }

    switch ($action) {
        case 'stats':
            $role = current_role();
            if ($role === 'admin') {
                json_out(get_system_stats($conn));
            } elseif ($role === 'agency') {
                $agency = get_agency_profile($conn, current_user_id());
                json_out(get_agency_stats($conn, (int)($agency['id'] ?? 0), current_user_id()));
            } elseif ($role === 'university') {
                $uni = get_university_profile($conn, current_user_id());
                json_out(get_university_stats($conn, (int)($uni['id'] ?? 0)));
            } elseif ($role === 'student') {
                json_out(get_student_stats($conn, current_user_id()));
            }
            break;

        case 'search_users':
            require_role('admin');
            $term = trim($_GET['q'] ?? '');
            $role = trim($_GET['role'] ?? '');
            $rows = search_users($conn, $term, $role);
            json_out($rows);
            break;

        default:
            json_out(['error' => 'Unknown AJAX action'], 400);
            break;
    }
}
