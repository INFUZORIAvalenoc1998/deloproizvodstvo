<?php
include '../../db_connect.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $taskId = $_POST['id'];

    $stmt = $conn->prepare("DELETE FROM task WHERE id = ?");
    $stmt->bind_param("i", $taskId);

    if ($stmt->execute()) {
        echo 'success';
    } else {
        echo 'error';
    }

    $stmt->close();
    $conn->close();
}
?>