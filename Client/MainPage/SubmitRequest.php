<?php
session_start();
include '../../db_connect.php';

function submitRequest($conn, $task_name, $task_description, $task_area, $task_photo)
{
    if (!isset($_SESSION['login'])) {
        throw new Exception("Вы должны войти в систему, чтобы отправить дело.");
    }

    $login = $_SESSION['login'];

    if (empty($task_name) || empty($task_description) || empty($task_area)) {
        throw new Exception("Пожалуйста, заполните все поля формы.");
    }

    $stmt = $conn->prepare("SELECT id FROM user WHERE login = ?");
    if ($stmt === false) {
        throw new Exception('Ошибка подготовки запроса: ' . $conn->error);
    }

    $stmt->bind_param("s", $login);
    $stmt->execute();
//    $stmt->bind_result($client_id);
    $stmt->fetch();
    $stmt->close();

//    if (!$client_id) {
//        throw new Exception("Ошибка: клиент не найден.");
//    }

    $photo_url = null;
    if (isset($task_photo) && $task_photo['error'] === UPLOAD_ERR_OK) {
        $uploadDir = $_SERVER['DOCUMENT_ROOT'] . '/deloproizvodstvo/assets/uploads/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }
        $fileTmpPath = $task_photo['tmp_name'];
        $fileName = $task_photo['name'];
        $fileSize = $task_photo['size'];

        $maxFileSize = 2 * 1024 * 1024;
        if ($fileSize > $maxFileSize) {
            throw new Exception("Файл слишком большой. Максимальный размер: 2MB.");
        }

        $fileExtension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
        $allowedFileTypes = ['jpg', 'jpeg', 'png', 'gif'];
        if (!in_array($fileExtension, $allowedFileTypes)) {
            throw new Exception("Неподдерживаемый формат файла. Разрешены только JPG, PNG, GIF.");
        }

        $newFileName = uniqid('task_', true) . '.' . $fileExtension;
        $destPath = $uploadDir . $newFileName;

        if (!move_uploaded_file($fileTmpPath, $destPath)) {
            throw new Exception("Не удалось сохранить файл.");
        }

        $photo_url = '/deloproizvodstvo/assets/uploads/' . $newFileName;
    }

    $stmt = $conn->prepare("INSERT INTO request (name, description, area_id, client_id, photoURL) VALUES (?, ?, ?, ?, ?)");
    if ($stmt === false) {
        throw new Exception('Ошибка подготовки запроса: ' . $conn->error);
    }

    $stmt->bind_param("ssiss", $task_name, $task_description, $task_area, $client_id, $photo_url);

    if (!$stmt->execute()) {
        throw new Exception("Ошибка выполнения запроса: " . $stmt->error);
    }

    $stmt->close();
    $conn->close();

    return "Задание успешно отправлено на рассмотрение.";
}


if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    try {
        $task_name = trim($_POST['task_name'] ?? '');
        $task_description = trim($_POST['task_description'] ?? '');
        $task_area = trim($_POST['task_area'] ?? '');
        $task_photo = $_FILES['task-photo'] ?? null;

        $message = submitRequest($conn, $task_name, $task_description, $task_area, $task_photo);

        echo "<script>
                alert('{$message}');
                window.location.href = 'ClientMainPage.php';
              </script>";
    } catch (Exception $e) {
        echo "<script>
                alert('Ошибка: {$e->getMessage()}');
                window.history.back();
              </script>";
    }
}

