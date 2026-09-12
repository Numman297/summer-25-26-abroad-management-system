<?php
// ================================================================
// MODEL: revenue (transactions ledger, manual entries, financial auditing)
// ================================================================

function get_all_transactions($conn, $typeFilter = '') {
    if ($typeFilter === '') {
        $sql = "SELECT r.*, u.name as user_name, u.email as user_email, u.role as user_role,
                       sp.title as package_title, ap.company_name as agency_name,
                       (r.amount - r.platform_commission) as agency_share
                FROM revenue_transactions r
                JOIN users u ON r.user_id = u.id
                LEFT JOIN service_packages sp ON r.related_id = sp.id
                LEFT JOIN agency_profiles ap ON sp.agency_id = ap.id
                ORDER BY r.id DESC";
        $res = mysqli_query($conn, $sql);
    } else {
        $sql = "SELECT r.*, u.name as user_name, u.email as user_email, u.role as user_role,
                       sp.title as package_title, ap.company_name as agency_name,
                       (r.amount - r.platform_commission) as agency_share
                FROM revenue_transactions r
                JOIN users u ON r.user_id = u.id
                LEFT JOIN service_packages sp ON r.related_id = sp.id
                LEFT JOIN agency_profiles ap ON sp.agency_id = ap.id
                WHERE r.transaction_type = ?
                ORDER BY r.id DESC";
        $stmt = mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($stmt, 's', $typeFilter);
        mysqli_stmt_execute($stmt);
        $res = mysqli_stmt_get_result($stmt);
    }
    return mysqli_fetch_all($res, MYSQLI_ASSOC);
}

function record_transaction($conn, $userId, $type, $amount, $commission, $method, $status = 'completed') {
    $stmt = mysqli_prepare($conn, "INSERT INTO revenue_transactions (transaction_type, user_id, amount, platform_commission, payment_method, status) VALUES (?, ?, ?, ?, ?, ?)");
    mysqli_stmt_bind_param($stmt, 'siddss', $type, $userId, $amount, $commission, $method, $status);
    $ok = mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);
    return $ok;
}

function get_revenue_summary($conn) {
    $sql = "SELECT 
        SUM(CASE WHEN status = 'completed' THEN amount ELSE 0 END) as gross_volume,
        SUM(CASE WHEN status = 'completed' THEN platform_commission ELSE 0 END) as net_commission,
        COUNT(CASE WHEN status = 'completed' THEN id END) as completed_count,
        COUNT(CASE WHEN status = 'pending' THEN id END) as pending_count
        FROM revenue_transactions";
    $res = mysqli_query($conn, $sql);
    return mysqli_fetch_assoc($res);
}
