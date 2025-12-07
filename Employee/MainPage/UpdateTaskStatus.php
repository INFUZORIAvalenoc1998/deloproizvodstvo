<?php
include '../../db_connect.php';

// Убедитесь, что сессия активирована
session_start();

if (!isset($_SESSION['login'])) {
    die('Вы должны войти в систему, чтобы обновить статус.');
}

// Проверяем, что форма отправлена
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Получаем данные из формы
    $taskId = $_POST['task_id'];
    $status = $_POST['status'];

    // Убедитесь, что данные корректны
    if (empty($taskId) || empty($status)) {
        die('Недостаточно данных для обновления.');
    }

    // Получаем текущий статус задачи
    $queryCurrentStatus = "SELECT status FROM task WHERE id = ?";
    $stmtCurrent = $conn->prepare($queryCurrentStatus);
    $stmtCurrent->bind_param("i", $taskId);
    $stmtCurrent->execute();
    $stmtCurrent->bind_result($currentStatus);
    $stmtCurrent->fetch();
    $stmtCurrent->close();

    if ($currentStatus === 'Завершено') {
        echo "<script>alert('Статус завершенной задачи нельзя изменить.');</script>";
        echo "<script>window.location.href = '" . $_SERVER['HTTP_REFERER'] . "';</script>";
        exit();
    }

    if ($currentStatus === $status) {
        echo "<script>alert('Этот статус задачи уже установлен');</script>";
        echo "<script>window.location.href = '" . $_SERVER['HTTP_REFERER'] . "';</script>";
        exit();
    }



    // Подготовка SQL-запроса для обновления статуса задачи
    $query = "UPDATE task SET status = ? WHERE id = ?";
    $stmt = $conn->prepare($query);
    if ($stmt === false) {
        die('Ошибка подготовки запроса: ' . $conn->error);
    }

    // Привязываем параметры и выполняем запрос
    $stmt->bind_param("si", $status, $taskId);
    $success = $stmt->execute();

    if ($success) {
        // Получаем id сотрудника, закрепленного за задачей
        $queryEmployee = "SELECT employee_id FROM task WHERE id = ?";
        $stmtEmployee = $conn->prepare($queryEmployee);
        $stmtEmployee->bind_param("i", $taskId);
        $stmtEmployee->execute();
        $stmtEmployee->bind_result($employeeId);
        $stmtEmployee->fetch();
        $stmtEmployee->close();

        // Вставка данных в таблицу task_history
        $queryHistory = "INSERT INTO task_history (task_id, employee_id, action, status, date) VALUES (?, ?, 'status', ?, CURDATE())";
        $stmtHistory = $conn->prepare($queryHistory);
        $stmtHistory->bind_param("iis", $taskId, $employeeId, $status);
        $stmtHistory->execute();
        $stmtHistory->close();

        echo "<script>alert('Статус задачи с ID $taskId изменен на \"$status\".');</script>";
    }

    // Закрываем соединение
    $stmt->close();
    $conn->close();

    // Перенаправляем на ту же страницу
    echo "<script>window.location.href = '" . $_SERVER['HTTP_REFERER'] . "';</script>";
    exit();
} else {
    echo "Некорректный метод запроса.";
}
?>
