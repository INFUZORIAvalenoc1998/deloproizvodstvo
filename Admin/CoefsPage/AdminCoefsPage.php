<?php
session_start();
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    echo "<script>
    alert('У вас нет прав доступа к этой странице.');
    window.location.href = '../../index.php'; 
    </script>";
    exit();
}

$pageTitle = "Управление коэффициентами";
include '../../auth_check.php';
include '../../header.php';
include '../../db_connect.php';

// Запрос на получение коэффициентов
$query = "SELECT id, name, value FROM coefficients";
$result = $conn->query($query);
?>

<div class="container">
    <h1 class="title main-title"><?php echo $pageTitle ?></h1>

    <div class="admin-table admin-table--coefficients">
        <div class="table-row table-row--coefficients">
            <span class="row-item">Коэффициент</span>
            <span class="row-item">Значение</span>
            <span class="row-item">Изменить значение</span>
        </div>

        <?php
        if ($result && $result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                echo '<div class="table-row table-row--coefficients">';
                echo '<span class="row-item">' . htmlspecialchars($row['name']) . '</span>';
                echo '<span class="row-item">' . htmlspecialchars($row['value']) . '</span>';
                
                echo '<span class="row-item">';
                echo '<form class="update-coefficient-form" method="POST" action="UpdateCoefficient.php">';
                echo '<input type="hidden" name="coefficient_id" value="' . $row['id'] . '">';
                echo '<input type="number" step="0.01" name="value" value="' . htmlspecialchars($row['value']) . '">';
                echo '<button class="save-btn" type="submit">Сохранить</button>';
                echo '</form>';
                echo '</span>';
                echo '</div>';
            }
        } else {
            echo '<div class="table-row"><span class="row-item">Нет коэффициентов</span></div>';
        }
        ?>
    </div>
</div>

<?php include '../../footer.php'; ?>
