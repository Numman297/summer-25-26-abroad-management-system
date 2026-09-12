<?php
// ================================================================
// CONTROLLER: UNIVERSITY REPRESENTATIVE dashboard
// CRUD  : degree programs & academic courses
// Extras: 1) Intake admissions review & offer letter issuance
//         2) Scholarship schemes management & grant awarding
//         3) Program entry cutoffs & visa guidelines setup
// ================================================================

function university_controller($conn) {
    $action = $_GET['action'] ?? 'dashboard';
    $me     = current_user();
    $userId = (int)$me['id'];

    $uni = get_university_profile($conn, $userId);
    $uniId = (int)($uni['id'] ?? 0);

    $error   = '';
    $editingProg = null;
    $editingSch  = null;

    /* ---------------- CREATE PROGRAM ---------------- */
    if ($action === 'add_program' && is_post()) {
        csrf_check();

        $title    = trim($_POST['title'] ?? '');
        $degree   = $_POST['degree_level'] ?? 'Master';
        $dept     = trim($_POST['department'] ?? '');
        $duration = (float)($_POST['duration_years'] ?? 2.0);
        $fee      = (float)($_POST['tuition_fee'] ?? 0);
        $intakes  = trim($_POST['intake_season'] ?? 'Fall 2026');
        $desc     = trim($_POST['description'] ?? '');

        if (is_blank($title) || $fee <= 0 || is_blank($dept)) {
            $error = 'Fill in all program fields with valid tuition fee.';
        } else {
            if (create_program($conn, $uniId, $title, $degree, $dept, $duration, $fee, $intakes, $desc)) {
                log_activity($conn, 'Created degree program: ' . $title);
                set_flash('success', 'Program added to catalog.');
                redirect('index.php?page=university');
            }
            $error = 'Could not create program.';
        }
    }

    /* ---------------- UPDATE PROGRAM ---------------- */
    if ($action === 'update_program' && is_post()) {
        csrf_check();

        $id       = (int)($_GET['id'] ?? 0);
        $title    = trim($_POST['title'] ?? '');
        $degree   = $_POST['degree_level'] ?? 'Master';
        $dept     = trim($_POST['department'] ?? '');
        $duration = (float)($_POST['duration_years'] ?? 2.0);
        $fee      = (float)($_POST['tuition_fee'] ?? 0);
        $intakes  = trim($_POST['intake_season'] ?? 'Fall 2026');
        $desc     = trim($_POST['description'] ?? '');

        $editingProg = ['id' => $id, 'title' => $title, 'degree_level' => $degree, 'department' => $dept,
                        'duration_years' => $duration, 'tuition_fee' => $fee, 'intake_season' => $intakes, 'description' => $desc];

        if (is_blank($title) || $fee <= 0) {
            $error = 'Provide valid program details.';
        } else {
            if (update_program($conn, $id, $uniId, $title, $degree, $dept, $duration, $fee, $intakes, $desc)) {
                log_activity($conn, 'Updated program #' . $id);
                set_flash('success', 'Program updated.');
                redirect('index.php?page=university');
            }
            $error = 'Update failed.';
        }
    }

    /* ---------------- READ PROGRAM FOR EDIT ---------------- */
    if ($action === 'edit_program' && !$editingProg) {
        $editingProg = get_program_by_id($conn, (int)($_GET['id'] ?? 0));
        if (!$editingProg) {
            set_flash('error', 'Program not found.');
            redirect('index.php?page=university');
        }
    }

    /* ---------------- DELETE PROGRAM ---------------- */
    if ($action === 'delete_program') {
        csrf_check();
        $id = (int)($_GET['id'] ?? 0);
        if (delete_program($conn, $id, $uniId)) {
            log_activity($conn, 'Deleted program #' . $id);
            set_flash('success', 'Program deleted.');
        } else {
            set_flash('error', 'Could not delete program.');
        }
        redirect('index.php?page=university');
    }

    /* ---------------- SAVE PROGRAM REQUIREMENTS ---------------- */
    if ($action === 'save_requirements' && is_post()) {
        csrf_check();
        $progId    = (int)($_POST['program_id'] ?? 0);
        $cgpa      = (float)($_POST['min_cgpa'] ?? 3.0);
        $ielts     = (float)($_POST['min_ielts'] ?? 6.5);
        $toefl     = (int)($_POST['min_toefl'] ?? 80);
        $gre       = (int)($_POST['min_gre'] ?? 300);
        $visaNotes = trim($_POST['visa_guidelines'] ?? '');
        $docs      = trim($_POST['documents_required'] ?? '');

        if ($progId > 0 && save_program_requirements($conn, $progId, $cgpa, $ielts, $toefl, $gre, $visaNotes, $docs)) {
            log_activity($conn, 'Updated admission requirements for program #' . $progId);
            set_flash('success', 'Program requirements saved.');
        } else {
            set_flash('error', 'Could not save requirements.');
        }
        redirect('index.php?page=university#requirements');
    }

    /* ---------------- CREATE SCHOLARSHIP ---------------- */
    if ($action === 'add_scholarship' && is_post()) {
        csrf_check();
        $title    = trim($_POST['title'] ?? '');
        $amount   = (float)($_POST['grant_amount'] ?? 0);
        $coverage = $_POST['coverage_type'] ?? 'Partial Tuition';
        $criteria = trim($_POST['eligibility_criteria'] ?? '');
        $deadline = $_POST['deadline'] ?? date('Y-m-d', strtotime('+90 days'));
        $maxRec   = (int)($_POST['max_recipients'] ?? 5);
        $isActive = isset($_POST['is_active']) ? 1 : 0;

        if (is_blank($title) || $amount <= 0 || is_blank($criteria)) {
            $error = 'Please provide valid scholarship details.';
        } else {
            if (create_scholarship($conn, $uniId, $title, $amount, $coverage, $criteria, $deadline, $maxRec, $isActive)) {
                log_activity($conn, 'Created scholarship scheme: ' . $title);
                set_flash('success', 'Scholarship created.');
                redirect('index.php?page=university#scholarships');
            }
            $error = 'Could not create scholarship.';
        }
    }

    /* ---------------- UPDATE SCHOLARSHIP ---------------- */
    if ($action === 'update_scholarship' && is_post()) {
        csrf_check();
        $id       = (int)($_GET['id'] ?? 0);
        $title    = trim($_POST['title'] ?? '');
        $amount   = (float)($_POST['grant_amount'] ?? 0);
        $coverage = $_POST['coverage_type'] ?? 'Partial Tuition';
        $criteria = trim($_POST['eligibility_criteria'] ?? '');
        $deadline = $_POST['deadline'] ?? date('Y-m-d');
        $maxRec   = (int)($_POST['max_recipients'] ?? 5);
        $isActive = isset($_POST['is_active']) ? 1 : 0;

        $editingSch = ['id' => $id, 'title' => $title, 'grant_amount' => $amount, 'coverage_type' => $coverage,
                       'eligibility_criteria' => $criteria, 'deadline' => $deadline, 'max_recipients' => $maxRec, 'is_active' => $isActive];

        if (is_blank($title) || $amount <= 0) {
            $error = 'Provide valid scholarship details.';
        } else {
            if (update_scholarship($conn, $id, $uniId, $title, $amount, $coverage, $criteria, $deadline, $maxRec, $isActive)) {
                log_activity($conn, 'Updated scholarship #' . $id);
                set_flash('success', 'Scholarship updated.');
                redirect('index.php?page=university#scholarships');
            }
            $error = 'Update failed.';
        }
    }

    /* ---------------- DELETE SCHOLARSHIP ---------------- */
    if ($action === 'delete_scholarship') {
        csrf_check();
        $id = (int)($_GET['id'] ?? 0);
        if (delete_scholarship($conn, $id, $uniId)) {
            log_activity($conn, 'Deleted scholarship #' . $id);
            set_flash('success', 'Scholarship deleted.');
        } else {
            set_flash('error', 'Could not delete scholarship.');
        }
        redirect('index.php?page=university#scholarships');
    }

    /* ---------------- DECIDE SCHOLARSHIP GRANT ---------------- */
    if ($action === 'decide_scholarship' && is_post()) {
        csrf_check();
        $appId  = (int)($_POST['app_id'] ?? 0);
        $status = $_POST['status'] ?? 'awarded';

        if (decide_scholarship($conn, $appId, $status)) {
            log_activity($conn, 'Updated scholarship application #' . $appId . ' status to ' . $status);
            set_flash('success', 'Scholarship applicant status set to ' . $status . '.');
        } else {
            set_flash('error', 'Could not update scholarship applicant.');
        }
        redirect('index.php?page=university#scholarships');
    }

    /* ---------------- REVIEW ADMISSION APPLICATION & ISSUE OFFER LETTER ---------------- */
    if ($action === 'admission_decision' && is_post()) {
        csrf_check();
        $appId   = (int)($_POST['application_id'] ?? 0);
        $status  = $_POST['status'] ?? 'under_review';
        $remarks = trim($_POST['remarks'] ?? '');

        if (update_admission_decision($conn, $appId, $status, $remarks)) {
            log_activity($conn, 'Processed admission application #' . $appId . ' as ' . $status);
            set_flash('success', 'Admission decision saved and offer status updated.');
        } else {
            set_flash('error', 'Could not save admission decision.');
        }
        redirect('index.php?page=university#admissions');
    }

    /* ---------------- UPDATE UNIVERSITY PROFILE ---------------- */
    if ($action === 'update_profile' && is_post()) {
        csrf_check();
        $name    = trim($_POST['name'] ?? '');
        $contact = trim($_POST['contact'] ?? '');
        $uniName = trim($_POST['uni_name'] ?? '');
        $country = trim($_POST['country'] ?? '');
        $city    = trim($_POST['city'] ?? '');
        $ranking = trim($_POST['ranking'] ?? '');
        $website = trim($_POST['website'] ?? '');
        $bio     = trim($_POST['bio'] ?? '');

        if (is_blank($name) || is_blank($uniName)) {
            $error = 'Representative Name and University Name are required.';
        } else {
            mysqli_query($conn, "UPDATE users SET name = '" . mysqli_real_escape_string($conn, $name) . "', contact = '" . mysqli_real_escape_string($conn, $contact) . "' WHERE id = $userId");
            update_university_profile($conn, $userId, $uniName, $country, $city, $ranking, $website, $bio);
            $_SESSION['user']['name'] = $name;
            $_SESSION['user']['contact'] = $contact;

            log_activity($conn, 'Updated university profile');
            set_flash('success', 'University profile saved.');
            redirect('index.php?page=university#profile');
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
        redirect('index.php?page=university#profile');
    }

    /* ---------------- DATA FOR THE VIEW ---------------- */
    $intakeFilter = $_GET['intake'] ?? '';
    $programs     = get_university_programs($conn, $uniId);
    $scholarships = get_university_scholarships($conn, $uniId);
    $schApps      = get_scholarship_applications($conn, $uniId);
    $applicants   = get_university_applicants($conn, $uniId, $intakeFilter);
    $stats        = get_university_stats($conn, $uniId);

    require __DIR__ . '/../views/university/dashboard.php';
}
