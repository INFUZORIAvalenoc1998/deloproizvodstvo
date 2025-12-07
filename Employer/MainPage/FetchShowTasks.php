<?php
include '../../db_connect.php';

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

try {
    // Проверяем, установлен ли ID работодателя в сессии
    if (!isset($_SESSION['employer_id'])) {
        throw new Exception("Ошибка: ID работодателя не установлен.");
    }

    $employerId = $_SESSION['employer_id'];

    // Получение параметров фильтров из GET-запроса
    $clientFilter = isset($_GET['client']) && !empty($_GET['client']) ? $_GET['client'] : null;
    $employeeFilter = isset($_GET['employee']) && !empty($_GET['employee']) ? $_GET['employee'] : null;
    $statusFilter = isset($_GET['status']) && !empty($_GET['status']) ? $_GET['status'] : null;
    $searchFilter = isset($_GET['search']) && !empty($_GET['search']) ? $_GET['search'] : null;

    // Базовый SQL-запрос с фильтрацией по работодателю
    $query = "
        SELECT t.id AS task_id, t.name AS task_name, t.description AS task_description, 
               u_client.login AS client_login, u_employee.login AS employee_login, t.employee_id, t.status 
        FROM task t
        JOIN user u_client ON t.client_id = u_client.id
        JOIN user u_employee ON t.employee_id = u_employee.id
        WHERE t.employer_id = ?
    ";

    $params = [$employerId];
    $types = 'i';

    if ($searchFilter) {
        $searchFilter = '%' . $conn->real_escape_string($searchFilter) . '%';
        $query .= " AND (t.name LIKE ? OR t.description LIKE ?)";
        $params[] = $searchFilter;
        $params[] = $searchFilter;
        $types .= 'ss';
    }

    if ($clientFilter) {
        $query .= " AND u_client.login = ?";
        $params[] = $conn->real_escape_string($clientFilter);
        $types .= 's';
    }

    if ($employeeFilter) {
        $query .= " AND u_employee.login = ?";
        $params[] = $conn->real_escape_string($employeeFilter);
        $types .= 's';
    }

    if ($statusFilter) {
        $query .= " AND t.status = ?";
        $params[] = $conn->real_escape_string($statusFilter);
        $types .= 's';
    }

    $query .= " ORDER BY t.id DESC";

    $stmt = $conn->prepare($query);

    if (!$stmt) {
        throw new Exception('Ошибка подготовки запроса: ' . $conn->error);
    }

    if (!empty($params)) {
        $stmt->bind_param($types, ...$params);
    }

    $stmt->execute();
    $result = $stmt->get_result();

    if (!$result) {
        throw new Exception('Ошибка выполнения запроса: ' . $conn->error);
    }

    if ($result->num_rows > 0) {
        // Получаем всех работников для выбора в select
        $employees = [];
        $empResult = $conn->query("SELECT id, login FROM user WHERE role = 'employee'");
        if (!$empResult) {
            throw new Exception('Ошибка получения работников: ' . $conn->error);
        }

        while ($empRow = $empResult->fetch_assoc()) {
            $employees[$empRow['id']] = $empRow['login'];
        }

        while ($row = $result->fetch_assoc()) {
            $taskId = htmlspecialchars($row['task_id']);
            $taskName = htmlspecialchars($row['task_name']);
            $taskDesc = htmlspecialchars($row['task_description']);
            $clientLogin = htmlspecialchars($row['client_login']);
            $currentEmployeeId = $row['employee_id'];
            $status = htmlspecialchars($row['status']);
            ?>
            <div class='table-row' data-task-id='<?php echo $taskId; ?>'>
                <div class='row-item'><?php echo $taskName; ?></div>
                <div class='row-item'><?php echo $taskDesc; ?></div>
                <div class='row-item'><?php echo $clientLogin; ?></div>
                <div class='row-item'>
                    <form class="update-employee__form" method="POST" action="UpdateEmployee.php">
                        <select class="employee-select" name="employee_id">
                            <?php
                            foreach ($employees as $empId => $empLogin) {
                                $selected = $empId == $currentEmployeeId ? 'selected' : '';
                                echo "<option value='$empId' $selected>$empLogin</option>";
                            }
                            ?>
                        </select>
                        <input type="hidden" name="task_id" value="<?php echo $taskId; ?>">
                        <button type="submit" class="btn update-employee__btn">Обновить</button>
                    </form>
                </div>
                <div class='row-item'><?php echo $status; ?></div>
                <button class='admin-btn cancel-btn delete-btn' data-task-id='<?php echo $taskId; ?>'>×</button>
            </div>
            <?php
        }
    } else {
        throw new Exception("<p class='no-tasks'>Заданий по запросу " . $_GET['search'] . " нет.</p>");
//        throw new Exception("<p class='no-tasks'>Заданий по запросу \"" . htmlspecialchars($_GET['search']) . "\" нет.</p>");
    }

    $stmt->close();
    $conn->close();
} catch (Exception $e) {
//    echo "<p class='no-tasks'>Ошибка: " . htmlspecialchars($e->getMessage()) . "</p>";
    echo $e->getMessage();
}
?>
