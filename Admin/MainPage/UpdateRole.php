<?php
include '../../db_connect.php';

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $userId = $_POST['user_id'];
    $newRole = $_POST['role'];

    $getRoleQuery = "SELECT role FROM user WHERE id = ?";
    $roleStmt = $conn->prepare($getRoleQuery);
    $roleStmt->bind_param('i', $userId);
    $roleStmt->execute();
    $roleStmt->bind_result($currentRole);
    $roleStmt->fetch();
    $roleStmt->close();

    if ($currentRole !== $newRole) {
        $updateRoleQuery = "UPDATE user SET role = ? WHERE id = ?";
        $updateStmt = $conn->prepare($updateRoleQuery);
        $updateStmt->bind_param('si', $newRole, $userId);

        if ($updateStmt->execute()) {
            echo "<script>
            alert('Роль успешно обновлена');
            window.location.href = 'AdminMainPage.php'; 
            </script>";
        } else {
            echo "<script>
            alert('Ошибка обновления роли');
            window.location.href = 'AdminMainPage.php'; 
            </script>";
        }

        $updateStmt->close();
    } else {
        echo "<script>
        alert('Роль не изменилась');
        window.location.href = 'AdminMainPage.php'; 
        </script>";
    }
}

$conn->close();
?>