<?php
include '../../db_connect.php';

header('Content-Type: application/json');

//echo "<script>alert('Файл CancelRequest.php успешно подключен');</script>";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    
    $requestId = $data['id'] ?? null;

    if ($requestId) {
        // Подготавливаем запрос для удаления по id и client_id
        $sql = "DELETE FROM request WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $requestId); // Используем id вместо имени
        
        if ($stmt->execute()) {
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Ошибка при удалении заявки.']);
        }
        
        $stmt->close();
    } else {
        echo json_encode(['success' => false, 'message' => 'Некорректные данные.']);
    }
}

$conn->close();
?>
