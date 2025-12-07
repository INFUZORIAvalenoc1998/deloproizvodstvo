<?php
// error.php
$pageTitle = "Ошибка";
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($pageTitle); ?></title>
    <link rel="stylesheet" href="/deloproizvodstvo/assets/css/styles.css">
</head>
<body>
<div class="container">
    <h1 class="title main-title"><?php echo htmlspecialchars($pageTitle); ?></h1>
    <p>Произошла ошибка при подключении к базе данных.</p>
    <?php
    if (isset($_GET['code']) && isset($_GET['message'])) {
        echo "<p><strong>Код ошибки:</strong> " . htmlspecialchars($_GET['code']) . "</p>";
        echo "<p><strong>Сообщение:</strong> " . htmlspecialchars($_GET['message']) . "</p>";
    }
    ?>
    <a href="/deloproizvodstvo/index.php">Вернуться на главную</a>
</div>
</body>
</html>