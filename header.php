
<?php
//header("Content-Security-Policy: default-src 'self';script-src 'self'; style-src 'self'; frame-ancestors 'none'; form-action 'self';");
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $pageTitle ?? 'Делопроизводство'; ?></title>
<!--    <link rel="preconnect" href="https://fonts.googleapis.com">-->
<!--    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>-->
<!--    <link href="https://fonts.googleapis.com/css2?family=Poppins&display=swap" rel="stylesheet">-->
    <link rel="stylesheet" href="/deloproizvodstvo/assets/css/normalize.css">
    <link rel="stylesheet" href="/deloproizvodstvo/assets/css/styles.css">
</head>
<body>
<header class="header">
    <nav class="header__nav">
        <?php
        // session_start();

        if (isset($_SESSION['login'])) {
            // Определяем роль пользователя
            $role = $_SESSION['role'] ?? 'client'; // Предполагаем, что роль хранится в сессии

            if ($role === 'employer') {
                echo '<a class="nav-link" href="/deloproizvodstvo/Employer/MainPage/EmployerMainPage.php">Дела</a>';
                echo '<a class="nav-link" href="/deloproizvodstvo/Employer/RequestsPage/EmployerRequestsPage.php">Заявки</a>';
            } else if ($role === 'employee') {
                echo '<a class="nav-link" href="/deloproizvodstvo/Employee/MainPage/EmpMainPage.php">Все дела</a>';
                echo '<a class="nav-link" href="/deloproizvodstvo/Employee/ProfilePage/ProfilePage.php">Личный кабинет</a>';
            } else if(($role === 'client')) {
                echo '<a class="nav-link" href="/deloproizvodstvo/Client/MainPage/ClientMainPage.php">Предложить дело</a>';
                echo '<a class="nav-link" href="/deloproizvodstvo/Client/RequestsPage/ClientRequestsPage.php">Ваши заявки</a>';
                echo '<a class="nav-link" href="/deloproizvodstvo/Client/TasksPage/ClientTasksPage.php">Ваши дела</a>';
            } else if (($role === 'admin')) {
              echo '<a class="nav-link" href="/deloproizvodstvo/Admin/MainPage/AdminMainPage.php">Управление ролями</a>';
              echo '<a class="nav-link" href="/deloproizvodstvo/Admin/TasksPage/AdminTasksPage.php">Управление делами</a>';
              echo '<a class="nav-link" href="/deloproizvodstvo/Admin/CoefsPage/AdminCoefsPage.php">Управление коэффициентами</a>';

            }
            
            // Приветствие
            echo '<span class="header__user">Здравствуйте, ' . htmlspecialchars($_SESSION['login']) . '</span>';

            echo '<form action="/deloproizvodstvo/logout.php" method="post">
            <button class="logout-btn" type="submit">Выйти</button>
          </form>';
        }

        ?>
        
    </nav>
</header>