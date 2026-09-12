<?php


function student_controller($conn) {
    $action = $_GET['action'] ?? 'dashboard';
    $me     = current_user();
    $userId = (int)$me['id'];

    $student = get_student_profile($conn, $userId);
    $error   = '';
    $editingEdu = null;

    /* ---------------- ADD EDUCATIONAL QUALIFICATION ---------------- */
    if ($action === 'add_education' && is_post()) {
        csrf_check();

        $degree    = trim($_POST['degree_title'] ?? '');
        $institute = trim($_POST['institute_name'] ?? '');
        $board     = trim($_POST['board_or_uni'] ?? '');
        $year      = (int)($_POST['passing_year'] ?? date('Y'));
        $result    = trim($_POST['result_cgpa'] ?? '');

        if (is_blank($degree) || is_blank($institute) || is_blank($result)) {
            $error = 'Fill in all qualification fields.';
        } else {
            if (add_education($conn, $userId, $degree, $institute, $board, $year, $result)) {
                log_activity($conn, 'Added academic qualification: ' . $degree);
                set_flash('success', 'Educational record added.');
                redirect('index.php?page=student#education');
            }
            $error = 'Could not add qualification.';
        }
    }

    /* ---------------- UPDATE EDUCATIONAL QUALIFICATION ---------------- */
    if ($action === 'update_education' && is_post()) {
        csrf_check();
        $id        = (int)($_GET['id'] ?? 0);
        $degree    = trim($_POST['degree_title'] ?? '');
        $institute = trim($_POST['institute_name'] ?? '');
        $board     = trim($_POST['board_or_uni'] ?? '');
        $year      = (int)($_POST['passing_year'] ?? date('Y'));
        $result    = trim($_POST['result_cgpa'] ?? '');

        $editingEdu = ['id' => $id, 'degree_title' => $degree, 'institute_name' => $institute,
                       'board_or_uni' => $board, 'passing_year' => $year, 'result_cgpa' => $result];

        if (is_blank($degree) || is_blank($institute) || is_blank($result)) {
            $error = 'Fill in all qualification fields.';
        } else {
            if (update_education($conn, $id, $userId, $degree, $institute, $board, $year, $result)) {
                log_activity($conn, 'Updated qualification #' . $id);
                set_flash('success', 'Educational record updated.');
                redirect('index.php?page=student#education');
            }
            $error = 'Update failed.';
        }
    }

    /* ---------------- DELETE EDUCATIONAL QUALIFICATION ---------------- */
    if ($action === 'delete_education') {
        csrf_check();
        $id = (int)($_GET['id'] ?? 0);
        if (delete_education($conn, $id, $userId)) {
            log_activity($conn, 'Deleted qualification #' . $id);
            set_flash('success', 'Record removed.');
        } else {
            set_flash('error', 'Could not delete record.');
        }
        redirect('index.php?page=student#education');
    }

    /* ---------------- LOG MOCK TEST SCORE ---------------- */
    if ($action === 'add_mock_test' && is_post()) {
        csrf_check();

        $type      = $_POST['test_type'] ?? 'IELTS';
        $date      = $_POST['test_date'] ?? date('Y-m-d');
        $reading   = (float)($_POST['reading_score'] ?? 0);
        $listening = (float)($_POST['listening_score'] ?? 0);
        $writing   = (float)($_POST['writing_score'] ?? 0);
        $speaking  = (float)($_POST['speaking_score'] ?? 0);
        $notes     = trim($_POST['notes'] ?? '');

        if ($type === 'IELTS') {
            $rawAvg = ($reading + $listening + $writing + $speaking) / 4;
            $overall = round($rawAvg * 2) / 2;
        } elseif ($type === 'GRE' || $type === 'SAT') {
            $overall = $reading + $listening + $writing + $speaking;
        } else {
            $overall = round(($reading + $listening + $writing + $speaking) / 4, 1);
        }

        if (add_mock_test($conn, $userId, $type, $date, $reading, $listening, $writing, $speaking, $overall, $notes)) {
            log_activity($conn, 'Logged ' . $type . ' mock test score (Overall: ' . $overall . ')');
            set_flash('success', 'Mock test score saved. Overall Band Score: ' . $overall);
            redirect('index.php?page=student#mock_tests');
        }
        $error = 'Could not save test score.';
    }

    /* ---------------- DELETE MOCK TEST SCORE ---------------- */
    if ($action === 'delete_mock_test') {
        csrf_check();
        $id = (int)($_GET['id'] ?? 0);
        if (delete_mock_test($conn, $id, $userId)) {
            log_activity($conn, 'Deleted mock test #' . $id);
            set_flash('success', 'Test record deleted.');
        } else {
            set_flash('error', 'Could not delete test record.');
        }
        redirect('index.php?page=student#mock_tests');
    }

    /* ---------------- CREATE TRAVEL COMPANION POST ---------------- */
    if ($action === 'add_travel_post' && is_post()) {
        csrf_check();

        $country  = trim($_POST['destination_country'] ?? '');
        $city     = trim($_POST['destination_city'] ?? '');
        $uni      = trim($_POST['destination_uni'] ?? '');
        $date     = $_POST['travel_date'] ?? date('Y-m-d', strtotime('+30 days'));
        $airline  = trim($_POST['airline'] ?? '');
        $flightNo = trim($_POST['flight_no'] ?? '');
        $contact  = trim($_POST['contact_info'] ?? '');
        $notes    = trim($_POST['notes'] ?? '');

        if (is_blank($country) || is_blank($city) || is_blank($contact)) {
            $error = 'Destination and contact info are required.';
        } else {
            if (create_travel_post($conn, $userId, $country, $city, $uni, $date, $airline, $flightNo, $contact, $notes)) {
                log_activity($conn, 'Posted travel companion request to ' . $city . ', ' . $country);
                set_flash('success', 'Travel post published.');
                redirect('index.php?page=student#travel');
            }
            $error = 'Could not post travel request.';
        }
    }

    /* ---------------- TOGGLE / CLOSE TRAVEL POST ---------------- */
    if ($action === 'close_travel_post') {
        csrf_check();
        $id = (int)($_GET['id'] ?? 0);
        toggle_travel_status($conn, $id, $userId, 'closed');
        log_activity($conn, 'Closed travel post #' . $id);
        set_flash('success', 'Travel post marked as closed.');
        redirect('index.php?page=student#travel');
    }

    /* ---------------- DELETE TRAVEL POST ---------------- */
    if ($action === 'delete_travel_post') {
        csrf_check();
        $id = (int)($_GET['id'] ?? 0);
        if (delete_travel_post($conn, $id, $userId)) {
            log_activity($conn, 'Deleted travel post #' . $id);
            set_flash('success', 'Travel post removed.');
        } else {
            set_flash('error', 'Could not delete post.');
        }
        redirect('index.php?page=student#travel');
    }

    /* ---------------- APPLY TO UNIVERSITY PROGRAM ---------------- */
    if ($action === 'apply_program' && is_post()) {
        csrf_check();
        $progId = (int)($_POST['program_id'] ?? 0);
        $intake = $_POST['intake'] ?? 'Fall 2026';
        $notes  = trim($_POST['remarks'] ?? '');

        if ($progId > 0 && apply_university_program($conn, $userId, $progId, $intake, $notes)) {
            log_activity($conn, 'Submitted application for program #' . $progId);
            set_flash('success', 'Application submitted to university admissions.');
            redirect('index.php?page=student#applications');
        }
        $error = 'Could not submit program application.';
    }

    /* ---------------- BOOK AGENCY SERVICE PACKAGE ---------------- */
    if ($action === 'book_package' && is_post()) {
        csrf_check();
        $pkgId    = (int)($_POST['package_id'] ?? 0);
        $agencyId = (int)($_POST['agency_id'] ?? 0);
        $intake   = $_POST['intake'] ?? 'Fall 2026';
        $notes    = trim($_POST['remarks'] ?? '');
        $price    = (float)($_POST['package_price'] ?? 0);

        if ($pkgId > 0 && book_agency_package($conn, $userId, $agencyId, $pkgId, $intake, $notes, $price)) {
            log_activity($conn, 'Booked agency package #' . $pkgId . ' (' . money($price) . ')');
            set_flash('success', 'Consultancy package booked successfully.');
            redirect('index.php?page=student#applications');
        }
        $error = 'Could not book package.';
    }

    /* ---------------- APPLY FOR SCHOLARSHIP ---------------- */
    if ($action === 'apply_scholarship' && is_post()) {
        csrf_check();
        $schId = (int)($_POST['scholarship_id'] ?? 0);
        $stmt  = trim($_POST['statement'] ?? '');

        if (is_blank($stmt)) {
            $error = 'Please write a brief statement of purpose for the scholarship.';
        } else {
            if (apply_scholarship($conn, $userId, $schId, $stmt)) {
                log_activity($conn, 'Applied for scholarship scheme #' . $schId);
                set_flash('success', 'Scholarship application submitted.');
                redirect('index.php?page=student#applications');
            }
            $error = 'Could not submit scholarship application.';
        }
    }

    /* ---------------- SUBMIT FEEDBACK / REVIEW ---------------- */
    if ($action === 'submit_feedback' && is_post()) {
        csrf_check();
        $target  = $_POST['target_type'] ?? 'platform';
        $rating  = (int)($_POST['rating'] ?? 5);
        $subject = trim($_POST['subject'] ?? '');
        $message = trim($_POST['message'] ?? '');

        if (is_blank($subject) || is_blank($message)) {
            $error = 'Subject and message are required.';
        } else {
            if (submit_feedback($conn, $userId, $target, $rating, $subject, $message)) {
                log_activity($conn, 'Submitted ' . $rating . '-star feedback');
                set_flash('success', 'Thank you! Your feedback has been received.');
                redirect('index.php?page=student#feedback');
            }
            $error = 'Could not submit feedback.';
        }
    }

    /* ---------------- UPDATE STUDENT PROFILE ---------------- */
    if ($action === 'update_profile' && is_post()) {
        csrf_check();
        $name          = trim($_POST['name'] ?? '');
        $contact       = trim($_POST['contact'] ?? '');
        $passport      = trim($_POST['passport_no'] ?? '');
        $targetCountry = trim($_POST['target_country'] ?? '');
        $targetDegree  = $_POST['target_degree'] ?? 'Bachelor';
        $bio           = trim($_POST['bio'] ?? '');

        if (is_blank($name)) {
            $error = 'Full name is required.';
        } else {
            mysqli_query($conn, "UPDATE users SET name = '" . mysqli_real_escape_string($conn, $name) . "', contact = '" . mysqli_real_escape_string($conn, $contact) . "' WHERE id = $userId");
            update_student_profile($conn, $userId, $passport, $targetCountry, $targetDegree, $bio);
            $_SESSION['user']['name'] = $name;
            $_SESSION['user']['contact'] = $contact;

            log_activity($conn, 'Updated student academic profile');
            set_flash('success', 'Profile updated successfully.');
            redirect('index.php?page=student#profile');
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
        redirect('index.php?page=student#profile');
    }

    /* ---------------- DATA FOR THE VIEW ---------------- */
    $education    = get_student_education($conn, $userId);
    $mockTests    = get_student_mock_tests($conn, $userId);
    $travelPosts  = get_all_travel_companions($conn, $_GET['country'] ?? '');
    $myPosts      = get_my_travel_posts($conn, $userId);
    $applications = get_student_applications($conn, $userId);
    $feedbacks    = get_student_feedbacks($conn, $userId);
    $stats        = get_student_stats($conn, $userId);

    // Explore catalog
    $explorePrograms     = get_explore_programs($conn);
    $explorePackages     = get_explore_packages($conn);
    $exploreScholarships = get_explore_scholarships($conn);

    require __DIR__ . '/../views/student/dashboard.php';
}
