<?php
// ================================================================
// CONTROLLER: ADMIN dashboard
// CRUD  : user accounts (every role)
// Extras: 1) Agency & University verification workflow
//         2) Platform revenue & commission ledger
//         3) Student feedback resolution
//         4) System activity audit log
// ================================================================

function admin_controller($conn) {
    $action     = $_GET['action'] ?? 'dashboard';
    $roleFilter = $_GET['role']   ?? '';
    $me         = current_user();

    $error   = '';
    $editing = null;

    $roles    = ['admin', 'agency', 'university', 'student'];
    $statuses = ['active', 'suspended'];

    /* ---------------- CREATE USER ---------------- */
    if ($action === 'add_user' && is_post()) {
        csrf_check();

        $name     = trim($_POST['name'] ?? '');
        $email    = trim($_POST['email'] ?? '');
        $contact  = trim($_POST['contact'] ?? '');
        $username = trim($_POST['username'] ?? '');
        $password = $_POST['password'] ?? '';
        $role     = $_POST['role'] ?? '';

        if (is_blank($name) || is_blank($email) || is_blank($contact)
            || is_blank($username) || is_blank($password)) {
            $error = 'Fill in every field.';
        } elseif (!in_array($role, $roles, true)) {
            $error = 'Choose a valid role.';
        } elseif (!valid_email($email)) {
            $error = 'Enter a valid email address.';
        } elseif (!valid_contact($contact)) {
            $error = 'Enter a valid contact number.';
        } elseif (username_exists($conn, $username)) {
            $error = 'That username is already taken.';
        } elseif (email_exists($conn, $email)) {
            $error = 'That email is already registered.';
        } elseif (strlen($password) < 6) {
            $error = 'Password must be at least 6 characters.';
        } else {
            if (create_user($conn, $name, $email, $contact, $username, $password, $role)) {
                log_activity($conn, 'Created ' . $role . ' account: ' . $username);
                set_flash('success', 'User account created successfully.');
                redirect('index.php?page=admin');
            }
            $error = 'Could not create the user account.';
        }
    }

    /* ---------------- UPDATE USER ---------------- */
    if ($action === 'update_user' && is_post()) {
        csrf_check();

        $id       = (int)($_GET['id'] ?? 0);
        $name     = trim($_POST['name'] ?? '');
        $email    = trim($_POST['email'] ?? '');
        $contact  = trim($_POST['contact'] ?? '');
        $username = trim($_POST['username'] ?? '');
        $password = $_POST['password'] ?? '';
        $role     = $_POST['role'] ?? '';
        $status   = $_POST['status'] ?? 'active';

        $editing = ['id' => $id, 'name' => $name, 'email' => $email, 'contact' => $contact,
                    'username' => $username, 'role' => $role, 'status' => $status];

        if (is_blank($name) || is_blank($email) || is_blank($contact) || is_blank($username)) {
            $error = 'No field can be left empty. All fields are required.';
        } elseif (!in_array($role, $roles, true) || !in_array($status, $statuses, true)) {
            $error = 'Choose a valid role and status.';
        } elseif (!valid_email($email)) {
            $error = 'Enter a valid email address.';
        } elseif (!valid_contact($contact)) {
            $error = 'Enter a valid contact number.';
        } elseif (username_exists($conn, $username, $id)) {
            $error = 'Another account already uses that username.';
        } elseif (email_exists($conn, $email, $id)) {
            $error = 'Another account already uses that email.';
        } elseif ($password !== '' && strlen($password) < 6) {
            $error = 'Password must be at least 6 characters (or leave it blank).';
        } elseif ($id === (int)$me['id'] && ($role !== 'admin' || $status !== 'active')) {
            $error = 'You cannot remove your own admin privileges or suspend yourself.';
        } else {
            if (update_user($conn, $id, $name, $email, $contact, $username, $role, $status)) {
                if ($password !== '') {
                    update_password($conn, $id, $password);
                }
                log_activity($conn, 'Updated user #' . $id . ' (' . $username . ')');
                set_flash('success', 'User account updated successfully.');
                redirect('index.php?page=admin');
            }
            $error = 'Update failed.';
        }
    }

    /* ---------------- READ ONE USER INTO FORM ---------------- */
    if ($action === 'edit_user' && !$editing) {
        $editing = get_user($conn, (int)($_GET['id'] ?? 0));
        if (!$editing) {
            set_flash('error', 'That account no longer exists.');
            redirect('index.php?page=admin');
        }
    }

    /* ---------------- DELETE USER ---------------- */
    if ($action === 'delete_user') {
        csrf_check();
        $id = (int)($_GET['id'] ?? 0);

        if ($id === (int)$me['id']) {
            set_flash('error', 'You cannot delete your own account.');
        } elseif ($id > 0 && delete_user($conn, $id)) {
            log_activity($conn, 'Deleted account #' . $id);
            set_flash('success', 'Account deleted.');
        } else {
            set_flash('error', 'Could not delete that account.');
        }
        redirect('index.php?page=admin');
    }

    /* ---------------- TOGGLE USER STATUS ---------------- */
    if ($action === 'status') {
        csrf_check();
        $id     = (int)($_GET['id'] ?? 0);
        $status = $_GET['to'] ?? '';

        if ($id === (int)$me['id']) {
            set_flash('error', 'You cannot suspend your own account.');
        } elseif (in_array($status, $statuses, true) && set_user_status($conn, $id, $status)) {
            log_activity($conn, 'Set account #' . $id . ' to ' . $status);
            set_flash('success', 'Account status changed to ' . $status . '.');
        } else {
            set_flash('error', 'Could not update account status.');
        }
        redirect('index.php?page=admin');
    }

    /* ---------------- INSTITUTIONAL VERIFICATION ---------------- */
    if ($action === 'verify' && is_post()) {
        csrf_check();
        $type   = $_POST['type'] ?? '';
        $id     = (int)($_POST['id'] ?? 0);
        $status = $_POST['status'] ?? 'approved';
        $notes  = trim($_POST['notes'] ?? '');

        if ($type === 'agency') {
            verify_agency($conn, $id, $status, $notes);
            log_activity($conn, 'Verified agency profile #' . $id . ' as ' . $status);
            set_flash('success', 'Agency verification updated.');
        } elseif ($type === 'university') {
            verify_university($conn, $id, $status, $notes);
            log_activity($conn, 'Verified university profile #' . $id . ' as ' . $status);
            set_flash('success', 'University verification updated.');
        }
        redirect('index.php?page=admin#verifications');
    }

    /* ---------------- RECORD REVENUE TRANSACTION ---------------- */
    if ($action === 'record_revenue' && is_post()) {
        csrf_check();
        $userId     = (int)($_POST['user_id'] ?? 0);
        $type       = $_POST['transaction_type'] ?? 'package_booking';
        $amount     = (float)($_POST['amount'] ?? 0);
        $rate       = (float)($_POST['commission_rate'] ?? 10);
        $commission = ($amount * $rate) / 100;
        $method     = $_POST['payment_method'] ?? 'Card';
        $status     = $_POST['status'] ?? 'completed';

        if ($userId <= 0 || $amount <= 0) {
            $error = 'Enter a valid user and amount.';
        } else {
            if (record_transaction($conn, $userId, $type, $amount, $commission, $method, $status)) {
                log_activity($conn, 'Recorded revenue entry: ' . money($amount) . ' (' . $type . ')');
                set_flash('success', 'Transaction entry recorded.');
                redirect('index.php?page=admin#revenue');
            }
            $error = 'Could not record transaction.';
        }
    }

    /* ---------------- RESPOND TO FEEDBACK ---------------- */
    if ($action === 'feedback_respond' && is_post()) {
        csrf_check();
        $fbId     = (int)($_POST['feedback_id'] ?? 0);
        $response = trim($_POST['response'] ?? '');
        $status   = $_POST['status'] ?? 'resolved';

        if (is_blank($response)) {
            set_flash('error', 'Response message cannot be empty.');
        } else {
            respond_feedback($conn, $fbId, $response, $status);
            log_activity($conn, 'Responded to student feedback #' . $fbId);
            set_flash('success', 'Feedback response saved.');
        }
        redirect('index.php?page=admin#feedback');
    }

    /* ---------------- DELETE FEEDBACK ---------------- */
    if ($action === 'feedback_delete') {
        csrf_check();
        $id = (int)($_GET['id'] ?? 0);
        delete_feedback($conn, $id);
        log_activity($conn, 'Deleted feedback #' . $id);
        set_flash('success', 'Feedback entry removed.');
        redirect('index.php?page=admin#feedback');
    }

    /* ---------------- CHANGE ADMIN PASSWORD ---------------- */
    if ($action === 'change_password' && is_post()) {
        csrf_check();
        $currentPass = $_POST['current_password'] ?? '';
        $newPass     = $_POST['new_password'] ?? '';
        $confirmPass = $_POST['confirm_password'] ?? '';

        $dbUser = find_user_by_username($conn, $me['username']);

        if (is_blank($currentPass) || is_blank($newPass) || is_blank($confirmPass)) {
            set_flash('error', 'All password fields are required.');
        } elseif (!$dbUser || !password_verify($currentPass, $dbUser['password'])) {
            set_flash('error', 'Current password is incorrect.');
        } elseif (strlen($newPass) < 6) {
            set_flash('error', 'New password must be at least 6 characters.');
        } elseif ($newPass !== $confirmPass) {
            set_flash('error', 'New password and confirmation do not match.');
        } else {
            if (update_password($conn, (int)$me['id'], $newPass)) {
                log_activity($conn, 'Changed administrator password');
                set_flash('success', 'Password changed successfully.');
            } else {
                set_flash('error', 'Could not update password. Please try again.');
            }
        }
        redirect('index.php?page=admin#profile');
    }

    /* ---------------- DATA FOR THE VIEW ---------------- */
    if (!in_array($roleFilter, $roles, true)) {
        $roleFilter = '';
    }
    $users        = get_users($conn, $roleFilter);
    $stats        = get_system_stats($conn);
    $agencies     = get_all_agencies($conn);
    $universities = get_all_universities($conn);
    $transactions = get_all_transactions($conn);
    $feedbacks    = get_all_feedbacks($conn);
    $logs         = get_logs($conn, 15);
    $revSummary   = get_revenue_summary($conn);

    require __DIR__ . '/../views/admin/dashboard.php';
}
