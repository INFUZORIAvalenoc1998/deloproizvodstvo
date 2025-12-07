<?php
include '../../db_connect.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $taskId = $_POST['id'];

    $stmt = $conn->prepare("DELETE FROM request WHERE id = ?");
    $stmt->bind_param("i", $taskId);

    if ($stmt->execute()) {
        echo "<script>
        alert('Заявка отклонена.');
        window.location.href = 'EmployerRequestsPage.php'; 
      </script>";
    } else {
        echo "<script>
        alert('Ошибка при удалении дела.');
        window.location.href = 'EmployerRequestsPage.php'; 
      </script>";
    }

    $stmt->close();
    $conn->close();
}
?>