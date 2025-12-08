<?php
// Убираем session_start() здесь, так как он уже вызывается в db_connect.php или на верхнем уровне
// include уже включает файл с сессией

include '../../db_connect.php';

// Проверяем, активна ли сессия
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['login'])) {
    die('Вы должны войти в систему, чтобы просмотреть дела.');
}

$client_id = $_SESSION['client_id'] ?? null;
if ($client_id === null) {
    die('ID клиента не найден.');
}

$sql = "SELECT 
        t.id AS task_id,
        t.name AS task_name,
        t.status AS task_status,
        t.description AS task_description,
        t.date AS task_date,
        a.name AS area_name,
        u.login AS employee_login
    FROM task t
    LEFT JOIN user u ON t.employee_id = u.id
    LEFT JOIN area a ON t.area_id = a.id
    WHERE t.client_id = ?
    ORDER BY t.id DESC";

$stmt = $conn->prepare($sql);
if (!$stmt) {
    die("Ошибка подготовки запроса: " . $conn->error);
}

$stmt->bind_param("i", $client_id);
$stmt->execute();
$result = $stmt->get_result();

$cachedEmployees = []; // Кэш логинов сотрудников
$cachedHistory = [];   // Кэш истории задач

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $TaskTitle = $row['task_name'];
        $TaskId = $row['task_id'];
        $TaskDesc = $row['task_description'];
        $TaskArea = $row['area_name'];
        $TaskStatus = $row['task_status'];
        $TaskEmployee = $row['employee_login'] ?? '-';
        $TaskDate = $row['task_date'];
        
        // Фото дела всегда не загружено, так как столбец photoURL убран
        $photoHTML = "<p class='task-photo'>Фото дела не загружено</p>";

        ?>
        <div class="task">
            <h2 class="task-title"><?php echo htmlspecialchars($TaskTitle); ?></h2>
            <p class="task-desc"><?php echo htmlspecialchars($TaskDesc); ?></p>
            <span class="task-id">#<?php echo $TaskId; ?></span>
            <button class="task-details-btn">Подробнее...</button>

            <div class="task-bottom">
                <p class="task-status">Статус: <?php echo htmlspecialchars($TaskStatus); ?></p>
                <p class="task-employee">Исполнитель: <?php echo htmlspecialchars($TaskEmployee); ?></p>
            </div>

            <div class="details__overlay">
                <div class="details__pop-up">
                    <span class="task-id">#<?php echo $TaskId; ?></span>
                    <button class="pop-up__close-btn">×</button>
                    <h3 class="pop-up__title">Подробная информация по делу</h3>
                    <div class="pop-up__inner">
                        <?php echo $photoHTML; ?>
                        <p class="pop-up__row"><strong>Название:</strong> <?php echo htmlspecialchars($TaskTitle); ?></p>
                        <p class="pop-up__row"><strong>Описание:</strong> <?php echo htmlspecialchars($TaskDesc); ?></p>
                        <p class="pop-up__row"><strong>Направление:</strong> <?php echo htmlspecialchars($TaskArea); ?></p>
                        <p class="pop-up__row"><strong>Статус:</strong> <?php echo htmlspecialchars($TaskStatus); ?></p>
                        <p class="pop-up__row"><strong>Исполнитель:</strong> <?php echo htmlspecialchars($TaskEmployee); ?></p>
                        <p class="pop-up__row"><strong>Дата одобрения:</strong> <?php echo htmlspecialchars($TaskDate); ?></p>
                    </div>
                    <h3 class="pop-up__title">История изменений</h3>
                    <div class="pop-up__inner">
                        <p class="pop-up__row">Заявка одобрена. Дело открыто.<span class="pop-up__date"><?php echo htmlspecialchars($TaskDate); ?></span></p>

                        <?php
                        // Кэширование истории задач
                        if (!isset($cachedHistory[$TaskId])) {
                            $historySql = "SELECT action, status, date, employee_id FROM task_history WHERE task_id = ? ORDER BY date DESC";
                            $stmtHistory = $conn->prepare($historySql);
                            $stmtHistory->bind_param("i", $TaskId);
                            $stmtHistory->execute();
                            $historyResult = $stmtHistory->get_result();

                            $cachedHistory[$TaskId] = $historyResult->fetch_all(MYSQLI_ASSOC);
                            $stmtHistory->close();
                        }

                        foreach ($cachedHistory[$TaskId] as $history) {
                            $action = htmlspecialchars($history['action']);
                            $status = htmlspecialchars($history['status']);
                            $date = htmlspecialchars($history['date']);
                            $employeeId = $history['employee_id'];

                            // Кэширование логинов сотрудников
                            if ($employeeId && !isset($cachedEmployees[$employeeId])) {
                                $empSql = "SELECT login FROM user WHERE id = ?";
                                $empStmt = $conn->prepare($empSql);
                                $empStmt->bind_param("i", $employeeId);
                                $empStmt->execute();
                                $empResult = $empStmt->get_result();

                                $cachedEmployees[$employeeId] = $empResult->fetch_assoc()['login'] ?? '-';
                                $empStmt->close();
                            }

                            $employeeLogin = htmlspecialchars($cachedEmployees[$employeeId] ?? '-');
                            if ($action === 'status') {
                                echo '<p class="pop-up__row">Исполнитель ' . $employeeLogin . ' изменил статус дела на "' . $status . '" <span class="pop-up__date">' . $date . '</span></p>';
                            } elseif ($action === 'employee') {
                                echo '<p class="pop-up__row">Исполнитель изменен на ' . $employeeLogin . ' <span class="pop-up__date">' . $date . '</span></p>';
                            }
                        }
                        ?>
                    </div>
                </div>
            </div>
        </div>
        <?php
    }
} else {
    echo "<p>Вы не предложили ни одного дела либо ни одну из ваших заявок не одобрили.</p>";
}

$stmt->close();
$conn->close();
?>


<script>
document.addEventListener('DOMContentLoaded', () => {
    const detailButtons = document.querySelectorAll('.task-details-btn');

    detailButtons.forEach(button => {
        button.addEventListener('click', function () {
            const overlay = this.closest('.task').querySelector('.details__overlay');
            overlay.style.display = 'flex';
            document.body.classList.add('no-scroll');
        });
    });

    document.querySelectorAll('.pop-up__close-btn').forEach(closeButton => {
        closeButton.addEventListener('click', function () {
            closeOverlay(this.closest('.details__overlay'));
        });
    });

    document.querySelectorAll('.details__overlay').forEach(overlay => {
        overlay.addEventListener('click', (event) => {
            if (event.target === overlay) {
                closeOverlay(overlay);
            }
        });
    });

    document.addEventListener('keydown', (event) => {
        if (event.key === 'Escape') {
            document.querySelectorAll('.details__overlay').forEach(overlay => {
                if (overlay.style.display === 'flex') {
                    closeOverlay(overlay);
                }
            });
        }
    });

    function closeOverlay(overlay) {
        overlay.style.display = 'none'; 
        document.body.classList.remove('no-scroll');
    }
});
</script>