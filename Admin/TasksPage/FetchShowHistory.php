<?php
include '../../db_connect.php';

$query = "SELECT th.id, t.name AS task_name, u.login AS employee, th.action, 
          COALESCE(th.status, 'Не изменен') AS status, th.date 
          FROM task_history th
          JOIN task t ON th.task_id = t.id
          JOIN user u ON th.employee_id = u.id
          ORDER BY th.date DESC";

$result = $conn->query($query);

if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $historyId = htmlspecialchars($row['id']);
        $taskName = htmlspecialchars($row['task_name']);
        $employee = htmlspecialchars($row['employee']);
        $action = htmlspecialchars($row['action']);
        $status = htmlspecialchars($row['status']);
        $date = htmlspecialchars($row['date']);

        if ($status === 'status') {
          echo "<p>Полученный статус: $status</p>";
          $statusText = "Изменение статуса дела";
      } elseif ($status === 'employee') {
          $statusText = "Изменение исполнителя дела";
      } else {
          $statusText = !empty($status) ? $status : "Статус не изменен"; 
      }

        ?>
        <div class="table-row" data-task-id="<?php echo $historyId; ?>">
            <span class="row-item"><?php echo $taskName; ?></span>
            <span class="row-item"><?php echo $employee; ?></span>
            <span class="row-item"><?php echo $action; ?></span>
            <span class="row-item"><?php echo $statusText; ?></span>
            <span class="row-item"><?php echo $date; ?></span>
            <button class="admin-btn delete-btn cancel-btn" data-task-id="<?php echo $historyId; ?>">×</button>
        </div>
        <?php
    }
} else {
    echo "<p>Нет записей в истории изменений.</p>";
}

$conn->close();
?>
