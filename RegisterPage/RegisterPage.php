<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Регистрация</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="/deloproizvodstvo/assets/css/normalize.css">
    <link rel="stylesheet" href="/deloproizvodstvo/assets/css/styles.css">
</head>
<body>
    <div class="container">
        <h1 class="login-title">Регистрация</h1>

        <form action="Register.php" method="POST" class="form login-form">
                <input maxlength="30" class="input login-input" type="e-mail" id="login" name="login" placeholder="Введите логин" required>
                <input maxlength="30" class="input login-input" type="password" id="password" name="password" placeholder="Введите пароль" required>
                <input maxlength="30" class="input login-input" type="password" id="passwordAgain" name="passwordAgain" placeholder="Пароль снова" required>
            <button type="submit" class="btn btn-login">Зарегестрироваться</button>
        </form>

            <a class="register-link" href="../index.php">Войти</a>
    </div>

    <?php include '../footer.php'; ?>