<?php
include 'db_connect.php'; 

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $login = trim($_POST['login']);
    $password = trim($_POST['password']);
    $rememberMe = isset($_POST['remember_me']);

    $stmt = $conn->prepare("SELECT password, role FROM user WHERE login = ?");
    $stmt->bind_param("s", $login);
    $stmt->execute();
    $stmt->store_result();
    $stmt->bind_result($stored_password, $role);
    
    if ($stmt->num_rows > 0) {
        $stmt->fetch();

        if ($password === $stored_password) {
            session_start();
            $_SESSION['role'] = $role;
            $_SESSION['login'] = $login;

            // Получаем ID пользователя
            $idStmt = $conn->prepare("SELECT id FROM user WHERE login = ?");
            $idStmt->bind_param("s", $login);
            $idStmt->execute();
            $idStmt->bind_result($userId);
            $idStmt->fetch();
            $idStmt->close();

            // Сохраняем ID в сессии в зависимости от роли
            if ($role === 'client') {
                $_SESSION['client_id'] = $userId;
            } elseif ($role === 'employee') {
                $_SESSION['employee_id'] = $userId;
            } elseif ($role === 'employer') {
                $_SESSION['employer_id'] = $userId;
            }

            

            // Устанавливаем куки для логина, если "Запомнить меня" включено
            if ($rememberMe) {
                setcookie("remembered_login", $login, time() + (86400 * 30), "/"); // Кука на 30 дней
            } else {
                setcookie("remembered_login", "", time() - 3600, "/"); // Удаляем куку, если опция не выбрана
            }

            // Перенаправление на соответствующую страницу
            switch ($role) {
                case 'employer':
                    header("Location: Employer/MainPage/EmployerMainPage.php");
                    break;
                case 'employee':
                    header("Location: Employee/MainPage/EmpMainPage.php");
                    break;
                case 'admin':
                    header("Location: Admin/MainPage/AdminMainPage.php");
                    break;
                default:
                    header("Location: Client/MainPage/ClientMainPage.php");
                    break;
            }
            exit();
        } else {
            echo "<script>
                alert('Неверный логин или пароль.');
                window.location.href = 'index.php'; 
            </script>";
        }
    } else {
        echo "<script>
            alert('Неверный логин или пароль.');
            window.location.href = 'index.php'; 
        </script>";
    }

    $stmt->close();
}

$conn->close();
?>
