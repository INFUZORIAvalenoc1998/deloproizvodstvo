<?php
include '../../db_connect.php';

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();
if (!isset($_SESSION['login'])) {
    die('Вы должны войти в систему, чтобы обновить сотрудника.');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $taskId = $_POST['task_id'];
    $newEmployeeId = $_POST['employee_id'];

    // Получаем текущего сотрудника задачи
    $queryCurrentEmployee = "SELECT employee_id FROM task WHERE id = ?";
    $stmtCurrent = $conn->prepare($queryCurrentEmployee);
    $stmtCurrent->bind_param("i", $taskId);
    $stmtCurrent->execute();
    $stmtCurrent->bind_result($currentEmployeeId);
    $stmtCurrent->fetch();
    $stmtCurrent->close();

    // Проверяем, совпадает ли новый сотрудник с текущим
    if ($currentEmployeeId == $newEmployeeId) {
        echo "<script>alert('Этот сотрудник уже назначен на задачу.');</script>";
        echo "<script>window.location.href = '" . $_SERVER['HTTP_REFERER'] . "';</script>";
        exit();
    }

    // Обновляем сотрудника в таблице task
    $queryUpdate = "UPDATE task SET employee_id = ? WHERE id = ?";
    $stmtUpdate = $conn->prepare($queryUpdate);
    if (!$stmtUpdate) {
        die('Ошибка подготовки запроса UPDATE: ' . $conn->error);
    }
    $stmtUpdate->bind_param("ii", $newEmployeeId, $taskId);
    if (!$stmtUpdate->execute()) {
        die('Ошибка выполнения запроса UPDATE: ' . $stmtUpdate->error);
    }
    $stmtUpdate->close();

    // Добавляем запись в таблицу task_history
    $queryHistory = "INSERT INTO task_history (task_id, employee_id, action, date) VALUES (?, ?, 'employee', CURDATE())";
    $stmtHistory = $conn->prepare($queryHistory);
    if (!$stmtHistory) {
        die('Ошибка подготовки запроса INSERT: ' . $conn->error);
    }
    $stmtHistory->bind_param("ii", $taskId, $newEmployeeId);
    if (!$stmtHistory->execute()) {
        die('Ошибка выполнения запроса INSERT: ' . $stmtHistory->error);
    }
    $stmtHistory->close();

    echo "<script>alert('Сотрудник для задачи с ID $taskId успешно обновлен.');</script>";
    echo "<script>window.location.href = '" . $_SERVER['HTTP_REFERER'] . "';</script>";
}
?>
