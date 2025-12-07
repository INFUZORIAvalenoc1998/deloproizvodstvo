<?php
include '../../db_connect.php';

// Проверяем, установлен ли taskId
if (isset($taskId)) {
    // Подготовим SQL-запрос для получения истории задач
    $sql = "SELECT action, status, date, employee_id FROM task_history WHERE task_id = ? ORDER BY date DESC";
    $stmt = $conn->prepare($sql);

    // Проверяем успешность подготовки
    if ($stmt) {
        $stmt->bind_param("i", $taskId);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $action = $row['action'];
                $status = $row['status'];
                $date = $row['date'];

                // Получаем логин работника, если это необходимо
                $employeeLogin = '';
                if ($action == 'employee') {
                    $employeeId = $row['employee_id'];
                    $empSql = "SELECT login FROM user WHERE id = ?";
                    $empStmt = $conn->prepare($empSql);
                    if ($empStmt) {
                        $empStmt->bind_param("i", $employeeId);
                        $empStmt->execute();
                        $empResult = $empStmt->get_result();
                        if ($empResult->num_rows > 0) {
                            $employee = $empResult->fetch_assoc();
                            $employeeLogin = htmlspecialchars($employee['login']);
                        }
                        $empStmt->close(); // Закрываем только если был успешно подготовлен
                    }
                }

                // Формируем вывод в зависимости от значения action
                if ($action == 'status') {
                    echo '<p class="pop-up__row">Исполнитель ' . htmlspecialchars($employeeLogin) . ' изменил статус дела на "' . htmlspecialchars($status) . '" <span class="pop-up__date">' . htmlspecialchars($date) . '</span></p>';
                } elseif ($action == 'employee') {
                    echo '<p class="pop-up__row">Исполнитель изменен на ' . htmlspecialchars($employeeLogin) . ' <span class="pop-up__date">' . htmlspecialchars($date) . '</span></p>';
                }
            }
        } else {
            echo '<p class="pop-up__row">История изменений не найдена.</p>';
        }

        $stmt->close(); // Закрываем только один раз, после использования
    } else {
        echo '<p class="pop-up__row">Ошибка при подготовке SQL-запроса.</p>';
    }

    $conn->close(); // Закрываем соединение с базой данных
} else {
    echo '<p class="pop-up__row">ID дела не передан.</p>';
}
?>
