<?php
// ================================================================
// MODEL: student (profiles, mock tests, travel companions, education, explore)
// ================================================================

function get_student_profile($conn, $userId) {
    $stmt = mysqli_prepare($conn, "SELECT s.*, u.name, u.email, u.contact FROM student_profiles s JOIN users u ON s.user_id = u.id WHERE s.user_id = ?");
    mysqli_stmt_bind_param($stmt, 'i', $userId);
    mysqli_stmt_execute($stmt);
    $row = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
    mysqli_stmt_close($stmt);
    return $row;
}

function update_student_profile($conn, $userId, $passport, $targetCountry, $targetDegree, $bio) {
    $stmt = mysqli_prepare($conn, "UPDATE student_profiles SET passport_no = ?, target_country = ?, target_degree = ?, bio = ? WHERE user_id = ?");
    mysqli_stmt_bind_param($stmt, 'ssssi', $passport, $targetCountry, $targetDegree, $bio, $userId);
    $ok = mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);
    return $ok;
}

// Educational records CRUD
function get_student_education($conn, $studentId) {
    $stmt = mysqli_prepare($conn, "SELECT * FROM student_education WHERE student_id = ? ORDER BY passing_year DESC");
    mysqli_stmt_bind_param($stmt, 'i', $studentId);
    mysqli_stmt_execute($stmt);
    $rows = mysqli_fetch_all(mysqli_stmt_get_result($stmt), MYSQLI_ASSOC);
    mysqli_stmt_close($stmt);
    return $rows;
}

function add_education($conn, $studentId, $degree, $institute, $board, $year, $result) {
    $stmt = mysqli_prepare($conn, "INSERT INTO student_education (student_id, degree_title, institute_name, board_or_uni, passing_year, result_cgpa) VALUES (?, ?, ?, ?, ?, ?)");
    mysqli_stmt_bind_param($stmt, 'isssis', $studentId, $degree, $institute, $board, $year, $result);
    $ok = mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);
    return $ok;
}

function update_education($conn, $id, $studentId, $degree, $institute, $board, $year, $result) {
    $stmt = mysqli_prepare($conn, "UPDATE student_education SET degree_title = ?, institute_name = ?, board_or_uni = ?, passing_year = ?, result_cgpa = ? WHERE id = ? AND student_id = ?");
    mysqli_stmt_bind_param($stmt, 'sssisii', $degree, $institute, $board, $year, $result, $id, $studentId);
    $ok = mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);
    return $ok;
}

function delete_education($conn, $id, $studentId) {
    $stmt = mysqli_prepare($conn, "DELETE FROM student_education WHERE id = ? AND student_id = ?");
    mysqli_stmt_bind_param($stmt, 'ii', $id, $studentId);
    $ok = mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);
    return $ok;
}

// Mock tests CRUD
function get_student_mock_tests($conn, $studentId) {
    $stmt = mysqli_prepare($conn, "SELECT * FROM mock_tests WHERE student_id = ? ORDER BY test_date DESC");
    mysqli_stmt_bind_param($stmt, 'i', $studentId);
    mysqli_stmt_execute($stmt);
    $rows = mysqli_fetch_all(mysqli_stmt_get_result($stmt), MYSQLI_ASSOC);
    mysqli_stmt_close($stmt);
    return $rows;
}

function add_mock_test($conn, $studentId, $type, $date, $reading, $listening, $writing, $speaking, $overall, $notes) {
    $stmt = mysqli_prepare($conn, "INSERT INTO mock_tests (student_id, test_type, test_date, reading_score, listening_score, writing_score, speaking_score, overall_score, notes) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
    mysqli_stmt_bind_param($stmt, 'issddddds', $studentId, $type, $date, $reading, $listening, $writing, $speaking, $overall, $notes);
    $ok = mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);
    return $ok;
}

function delete_mock_test($conn, $id, $studentId) {
    $stmt = mysqli_prepare($conn, "DELETE FROM mock_tests WHERE id = ? AND student_id = ?");
    mysqli_stmt_bind_param($stmt, 'ii', $id, $studentId);
    $ok = mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);
    return $ok;
}

// Travel companion / flight buddy posts CRUD
function get_all_travel_companions($conn, $countryFilter = '') {
    if ($countryFilter === '') {
        $sql = "SELECT t.*, u.name as student_name, u.email as student_email, u.contact as student_phone
                FROM travel_companions t
                JOIN users u ON t.student_id = u.id
                ORDER BY t.status = 'open' DESC, t.travel_date ASC";
        $res = mysqli_query($conn, $sql);
    } else {
        $sql = "SELECT t.*, u.name as student_name, u.email as student_email, u.contact as student_phone
                FROM travel_companions t
                JOIN users u ON t.student_id = u.id
                WHERE t.destination_country LIKE ?
                ORDER BY t.status = 'open' DESC, t.travel_date ASC";
        $like = '%' . $countryFilter . '%';
        $stmt = mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($stmt, 's', $like);
        mysqli_stmt_execute($stmt);
        $res = mysqli_stmt_get_result($stmt);
    }
    return mysqli_fetch_all($res, MYSQLI_ASSOC);
}

function get_my_travel_posts($conn, $studentId) {
    $stmt = mysqli_prepare($conn, "SELECT * FROM travel_companions WHERE student_id = ? ORDER BY id DESC");
    mysqli_stmt_bind_param($stmt, 'i', $studentId);
    mysqli_stmt_execute($stmt);
    $rows = mysqli_fetch_all(mysqli_stmt_get_result($stmt), MYSQLI_ASSOC);
    mysqli_stmt_close($stmt);
    return $rows;
}

function create_travel_post($conn, $studentId, $country, $city, $uni, $date, $airline, $flightNo, $contact, $notes) {
    $stmt = mysqli_prepare($conn, "INSERT INTO travel_companions (student_id, destination_country, destination_city, destination_uni, travel_date, airline, flight_no, contact_info, notes, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 'open')");
    mysqli_stmt_bind_param($stmt, 'issssssss', $studentId, $country, $city, $uni, $date, $airline, $flightNo, $contact, $notes);
    $ok = mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);
    return $ok;
}

function toggle_travel_status($conn, $id, $studentId, $status) {
    $stmt = mysqli_prepare($conn, "UPDATE travel_companions SET status = ? WHERE id = ? AND student_id = ?");
    mysqli_stmt_bind_param($stmt, 'sii', $status, $id, $studentId);
    $ok = mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);
    return $ok;
}

function delete_travel_post($conn, $id, $studentId) {
    $stmt = mysqli_prepare($conn, "DELETE FROM travel_companions WHERE id = ? AND student_id = ?");
    mysqli_stmt_bind_param($stmt, 'ii', $id, $studentId);
    $ok = mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);
    return $ok;
}

// Student applications list & submission
function get_student_applications($conn, $studentId) {
    $stmt = mysqli_prepare($conn, "SELECT a.*, p.title as program_title, u.uni_name, pkg.title as package_title, ag.company_name
            FROM student_applications a
            LEFT JOIN programs p ON a.program_id = p.id
            LEFT JOIN university_profiles u ON p.university_id = u.id
            LEFT JOIN service_packages pkg ON a.package_id = pkg.id
            LEFT JOIN agency_profiles ag ON a.agency_id = ag.id
            WHERE a.student_id = ?
            ORDER BY a.id DESC");
    mysqli_stmt_bind_param($stmt, 'i', $studentId);
    mysqli_stmt_execute($stmt);
    $rows = mysqli_fetch_all(mysqli_stmt_get_result($stmt), MYSQLI_ASSOC);
    mysqli_stmt_close($stmt);
    return $rows;
}

function apply_university_program($conn, $studentId, $programId, $intake, $notes) {
    $stmt = mysqli_prepare($conn, "INSERT INTO student_applications (student_id, program_id, intake, application_type, status, remarks) VALUES (?, ?, ?, 'university', 'pending', ?)");
    mysqli_stmt_bind_param($stmt, 'iiss', $studentId, $programId, $intake, $notes);
    $ok = mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);
    return $ok;
}

function book_agency_package($conn, $studentId, $agencyId, $packageId, $intake, $notes, $packagePrice) {
    $stmt = mysqli_prepare($conn, "INSERT INTO student_applications (student_id, agency_id, package_id, intake, application_type, status, remarks) VALUES (?, ?, ?, ?, 'agency', 'pending', ?)");
    mysqli_stmt_bind_param($stmt, 'iiiss', $studentId, $agencyId, $packageId, $intake, $notes);
    $ok = mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);

    if ($ok) {
        $commission = ($packagePrice * COMMISSION_RATE) / 100;
        $tStmt = mysqli_prepare($conn, "INSERT INTO revenue_transactions (transaction_type, user_id, related_id, amount, platform_commission, payment_method, status) VALUES ('package_booking', ?, ?, ?, ?, 'bKash / Card', 'completed')");
        mysqli_stmt_bind_param($tStmt, 'iidd', $studentId, $packageId, $packagePrice, $commission);
        mysqli_stmt_execute($tStmt);
        mysqli_stmt_close($tStmt);
    }
    return $ok;
}

function apply_scholarship($conn, $studentId, $scholarshipId, $statement) {
    $stmt = mysqli_prepare($conn, "INSERT INTO scholarship_applications (scholarship_id, student_id, statement, status) VALUES (?, ?, ?, 'applied')");
    mysqli_stmt_bind_param($stmt, 'iis', $scholarshipId, $studentId, $statement);
    $ok = mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);
    return $ok;
}

// Explore directory
function get_explore_programs($conn) {
    $sql = "SELECT p.*, u.uni_name, u.country, u.city, u.ranking,
                   r.min_cgpa, r.min_ielts, r.min_toefl, r.min_gre, r.documents_required
            FROM programs p
            JOIN university_profiles u ON p.university_id = u.id
            LEFT JOIN program_requirements r ON p.id = r.program_id
            WHERE u.verified_status = 'approved'
            ORDER BY p.id DESC";
    $res = mysqli_query($conn, $sql);
    return mysqli_fetch_all($res, MYSQLI_ASSOC);
}

function get_explore_packages($conn) {
    $sql = "SELECT p.*, a.company_name, a.address, a.license_no
            FROM service_packages p
            JOIN agency_profiles a ON p.agency_id = a.id
            WHERE p.is_active = 1 AND a.verified_status = 'approved'
            ORDER BY p.id DESC";
    $res = mysqli_query($conn, $sql);
    return mysqli_fetch_all($res, MYSQLI_ASSOC);
}

function get_explore_scholarships($conn) {
    $sql = "SELECT s.*, u.uni_name, u.country
            FROM scholarships s
            JOIN university_profiles u ON s.university_id = u.id
            WHERE s.is_active = 1 AND u.verified_status = 'approved' AND s.deadline >= CURDATE()
            ORDER BY s.id DESC";
    $res = mysqli_query($conn, $sql);
    return mysqli_fetch_all($res, MYSQLI_ASSOC);
}

// Student stats
function get_student_stats($conn, $studentId) {
    $stats = [
        'mock_tests' => 0, 'best_score' => '0.0', 'applications' => 0, 'travel_posts' => 0
    ];

    $one = function ($conn, $sql) {
        $res = mysqli_query($conn, $sql);
        $row = mysqli_fetch_row($res);
        return $row[0] ?? 0;
    };

    $stats['mock_tests']    = (int)$one($conn, "SELECT COUNT(*) FROM mock_tests WHERE student_id = $studentId");
    $stats['best_score']    = (string)($one($conn, "SELECT MAX(overall_score) FROM mock_tests WHERE student_id = $studentId") ?? '0.0');
    $stats['applications']  = (int)$one($conn, "SELECT COUNT(*) FROM student_applications WHERE student_id = $studentId");
    $stats['travel_posts']  = (int)$one($conn, "SELECT COUNT(*) FROM travel_companions WHERE status = 'open'");

    return $stats;
}
