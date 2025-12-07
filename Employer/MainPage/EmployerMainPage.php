<?php
  session_start();
  if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'employer') {
    echo "<script>
    alert('У вас нет прав доступа к этой странице.');
    window.location.href = '../../index.php'; 
    </script>";
    exit();
  }


$pageTitle = "Дела";
include '../../header.php';
include '../../db_connect.php';

$clientsQuery = "SELECT id, login FROM user WHERE role = 'client'";
$clientsResult = $conn->query($clientsQuery);
$clients = [];
if ($clientsResult && $clientsResult->num_rows > 0) {
    while ($row = $clientsResult->fetch_assoc()) {
        $clients[] = $row;
    }
}

$employeesQuery = "SELECT id, login FROM user WHERE role = 'employee'";
$employeesResult = $conn->query($employeesQuery);
$employees = [];
if ($employeesResult && $employeesResult->num_rows > 0) {
    while ($row = $employeesResult->fetch_assoc()) {
        $employees[] = $row;
    }
}
?>
<div class="container">
    <h1 class="title main-title"><?php echo $pageTitle?></h1>
    <form class="task-filter-form" method="GET" action="">

        <div class="filter-item">
            <input class="filter-select" type="text" id="search" name="search" value="<?php echo htmlspecialchars($_GET['search'] ?? ''); ?>" placeholder="Поиск по имени и описанию">
        </div>

        <div class="filter-item">
            <select class="filter-select" id="client" name="client">
                <option value="">Все клиенты</option>
                <?php foreach ($clients as $client): ?>
                    <option value="<?php echo $client['login']; ?>" <?php echo (isset($_GET['client']) && $_GET['client'] == $client['login']) ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($client['login']); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="filter-item">
            <select class="filter-select" id="employee" name="employee">
                <option value="">Все работники</option>
                <?php foreach ($employees as $employee): ?>
                    <option value="<?php echo $employee['login']; ?>" <?php echo (isset($_GET['employee']) && $_GET['employee'] == $employee['login']) ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($employee['login']); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="filter-item">
            <select class="filter-select" id="status" name="status">
                <option value="">Все статусы</option>
                <option value="Одобрено" <?php echo (isset($_GET['status']) && $_GET['status'] == 'Одобрено') ? 'selected' : ''; ?>>Одобрено</option>
                <option value="Начато" <?php echo (isset($_GET['status']) && $_GET['status'] == 'Начато') ? 'selected' : ''; ?>>Начато</option>
                <option value="В процессе" <?php echo (isset($_GET['status']) && $_GET['status'] == 'В процессе') ? 'selected' : ''; ?>>В процессе</option>
                <option value="Завершено" <?php echo (isset($_GET['status']) && $_GET['status'] == 'Завершено') ? 'selected' : ''; ?>>Завершено</option>
            </select>
        </div>
        <button class="btn submit-btn" type="submit">Фильтровать</button>
    </form>

    <div class="admin-table">
        <div class="table-row">
            <span class="row-item">Дело</span>
            <span class="row-item">Описание</span>
            <span class="row-item">Клиент</span>
            <span class="row-item">Работник</span>
            <span class="row-item">Статус</span>
        </div>
        <?php include 'FetchShowTasks.php'; ?> 
    </div>
</div>

<script>
document.querySelectorAll('.delete-btn').forEach((button) => {
  button.addEventListener('click', function () {
    const taskId = this.getAttribute('data-task-id');

    fetch('DeleteTask.php', {
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
          alert('Задание удалено.');
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