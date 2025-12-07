<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();
$employeeId = $_SESSION['employee_id'];

// Проверяем, был ли загружен файл
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['empAvatar'])) {
    // Проверка на ошибки загрузки
    if ($_FILES['empAvatar']['error'] === UPLOAD_ERR_OK) {
        // Проверка типа файла (допустимые типы: jpg, jpeg, png)
        $fileType = $_FILES['empAvatar']['type'];
        $allowedTypes = ['image/jpeg', 'image/png'];
        if (!in_array($fileType, $allowedTypes)) {
            echo "<script>alert('Неподдерживаемый формат файла. Пожалуйста, загрузите изображение в формате JPEG или PNG.');
                window.location.href = 'ProfilePage.php';
            </script>";
            
            exit();
        }

        $fileTmpPath = $_FILES['empAvatar']['tmp_name'];

        // Проверка на битый файл с помощью getimagesize()
        if (!getimagesize($fileTmpPath)) {
            echo "<script>
                    alert('Загруженный файл поврежден.');
                    window.location.href = 'ProfilePage.php';
                  </script>";
            exit();
        }

        // Проверка размера файла (до 2 МБ)
        $fileSize = $_FILES['empAvatar']['size'];
        if ($fileSize > 2 * 1024 * 1024) {
            echo "<script>alert('Размер файла превышает 2 МБ.');
                window.location.href = 'ProfilePage.php';
                </script>";
            exit();
        }

        // Подключение к базе данных
        include '../../db_connect.php';

        // Получаем данные файла
        $fileData = file_get_contents($_FILES['empAvatar']['tmp_name']);
        if ($fileData === false) {
            echo "<script>alert('Ошибка при чтении файла.');
             window.location.href = 'ProfilePage.php';
            </script>";
            exit();
        }

        // Подготовка SQL-запроса для обновления аватара
        $stmt = $conn->prepare("INSERT INTO emp_avatars (emp_id, avatar) VALUES (?, ?) ON DUPLICATE KEY UPDATE avatar = ?");
        if (!$stmt) {
            echo "<script>alert('Ошибка подготовки запроса: " . $conn->error . "');
                window.location.href = 'ProfilePage.php';
            </script>";
            exit();
        }

        // Используем 'b' для LONGBLOB
        $bindResult = $stmt->bind_param("iss", $employeeId, $fileData, $fileData);
        if ($bindResult === false) {
            echo "<script>alert('Ошибка привязки параметров: " . $stmt->error . "');
             window.location.href = 'ProfilePage.php';
            </script>";
            exit();
        }

        // Выполняем запрос
        if ($stmt->execute()) {
            echo "<script>alert('Аватар успешно сохранен!'); window.location.href = 'ProfilePage.php';</script>";
        } else {
            echo "<script>alert('Ошибка при сохранении аватара: " . $stmt->error . "');window.location.href = 'ProfilePage.php';</script>";
        }

        // Закрываем соединение
        $stmt->close();
        $conn->close();
    } else {
        echo "<script>alert('Ошибка при загрузке файла.');
         window.location.href = 'ProfilePage.php';
        </script>";
    }
} else {
    echo "<script>alert('Пожалуйста, выберите файл для загрузки.');
             window.location.href = 'ProfilePage.php';
            </script>";
}
?>
