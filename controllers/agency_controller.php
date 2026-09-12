<?php
// ================================================================
// CONTROLLER: AGENCY dashboard
// CRUD  : service packages
// Extras: 1) 5-stage student application progress tracker
//         2) Business performance analytics & visa success reports
//         3) Business consultancy profile manager
// ================================================================

function agency_controller($conn) {
    $action = $_GET['action'] ?? 'dashboard';
    $me     = current_user();
    $userId = (int)$me['id'];

    $agency = get_agency_profile($conn, $userId);
    $agencyId = (int)($agency['id'] ?? 0);

    $error   = '';
    $editing = null;

    /* ---------------- CREATE SERVICE PACKAGE ---------------- */
    if ($action === 'add_package' && is_post()) {
        csrf_check();

        $title    = trim($_POST['title'] ?? '');
        $price    = (float)($_POST['price'] ?? 0);
        $duration = (int)($_POST['duration_weeks'] ?? 4);
        $desc     = trim($_POST['description'] ?? '');
        $features = trim($_POST['features_list'] ?? '');
        $isActive = isset($_POST['is_active']) ? 1 : 0;

        if (is_blank($title) || $price <= 0 || is_blank($desc)) {
            $error = 'Please provide a valid package title, fee, and description.';
        } else {
            if (create_package($conn, $agencyId, $title, $price, $duration, $desc, $features, $isActive)) {
                log_activity($conn, 'Created service package: ' . $title);
                set_flash('success', 'Service package created successfully.');
                redirect('index.php?page=agency');
            }
            $error = 'Could not create package.';
        }
    }

    /* ---------------- UPDATE SERVICE PACKAGE ---------------- */
    if ($action === 'update_package' && is_post()) {
        csrf_check();

        $id       = (int)($_GET['id'] ?? 0);
        $title    = trim($_POST['title'] ?? '');
        $price    = (float)($_POST['price'] ?? 0);
        $duration = (int)($_POST['duration_weeks'] ?? 4);
        $desc     = trim($_POST['description'] ?? '');
        $features = trim($_POST['features_list'] ?? '');
        $isActive = isset($_POST['is_active']) ? 1 : 0;

        $editing = ['id' => $id, 'title' => $title, 'price' => $price, 'duration_weeks' => $duration,
                    'description' => $desc, 'features_list' => $features, 'is_active' => $isActive];

        if (is_blank($title) || $price <= 0 || is_blank($desc)) {
            $error = 'Please provide valid package details.';
        } else {
            if (update_package($conn, $id, $agencyId, $title, $price, $duration, $desc, $features, $isActive)) {
                log_activity($conn, 'Updated service package #' . $id);
                set_flash('success', 'Service package updated.');
                redirect('index.php?page=agency');
            }
            $error = 'Update failed.';
        }
    }

    /* ---------------- EDIT SERVICE PACKAGE (READ ROW) ---------------- */
    if ($action === 'edit_package' && !$editing) {
        $editing = get_package_by_id($conn, (int)($_GET['id'] ?? 0));
        if (!$editing || (int)$editing['agency_id'] !== $agencyId) {
            set_flash('error', 'Package not found.');
            redirect('index.php?page=agency');
        }
    }

    /* ---------------- DELETE SERVICE PACKAGE ---------------- */
    if ($action === 'delete_package') {
        csrf_check();
        $id = (int)($_GET['id'] ?? 0);
        if (delete_package($conn, $id, $agencyId)) {
            log_activity($conn, 'Deleted service package #' . $id);
            set_flash('success', 'Package deleted.');
        } else {
            set_flash('error', 'Could not delete package.');
        }
        redirect('index.php?page=agency');
    }

    /* ---------------- UPDATE APPLICATION STAGE (PIPELINE) ---------------- */
    if ($action === 'update_application' && is_post()) {
        csrf_check();
        $appId   = (int)($_POST['application_id'] ?? 0);
        $status  = $_POST['status'] ?? 'under_review';
        $remarks = trim($_POST['remarks'] ?? '');

        if (update_application_status($conn, $appId, $status, $remarks)) {
            log_activity($conn, 'Updated student application #' . $appId . ' status to ' . $status);
            set_flash('success', 'Application milestone updated.');
        } else {
            set_flash('error', 'Could not update application.');
        }
        redirect('index.php?page=agency#applications');
    }

    /* ---------------- UPDATE AGENCY PROFILE ---------------- */
    if ($action === 'update_profile' && is_post()) {
        csrf_check();
        $name        = trim($_POST['name'] ?? '');
        $contact     = trim($_POST['contact'] ?? '');
        $companyName = trim($_POST['company_name'] ?? '');
        $licenseNo   = trim($_POST['license_no'] ?? '');
        $address     = trim($_POST['address'] ?? '');
        $website     = trim($_POST['website'] ?? '');
        $bio         = trim($_POST['bio'] ?? '');

        if (is_blank($name) || is_blank($companyName)) {
            $error = 'Name and Company Name are required.';
        } else {
            mysqli_query($conn, "UPDATE users SET name = '" . mysqli_real_escape_string($conn, $name) . "', contact = '" . mysqli_real_escape_string($conn, $contact) . "' WHERE id = $userId");
            update_agency_profile($conn, $userId, $companyName, $licenseNo, $address, $website, $bio);
            $_SESSION['user']['name'] = $name;
            $_SESSION['user']['contact'] = $contact;

            log_activity($conn, 'Updated agency business profile');
            set_flash('success', 'Profile updated successfully.');
            redirect('index.php?page=agency#profile');
        }
    }

    /* ---------------- CHANGE PASSWORD ---------------- */
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
            if (update_password($conn, $userId, $newPass)) {
                log_activity($conn, 'Changed account password');
                set_flash('success', 'Password changed successfully.');
            } else {
                set_flash('error', 'Could not update password. Please try again.');
            }
        }
        redirect('index.php?page=agency#profile');
    }

    /* ---------------- DATA FOR THE VIEW ---------------- */
    $statusFilter       = $_GET['app_status'] ?? '';
    $packages           = get_agency_packages($conn, $agencyId);
    $applications       = get_agency_applications($conn, $agencyId, $statusFilter);
    $agencyTransactions = get_agency_transactions($conn, $agencyId);
    $stats              = get_agency_stats($conn, $agencyId, $userId);

    require __DIR__ . '/../views/agency/dashboard.php';
}
