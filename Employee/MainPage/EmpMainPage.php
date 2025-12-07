<?php
  session_start();
  if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'employee') {
    echo "<script>
    alert('У вас нет прав доступа к этой странице.');
    window.location.href = '../../index.php'; 
    </script>";
    exit();
  }

$pageTitle = "Все дела";
include '../../header.php';
?>
    <div class="container">
    <h1 class="title main-title"><?php echo $pageTitle?></h1>
        <div class="admin-table">
            <div class="table-row">
                <span class="row-item">
                    Дело
                </span>
                <span class="row-item">
                    Описание
                </span>
                <span class="row-item">
                    Клиент
                </span>
                <span class="row-item">
                    Направление
                </span>
            </div>
        <?php include 'FetchShowTasks.php';  ?> 
        </div>
    </div>

    <?php include '../../footer.php'; ?>