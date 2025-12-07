<?php
include '../../db_connect.php';

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

$query = "
    SELECT t.id AS task_id, t.name AS task_name, t.description AS task_desc, t.status, u.login AS client_login, a.name AS area_name 
    FROM task t
    JOIN user u ON t.client_id = u.id
    LEFT JOIN area a ON t.area_id = a.id  -- Добавляем JOIN для получения названия направления
    WHERE t.employee_id = ?
    ORDER BY t.id DESC
";

$stmt = $conn->prepare($query);
if ($stmt === false) {
    die('Ошибка подготовки запроса: ' . $conn->error);
}

$employeeId = $_SESSION['employee_id'];
$stmt->bind_param("i", $employeeId);
$stmt->execute();
$result = $stmt->get_result();

if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $taskId = htmlspecialchars($row['task_id']);
        $taskName = htmlspecialchars($row['task_name']);
        $taskDesc = htmlspecialchars($row['task_desc']);
        $clientLogin = htmlspecialchars($row['client_login']);
        $status = htmlspecialchars($row['status']);
        $taskArea = htmlspecialchars($row['area_name']); // Получаем название направления
        
        ?> 
        <div class='table-row'>
            <span class="task-id task-id--emp">#<?php echo $taskId; ?></span>
            <div class='row-item'><?php echo $taskName; ?></div>
            <div class='row-item'><?php echo $taskDesc; ?></div>
            <div class='row-item'><?php echo $clientLogin; ?></div>
            <div class='row-item'><?php echo !empty($taskArea) ? $taskArea : '<span style="font-weight: 400; opacity: 0.4;">нет направления</span>'; ?></div>
            <div class='row-item'>
                <form class="status-form" method="POST" action="UpdateTaskStatus.php">
                    <input type="hidden" name="task_id" value="<?php echo $taskId; ?>">
                    <select class="status-select" name="status">
                        <option value="Начато" <?php echo $status == 'Начато' ? 'selected' : ''; ?>>Начато</option>
                        <option value="В процессе" <?php echo $status == 'В процессе' ? 'selected' : ''; ?>>В процессе</option>
                        <option value="Завершено" <?php echo $status == 'Завершено' ? 'selected' : ''; ?>>Завершено</option>
                    </select>
                    <button type="submit" class="btn btn-update-status">Обновить</button>
                </form>
            </div>
        </div>
        <?php
    }
} else {
    echo "<p class='no-tasks'>Заданий нет.</p>";
}

$stmt->close();
$conn->close();
?>
