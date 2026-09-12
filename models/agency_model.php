<?php
// ================================================================
// MODEL: agency (profiles, service packages, pipeline tracking, reports)
// ================================================================

function get_agency_profile($conn, $userId) {
    $stmt = mysqli_prepare($conn, "SELECT a.*, u.name, u.email, u.contact FROM agency_profiles a JOIN users u ON a.user_id = u.id WHERE a.user_id = ?");
    mysqli_stmt_bind_param($stmt, 'i', $userId);
    mysqli_stmt_execute($stmt);
    $row = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
    mysqli_stmt_close($stmt);
    return $row;
}

function update_agency_profile($conn, $userId, $companyName, $licenseNo, $address, $website, $bio) {
    $stmt = mysqli_prepare($conn, "UPDATE agency_profiles SET company_name = ?, license_no = ?, address = ?, website = ?, bio = ? WHERE user_id = ?");
    mysqli_stmt_bind_param($stmt, 'sssssi', $companyName, $licenseNo, $address, $website, $bio, $userId);
    $ok = mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);
    return $ok;
}

// Packages CRUD
function get_agency_packages($conn, $agencyId) {
    $stmt = mysqli_prepare($conn, "SELECT * FROM service_packages WHERE agency_id = ? ORDER BY id DESC");
    mysqli_stmt_bind_param($stmt, 'i', $agencyId);
    mysqli_stmt_execute($stmt);
    $rows = mysqli_fetch_all(mysqli_stmt_get_result($stmt), MYSQLI_ASSOC);
    mysqli_stmt_close($stmt);
    return $rows;
}

function get_package_by_id($conn, $id) {
    $stmt = mysqli_prepare($conn, "SELECT p.*, a.company_name FROM service_packages p JOIN agency_profiles a ON p.agency_id = a.id WHERE p.id = ?");
    mysqli_stmt_bind_param($stmt, 'i', $id);
    mysqli_stmt_execute($stmt);
    $row = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
    mysqli_stmt_close($stmt);
    return $row;
}

function create_package($conn, $agencyId, $title, $price, $durationWeeks, $desc, $features, $isActive = 1) {
    $stmt = mysqli_prepare($conn, "INSERT INTO service_packages (agency_id, title, price, duration_weeks, description, features_list, is_active) VALUES (?, ?, ?, ?, ?, ?, ?)");
    mysqli_stmt_bind_param($stmt, 'isdisss', $agencyId, $title, $price, $durationWeeks, $desc, $features, $isActive);
    $ok = mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);
    return $ok;
}

function update_package($conn, $id, $agencyId, $title, $price, $durationWeeks, $desc, $features, $isActive) {
    $stmt = mysqli_prepare($conn, "UPDATE service_packages SET title = ?, price = ?, duration_weeks = ?, description = ?, features_list = ?, is_active = ? WHERE id = ? AND agency_id = ?");
    mysqli_stmt_bind_param($stmt, 'sdisssii', $title, $price, $durationWeeks, $desc, $features, $isActive, $id, $agencyId);
    $ok = mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);
    return $ok;
}

function delete_package($conn, $id, $agencyId) {
    $stmt = mysqli_prepare($conn, "DELETE FROM service_packages WHERE id = ? AND agency_id = ?");
    mysqli_stmt_bind_param($stmt, 'ii', $id, $agencyId);
    $ok = mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);
    return $ok;
}

// Applications pipeline tracker
function get_agency_applications($conn, $agencyId, $statusFilter = '') {
    if ($statusFilter === '') {
        $sql = "SELECT a.*, u.name as student_name, u.email as student_email, u.contact as student_contact,
                       p.title as package_name, pr.title as uni_program_name
                FROM student_applications a
                JOIN users u ON a.student_id = u.id
                LEFT JOIN service_packages p ON a.package_id = p.id
                LEFT JOIN programs pr ON a.program_id = pr.id
                WHERE a.agency_id = ?
                ORDER BY a.id DESC";
        $stmt = mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($stmt, 'i', $agencyId);
    } else {
        $sql = "SELECT a.*, u.name as student_name, u.email as student_email, u.contact as student_contact,
                       p.title as package_name, pr.title as uni_program_name
                FROM student_applications a
                JOIN users u ON a.student_id = u.id
                LEFT JOIN service_packages p ON a.package_id = p.id
                LEFT JOIN programs pr ON a.program_id = pr.id
                WHERE a.agency_id = ? AND a.status = ?
                ORDER BY a.id DESC";
        $stmt = mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($stmt, 'is', $agencyId, $statusFilter);
    }
    mysqli_stmt_execute($stmt);
    $rows = mysqli_fetch_all(mysqli_stmt_get_result($stmt), MYSQLI_ASSOC);
    mysqli_stmt_close($stmt);
    return $rows;
}

function update_application_status($conn, $appId, $status, $remarks = '') {
    $stmt = mysqli_prepare($conn, "UPDATE student_applications SET status = ?, remarks = ?, updated_at = NOW() WHERE id = ?");
    mysqli_stmt_bind_param($stmt, 'ssi', $status, $remarks, $appId);
    $ok = mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);
    return $ok;
}

// Admin verifications list for agencies
function get_all_agencies($conn) {
    $sql = "SELECT a.*, u.name, u.email, u.contact, u.status as user_status
            FROM agency_profiles a
            JOIN users u ON a.user_id = u.id
            ORDER BY a.verified_status = 'pending' DESC, a.id DESC";
    $res = mysqli_query($conn, $sql);
    return mysqli_fetch_all($res, MYSQLI_ASSOC);
}

function verify_agency($conn, $id, $status, $notes = '') {
    $stmt = mysqli_prepare($conn, "UPDATE agency_profiles SET verified_status = ?, verification_notes = ?, verified_at = NOW() WHERE id = ?");
    mysqli_stmt_bind_param($stmt, 'ssi', $status, $notes, $id);
    $ok = mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);
    return $ok;
}

// Agency business report analytics
function get_agency_stats($conn, $agencyId, $userId) {
    $stats = [
        'packages' => 0, 'clients' => 0, 'visa_approved' => 0,
        'in_progress' => 0, 'rejected' => 0, 'success_rate' => 0,
        'gross_bookings' => 0.0, 'platform_deductions' => 0.0, 'net_earnings' => 0.0, 'earnings' => 0.0
    ];

    $one = function ($conn, $sql) {
        $res = mysqli_query($conn, $sql);
        $row = mysqli_fetch_row($res);
        return (int)($row[0] ?? 0);
    };

    $stats['packages']      = $one($conn, "SELECT COUNT(*) FROM service_packages WHERE agency_id = $agencyId");
    $stats['clients']       = $one($conn, "SELECT COUNT(*) FROM student_applications WHERE agency_id = $agencyId");
    $stats['visa_approved'] = $one($conn, "SELECT COUNT(*) FROM student_applications WHERE agency_id = $agencyId AND status = 'visa_approved'");
    $stats['in_progress']   = $one($conn, "SELECT COUNT(*) FROM student_applications WHERE agency_id = $agencyId AND status IN ('pending', 'under_review', 'offer_issued', 'visa_in_progress')");
    $stats['rejected']      = $one($conn, "SELECT COUNT(*) FROM student_applications WHERE agency_id = $agencyId AND status = 'rejected'");
    
    if ($stats['clients'] > 0) {
        $stats['success_rate'] = round(($stats['visa_approved'] / $stats['clients']) * 100, 1);
    }

    // Direct automated revenue split: 90% to agency, 10% to admin
    $revSql = "SELECT 
                SUM(r.amount) as gross,
                SUM(r.platform_commission) as commission_cut,
                SUM(r.amount - r.platform_commission) as net
               FROM revenue_transactions r
               JOIN service_packages sp ON r.related_id = sp.id
               WHERE sp.agency_id = $agencyId AND r.status = 'completed'";
    $revRow = mysqli_fetch_assoc(mysqli_query($conn, $revSql));

    $stats['gross_bookings']      = (float)($revRow['gross'] ?? 0.0);
    $stats['platform_deductions'] = (float)($revRow['commission_cut'] ?? 0.0);
    $stats['net_earnings']        = (float)($revRow['net'] ?? 0.0);
    $stats['earnings']            = $stats['net_earnings']; // for compatibility

    return $stats;
}

function get_agency_transactions($conn, $agencyId) {
    $sql = "SELECT r.*, u.name as student_name, u.email as student_email, u.contact as student_contact,
                   sp.title as package_title, (r.amount - r.platform_commission) as net_earned
            FROM revenue_transactions r
            JOIN users u ON r.user_id = u.id
            JOIN service_packages sp ON r.related_id = sp.id
            WHERE sp.agency_id = ?
            ORDER BY r.id DESC";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, 'i', $agencyId);
    mysqli_stmt_execute($stmt);
    $rows = mysqli_fetch_all(mysqli_stmt_get_result($stmt), MYSQLI_ASSOC);
    mysqli_stmt_close($stmt);
    return $rows;
}
