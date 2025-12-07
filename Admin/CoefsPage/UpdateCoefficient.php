<?php
session_start();
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../../index.php");
    exit();
}

include '../../db_connect.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $coefficientId = $_POST['coefficient_id'];
    $newValue = trim($_POST['value']); // Удаляем пробелы

    // Проверка, что значение введено
    if ($newValue === '') {
        echo "<script>
        alert('Пожалуйста, введите значение коэффициента.');
        window.location.href = 'AdminCoefsPage.php';
        </script>";
        exit();
    }

    // Преобразуем значение в float
    $newValue = (float)$newValue;

    // Проверка, что значение находится в пределах 0-1
    if ($newValue < 0 || $newValue > 1) {
        echo "<script>
        alert('Значение коэффициента должно быть в пределах от 0 до 1.');
        window.location.href = 'AdminCoefsPage.php';
        </script>";
        exit();
    }

    // Получаем текущую сумму всех коэффициентов, кроме обновляемого
    $stmt = $conn->prepare("SELECT SUM(value) AS total FROM coefficients WHERE id != ?");
    $stmt->bind_param("i", $coefficientId);
    $stmt->execute();
    $result = $stmt->get_result()->fetch_assoc();
    $currentTotal = (float)$result['total'];
    $stmt->close();

    // Проверка, чтобы новая сумма коэффициентов не превышала 1
    if ($currentTotal + $newValue > 1) {
        echo "<script>
        alert('Сумма коэффициентов не должна превышать 1. Изменение не сохранено.');
        window.location.href = 'AdminCoefsPage.php';
        </script>";
        exit();
    }

    // Обновляем значение коэффициента в базе данных
    $stmt = $conn->prepare("UPDATE coefficients SET value = ? WHERE id = ?");
    $stmt->bind_param("di", $newValue, $coefficientId);
    
    if ($stmt->execute()) {
        echo "<script>
        alert('Коэффициент успешно обновлен.');
        window.location.href = 'AdminCoefsPage.php';
        </script>";
    } else {
        echo "<script>
        alert('Ошибка при обновлении коэффициента.');
        window.location.href = 'AdminCoefsPage.php';
        </script>";
    }

    $stmt->close();
}

$conn->close();
?>
