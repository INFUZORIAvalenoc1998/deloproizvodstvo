<?php
  session_start();
  if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'employer') {
    echo "<script>
    alert('У вас нет прав доступа к этой странице.');
    window.location.href = '../../index.php'; 
    </script>";
    exit();
  }

$pageTitle = "Заявки";
include '../../header.php';
?>

<div class="container">
    <h1 class="title main-title"><?php echo $pageTitle?></h1>

        <div class="tasks">
            <?php include 'FetchShowRequests.php';  ?> 
        </div>
    </div>
<script src="AcceptRequest.js"></script>


<?php include '../../footer.php'; ?>
