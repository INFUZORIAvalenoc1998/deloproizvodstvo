<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Вход</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="/deloproizvodstvo/assets/css/normalize.css">
    <link rel="stylesheet" href="/deloproizvodstvo/assets/css/styles.css">
</head>
<body>
    <div class="container">
        <h1 class="login-title">Вход</h1>

        <form action="login.php" method="POST" class="form login-form">
                <input maxlength="30" class="input login-input" type="text" id="login" name="login" placeholder="Введите логин" required>
                <input maxlength="30" class="input login-input" type="password" id="password" name="password" placeholder="Введите пароль" required>
                <button type="submit" class="btn btn-login">Войти</button>
            <label>
                <input type="checkbox" name="remember_me" id="remember_me"> Запомнить меня
                </label>
        </form>

        <a class="register-link" href="/deloproizvodstvo/RegisterPage/RegisterPage.php">Зарегистрироваться</a>

    </div>

<?php include 'footer.php'; ?>


<script>
    document.addEventListener("DOMContentLoaded", () => {
        const loginInput = document.getElementById("login");
        const savedLogin = getCookie("remembered_login");

        if (savedLogin) {
            loginInput.value = savedLogin;
            document.getElementById("remember_me").checked = true;
        }
    });

    function getCookie(name) {
        const value = `; ${document.cookie}`;
        const parts = value.split(`; ${name}=`);
        if (parts.length === 2) return parts.pop().split(";").shift();
    }
</script>
