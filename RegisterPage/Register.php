<?php
include '../db_connect.php';

function Register($conn, $login, $password, $passwordAgain)
{
    try {
        if (empty($login) || empty($password) || empty($passwordAgain)) {
            throw new Exception("Все поля должны быть заполнены.");
        }

        if ($password !== $passwordAgain) {
            throw new Exception("Пароли не совпадают.");
        }

        $stmt = $conn->prepare("SELECT login FROM user WHERE login = ?");
        if ($stmt === false) {
            throw new Exception('Ошибка подготовки запроса: ' . $conn->error);
        }

        $stmt->bind_param("s", $login);
        $stmt->execute();
        $stmt->store_result();

//        if ($stmt->num_rows > 0) {
//            $stmt->close();
            throw new Exception("Логин уже существует.");
//        }
        $stmt->close();

        $stmt = $conn->prepare("INSERT INTO user (login, password, role) VALUES (?, ?, 'client')");
        if ($stmt === false) {
            throw new Exception('Ошибка подготовки запроса: ' . $conn->error);
        }

        $stmt->bind_param("ss", $login, $password);

        if (!$stmt->execute()) {
            $error = $conn->error;
            $stmt->close();
            throw new Exception("Ошибка выполнения запроса: $error");
        }

        $stmt->close();
    } catch (Exception $e) {
        throw $e;
    }
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    try {
        $login = trim($_POST['login'] ?? '');
        $password = trim($_POST['password'] ?? '');
        $passwordAgain = trim($_POST['passwordAgain'] ?? '');

        Register($conn, $login, $password, $passwordAgain);

        echo "<script>
                alert('Регистрация прошла успешно. Пожалуйста, войдите в систему.');
                window.location.href = '../index.php';
              </script>";
    } catch (Exception $e) {
        echo "<script>
                alert('Ошибка: {$e->getMessage()}');
                window.history.back();
              </script>";
    } finally {
        $conn->close();
    }
}
