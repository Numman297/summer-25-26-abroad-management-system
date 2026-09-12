<?php
// ================================================================
// MODEL: feedbacks (student reviews, ratings, admin dispute resolution)
// ================================================================

function get_all_feedbacks($conn, $statusFilter = '') {
    if ($statusFilter === '') {
        $sql = "SELECT f.*, u.name as student_name, u.email as student_email
                FROM feedbacks f
                JOIN users u ON f.student_id = u.id
                ORDER BY f.status = 'pending' DESC, f.id DESC";
        $res = mysqli_query($conn, $sql);
    } else {
        $sql = "SELECT f.*, u.name as student_name, u.email as student_email
                FROM feedbacks f
                JOIN users u ON f.student_id = u.id
                WHERE f.status = ?
                ORDER BY f.id DESC";
        $stmt = mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($stmt, 's', $statusFilter);
        mysqli_stmt_execute($stmt);
        $res = mysqli_stmt_get_result($stmt);
    }
    return mysqli_fetch_all($res, MYSQLI_ASSOC);
}

function get_student_feedbacks($conn, $studentId) {
    $stmt = mysqli_prepare($conn, "SELECT * FROM feedbacks WHERE student_id = ? ORDER BY id DESC");
    mysqli_stmt_bind_param($stmt, 'i', $studentId);
    mysqli_stmt_execute($stmt);
    $rows = mysqli_fetch_all(mysqli_stmt_get_result($stmt), MYSQLI_ASSOC);
    mysqli_stmt_close($stmt);
    return $rows;
}

function submit_feedback($conn, $studentId, $targetType, $rating, $subject, $message) {
    $stmt = mysqli_prepare($conn, "INSERT INTO feedbacks (student_id, target_type, rating, subject, message, status) VALUES (?, ?, ?, ?, ?, 'pending')");
    mysqli_stmt_bind_param($stmt, 'isiss', $studentId, $targetType, $rating, $subject, $message);
    $ok = mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);
    return $ok;
}

function respond_feedback($conn, $id, $response, $status = 'resolved') {
    $stmt = mysqli_prepare($conn, "UPDATE feedbacks SET admin_response = ?, status = ? WHERE id = ?");
    mysqli_stmt_bind_param($stmt, 'ssi', $response, $status, $id);
    $ok = mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);
    return $ok;
}

function delete_feedback($conn, $id) {
    $stmt = mysqli_prepare($conn, "DELETE FROM feedbacks WHERE id = ?");
    mysqli_stmt_bind_param($stmt, 'i', $id);
    $ok = mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);
    return $ok;
}
