<?php
  session_start();
  if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    echo "<script>
    alert('У вас нет прав доступа к этой странице.');
    window.location.href = '../../index.php'; 
    </script>";
    exit();
  }

  ini_set('display_errors', 1);
  ini_set('display_startup_errors', 1);
  error_reporting(E_ALL);

$pageTitle = "Управление делами";
include '../../header.php';
include '../../db_connect.php';


?>
<div class="container">
    <h1 class="title main-title"><?php echo $pageTitle?></h1>
    <div class="tasks">
        <?php
        include 'FetchShowTasks.php';
        ?>
      </div>
</div>

<script>
document.querySelectorAll('.delete-btn').forEach((button) => {
  button.addEventListener('click', function () {
    const taskId = this.getAttribute('data-task-id');

    fetch('DeleteHistory.php', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/x-www-form-urlencoded',
      },
      body: `id=${encodeURIComponent(taskId)}`,
    })
      .then((response) => response.text())
      .then((data) => {
        console.log('Удаление задания:', data);
        if (data.trim() === 'success') {
          alert('Запись в истории дел удалена.');
          const taskRow = document.querySelector(`.table-row[data-task-id='${taskId}']`);
          taskRow.remove();
        } else {
          alert('Ошибка при удалении задания: ' + data);
        }
      })
      .catch((error) => {
        console.error('Ошибка:', error);
        alert('Ошибка при выполнении запроса.');
      });
  });
});
</script>

<?php include '../../footer.php'; ?>