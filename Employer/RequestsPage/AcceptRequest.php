<?php
include '../../db_connect.php';

session_start(); // Не забудьте запустить сессию для доступа к переменной $_SESSION

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['id']) && isset($_POST['employee_select'])) {
    $requestId = intval($_POST['id']);
    $employeeLogin = $_POST['employee_select'];
    $complexityId = isset($_POST['complexity_select']) ? intval($_POST['complexity_select']) : 1; // По умолчанию 'легко'
    $employerId = $_SESSION['employer_id'];

    // Получаем ID работника по его логину
    $employeeQuery = "SELECT id FROM user WHERE login = ?";
    $stmt = $conn->prepare($employeeQuery);
    $stmt->bind_param("s", $employeeLogin);
    $stmt->execute();
    $employeeResult = $stmt->get_result();

    if ($employeeResult && $employeeRow = $employeeResult->fetch_assoc()) {
        $employeeId = $employeeRow['id'];
    } else {
        echo "Ошибка: работник не найден.";
        exit;
    }

    // Получаем данные по заявке, включая photoURL
    $requestQuery = "SELECT name, description, area_id, client_id, photoURL FROM request WHERE id = ?";
    $stmt = $conn->prepare($requestQuery);
    $stmt->bind_param("i", $requestId);
    $stmt->execute();
    $requestResult = $stmt->get_result();

    if ($requestResult && $requestRow = $requestResult->fetch_assoc()) {
        $name = $requestRow['name'];
        $description = $requestRow['description'];
        $areaId = $requestRow['area_id'];
        $clientId = $requestRow['client_id'];
        $photoURL = $requestRow['photoURL']; // Извлекаем photoURL
        $status = 'Одобрено';
        $date = date('Y-m-d');

        // Вставляем новую задачу, включая photoURL
        $insertTaskQuery = "INSERT INTO task (name, description, area_id, client_id, employee_id, employer_id, status, date, complexity_id, photoURL) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($insertTaskQuery);
        $stmt->bind_param("ssiiiissis", $name, $description, $areaId, $clientId, $employeeId, $employerId, $status, $date, $complexityId, $photoURL);

        if ($stmt->execute()) {
            // Обновление статуса одобрения в таблице request
            $updateRequestQuery = "UPDATE request SET approved = 1 WHERE id = ?";
            $stmt = $conn->prepare($updateRequestQuery);
            $stmt->bind_param("i", $requestId);
            $stmt->execute();

            echo "Заявка успешно одобрена.";
        } else {
            echo "Ошибка при добавлении задачи.";
        }
    } else {
        echo "Ошибка: заявка не найдена.";
    }
} else {
    echo "Ошибка: некорректные данные.";
}

$conn->close();
?>
