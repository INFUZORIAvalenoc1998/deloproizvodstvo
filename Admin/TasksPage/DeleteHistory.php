<?php
include '../../db_connect.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id'])) {
    $historyId = $_POST['id'];
    
    $stmt = $conn->prepare("DELETE FROM task_history WHERE id = ?");
    $stmt->bind_param("i", $historyId);
    
    if ($stmt->execute()) {
        echo "success";
    } else {
        echo "error: " . $conn->error;
    }
    
    $stmt->close();
} else {
    echo "error: Invalid request";
}

$conn->close();
?>
