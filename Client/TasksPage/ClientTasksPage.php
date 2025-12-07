<?php
session_start();
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'client') {
  echo "<script>
  alert('У вас нет прав доступа к этой странице.');
  window.location.href = '../../index.php'; 
  </script>";
  exit();
}

$pageTitle = "Ваши дела";
include '../../header.php';

?>
    <div class="container">
        <h1 class="title"><?php echo $pageTitle?></h1>

        <div class="tasks">
        <?php
        include 'FetchShowTasks.php';
        ?>
        </div>
    </div>


<?php include '../../footer.php'; ?>
