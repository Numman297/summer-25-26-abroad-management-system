<?php
// ================================================================
// MODEL: university (programs, requirements, scholarships, admissions)
// ================================================================

function get_university_profile($conn, $userId) {
    $stmt = mysqli_prepare($conn, "SELECT p.*, u.name, u.email, u.contact FROM university_profiles p JOIN users u ON p.user_id = u.id WHERE p.user_id = ?");
    mysqli_stmt_bind_param($stmt, 'i', $userId);
    mysqli_stmt_execute($stmt);
    $row = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
    mysqli_stmt_close($stmt);
    return $row;
}

function update_university_profile($conn, $userId, $uniName, $country, $city, $ranking, $website, $bio) {
    $stmt = mysqli_prepare($conn, "UPDATE university_profiles SET uni_name = ?, country = ?, city = ?, ranking = ?, website = ?, bio = ? WHERE user_id = ?");
    mysqli_stmt_bind_param($stmt, 'ssssssi', $uniName, $country, $city, $ranking, $website, $bio, $userId);
    $ok = mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);
    return $ok;
}

// Programs CRUD
function get_university_programs($conn, $uniId) {
    $stmt = mysqli_prepare($conn, "SELECT p.*, COUNT(a.id) as total_applicants FROM programs p LEFT JOIN student_applications a ON p.id = a.program_id WHERE p.university_id = ? GROUP BY p.id ORDER BY p.id DESC");
    mysqli_stmt_bind_param($stmt, 'i', $uniId);
    mysqli_stmt_execute($stmt);
    $rows = mysqli_fetch_all(mysqli_stmt_get_result($stmt), MYSQLI_ASSOC);
    mysqli_stmt_close($stmt);
    return $rows;
}

function get_program_by_id($conn, $id) {
    $stmt = mysqli_prepare($conn, "SELECT p.*, u.uni_name, u.country, u.city FROM programs p JOIN university_profiles u ON p.university_id = u.id WHERE p.id = ?");
    mysqli_stmt_bind_param($stmt, 'i', $id);
    mysqli_stmt_execute($stmt);
    $row = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
    mysqli_stmt_close($stmt);
    return $row;
}

function create_program($conn, $uniId, $title, $degree, $dept, $duration, $fee, $intakes, $desc) {
    $stmt = mysqli_prepare($conn, "INSERT INTO programs (university_id, title, degree_level, department, duration_years, tuition_fee, intake_season, description) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
    mysqli_stmt_bind_param($stmt, 'isssdsss', $uniId, $title, $degree, $dept, $duration, $fee, $intakes, $desc);
    $ok = mysqli_stmt_execute($stmt);
    $newId = mysqli_insert_id($conn);
    mysqli_stmt_close($stmt);

    if ($ok && $newId > 0) {
        mysqli_query($conn, "INSERT INTO program_requirements (program_id, min_cgpa, min_ielts, min_toefl, min_gre, visa_guidelines, documents_required) VALUES ($newId, 3.00, 6.5, 80, 300, 'Student visa required. Proof of financial solvency.', 'Transcripts, SOP, 2 LORs, English Certificate')");
    }
    return $ok ? $newId : false;
}

function update_program($conn, $id, $uniId, $title, $degree, $dept, $duration, $fee, $intakes, $desc) {
    $stmt = mysqli_prepare($conn, "UPDATE programs SET title = ?, degree_level = ?, department = ?, duration_years = ?, tuition_fee = ?, intake_season = ?, description = ? WHERE id = ? AND university_id = ?");
    mysqli_stmt_bind_param($stmt, 'sssdsssii', $title, $degree, $dept, $duration, $fee, $intakes, $desc, $id, $uniId);
    $ok = mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);
    return $ok;
}

function delete_program($conn, $id, $uniId) {
    $stmt = mysqli_prepare($conn, "DELETE FROM programs WHERE id = ? AND university_id = ?");
    mysqli_stmt_bind_param($stmt, 'ii', $id, $uniId);
    $ok = mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);
    return $ok;
}

// Program requirements
function get_program_requirements($conn, $programId) {
    $stmt = mysqli_prepare($conn, "SELECT * FROM program_requirements WHERE program_id = ?");
    mysqli_stmt_bind_param($stmt, 'i', $programId);
    mysqli_stmt_execute($stmt);
    $row = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
    mysqli_stmt_close($stmt);
    return $row;
}

function save_program_requirements($conn, $programId, $cgpa, $ielts, $toefl, $gre, $visaNotes, $docs) {
    $chk = mysqli_query($conn, "SELECT id FROM program_requirements WHERE program_id = $programId");
    if (mysqli_num_rows($chk) > 0) {
        $stmt = mysqli_prepare($conn, "UPDATE program_requirements SET min_cgpa = ?, min_ielts = ?, min_toefl = ?, min_gre = ?, visa_guidelines = ?, documents_required = ? WHERE program_id = ?");
        mysqli_stmt_bind_param($stmt, 'ddiissi', $cgpa, $ielts, $toefl, $gre, $visaNotes, $docs, $programId);
    } else {
        $stmt = mysqli_prepare($conn, "INSERT INTO program_requirements (program_id, min_cgpa, min_ielts, min_toefl, min_gre, visa_guidelines, documents_required) VALUES (?, ?, ?, ?, ?, ?, ?)");
        mysqli_stmt_bind_param($stmt, 'iddssii', $programId, $cgpa, $ielts, $toefl, $gre, $visaNotes, $docs);
    }
    $ok = mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);
    return $ok;
}

// Scholarships CRUD
function get_university_scholarships($conn, $uniId) {
    $stmt = mysqli_prepare($conn, "SELECT s.*, COUNT(sa.id) as applicant_count, COUNT(CASE WHEN sa.status = 'awarded' THEN 1 END) as awarded_count FROM scholarships s LEFT JOIN scholarship_applications sa ON s.id = sa.scholarship_id WHERE s.university_id = ? GROUP BY s.id ORDER BY s.id DESC");
    mysqli_stmt_bind_param($stmt, 'i', $uniId);
    mysqli_stmt_execute($stmt);
    $rows = mysqli_fetch_all(mysqli_stmt_get_result($stmt), MYSQLI_ASSOC);
    mysqli_stmt_close($stmt);
    return $rows;
}

function get_scholarship_by_id($conn, $id) {
    $stmt = mysqli_prepare($conn, "SELECT s.*, u.uni_name FROM scholarships s JOIN university_profiles u ON s.university_id = u.id WHERE s.id = ?");
    mysqli_stmt_bind_param($stmt, 'i', $id);
    mysqli_stmt_execute($stmt);
    $row = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
    mysqli_stmt_close($stmt);
    return $row;
}

function create_scholarship($conn, $uniId, $title, $grantAmount, $coverage, $criteria, $deadline, $maxRecipients, $isActive = 1) {
    $stmt = mysqli_prepare($conn, "INSERT INTO scholarships (university_id, title, grant_amount, coverage_type, eligibility_criteria, deadline, max_recipients, is_active) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
    mysqli_stmt_bind_param($stmt, 'isdsssii', $uniId, $title, $grantAmount, $coverage, $criteria, $deadline, $maxRecipients, $isActive);
    $ok = mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);
    return $ok;
}

function update_scholarship($conn, $id, $uniId, $title, $grantAmount, $coverage, $criteria, $deadline, $maxRecipients, $isActive) {
    $stmt = mysqli_prepare($conn, "UPDATE scholarships SET title = ?, grant_amount = ?, coverage_type = ?, eligibility_criteria = ?, deadline = ?, max_recipients = ?, is_active = ? WHERE id = ? AND university_id = ?");
    mysqli_stmt_bind_param($stmt, 'sdsssiiii', $title, $grantAmount, $coverage, $criteria, $deadline, $maxRecipients, $isActive, $id, $uniId);
    $ok = mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);
    return $ok;
}

function delete_scholarship($conn, $id, $uniId) {
    $stmt = mysqli_prepare($conn, "DELETE FROM scholarships WHERE id = ? AND university_id = ?");
    mysqli_stmt_bind_param($stmt, 'ii', $id, $uniId);
    $ok = mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);
    return $ok;
}

// Scholarship applicants
function get_scholarship_applications($conn, $uniId) {
    $stmt = mysqli_prepare($conn, "SELECT sa.*, s.title as scholarship_title, s.grant_amount, u.name as student_name, u.email as student_email FROM scholarship_applications sa JOIN scholarships s ON sa.scholarship_id = s.id JOIN users u ON sa.student_id = u.id WHERE s.university_id = ? ORDER BY sa.id DESC");
    mysqli_stmt_bind_param($stmt, 'i', $uniId);
    mysqli_stmt_execute($stmt);
    $rows = mysqli_fetch_all(mysqli_stmt_get_result($stmt), MYSQLI_ASSOC);
    mysqli_stmt_close($stmt);
    return $rows;
}

function decide_scholarship($conn, $appId, $status) {
    $stmt = mysqli_prepare($conn, "UPDATE scholarship_applications SET status = ? WHERE id = ?");
    mysqli_stmt_bind_param($stmt, 'si', $status, $appId);
    $ok = mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);
    return $ok;
}

// University admissions review & offer letter issuance
function get_university_applicants($conn, $uniId, $intakeFilter = '') {
    if ($intakeFilter === '') {
        $sql = "SELECT a.*, u.name as student_name, u.email as student_email, u.contact as student_contact,
                       p.title as program_title, p.degree_level, p.tuition_fee,
                       (SELECT GROUP_CONCAT(CONCAT(degree_title, ': ', result_cgpa) SEPARATOR ' | ') FROM student_education WHERE student_id = u.id) as academic_summary
                FROM student_applications a
                JOIN programs p ON a.program_id = p.id
                JOIN users u ON a.student_id = u.id
                WHERE p.university_id = ?
                ORDER BY a.id DESC";
        $stmt = mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($stmt, 'i', $uniId);
    } else {
        $sql = "SELECT a.*, u.name as student_name, u.email as student_email, u.contact as student_contact,
                       p.title as program_title, p.degree_level, p.tuition_fee,
                       (SELECT GROUP_CONCAT(CONCAT(degree_title, ': ', result_cgpa) SEPARATOR ' | ') FROM student_education WHERE student_id = u.id) as academic_summary
                FROM student_applications a
                JOIN programs p ON a.program_id = p.id
                JOIN users u ON a.student_id = u.id
                WHERE p.university_id = ? AND a.intake LIKE ?
                ORDER BY a.id DESC";
        $like = '%' . $intakeFilter . '%';
        $stmt = mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($stmt, 'is', $uniId, $like);
    }
    mysqli_stmt_execute($stmt);
    $rows = mysqli_fetch_all(mysqli_stmt_get_result($stmt), MYSQLI_ASSOC);
    mysqli_stmt_close($stmt);
    return $rows;
}

function update_admission_decision($conn, $appId, $status, $remarks) {
    $stmt = mysqli_prepare($conn, "UPDATE student_applications SET status = ?, remarks = ?, offer_letter_file = NULL, updated_at = NOW() WHERE id = ?");
    mysqli_stmt_bind_param($stmt, 'ssi', $status, $remarks, $appId);
    $ok = mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);
    return $ok;
}

// Admin verifications list for universities
function get_all_universities($conn) {
    $sql = "SELECT p.*, u.name, u.email, u.contact, u.status as user_status
            FROM university_profiles p
            JOIN users u ON p.user_id = u.id
            ORDER BY p.verified_status = 'pending' DESC, p.id DESC";
    $res = mysqli_query($conn, $sql);
    return mysqli_fetch_all($res, MYSQLI_ASSOC);
}

function verify_university($conn, $id, $status, $notes = '') {
    $stmt = mysqli_prepare($conn, "UPDATE university_profiles SET verified_status = ?, verification_notes = ?, verified_at = NOW() WHERE id = ?");
    mysqli_stmt_bind_param($stmt, 'ssi', $status, $notes, $id);
    $ok = mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);
    return $ok;
}

// University stats
function get_university_stats($conn, $uniId) {
    $stats = [
        'programs' => 0, 'applicants' => 0, 'pending' => 0, 'scholarships' => 0
    ];

    $one = function ($conn, $sql) {
        $res = mysqli_query($conn, $sql);
        $row = mysqli_fetch_row($res);
        return (int)($row[0] ?? 0);
    };

    $stats['programs']     = $one($conn, "SELECT COUNT(*) FROM programs WHERE university_id = $uniId");
    $stats['applicants']   = $one($conn, "SELECT COUNT(*) FROM student_applications a JOIN programs p ON a.program_id = p.id WHERE p.university_id = $uniId");
    $stats['pending']      = $one($conn, "SELECT COUNT(*) FROM student_applications a JOIN programs p ON a.program_id = p.id WHERE p.university_id = $uniId AND a.status IN ('pending', 'under_review')");
    $stats['scholarships'] = $one($conn, "SELECT COUNT(*) FROM scholarships WHERE university_id = $uniId");

    return $stats;
}
