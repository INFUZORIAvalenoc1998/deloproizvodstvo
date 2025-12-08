<?php
include '../../db_connect.php';

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Запрос на получение сотрудников
$employeeQuery = "SELECT DISTINCT u.id, u.login FROM user u WHERE role = 'employee'";
$employeeResult = $conn->query($employeeQuery);

$employees = [];
if ($employeeResult && $employeeResult->num_rows > 0) {
    while ($employeeRow = $employeeResult->fetch_assoc()) {
        $employees[] = htmlspecialchars($employeeRow['login']);
    }
} else {
    echo "<p class='error'>Не удалось получить список работников.</p>";
}

// Запрос на получение сложностей
$complexityQuery = "SELECT id, name FROM task_complexity";
$complexityResult = $conn->query($complexityQuery);

$complexities = [];
if ($complexityResult && $complexityResult->num_rows > 0) {
    while ($complexityRow = $complexityResult->fetch_assoc()) {
        $complexities[] = [
            'id' => htmlspecialchars($complexityRow['id']),
            'name' => htmlspecialchars($complexityRow['name'])
        ];
    }
} else {
    echo "<p class='error'>Не удалось получить список уровней сложности.</p>";
}

// Запрос на получение заявок (без photoURL)
$query = "SELECT r.id, r.name, r.description, r.area_id, u.login, a.name AS area_name 
          FROM request r 
          JOIN user u ON r.client_id = u.id 
          JOIN area a ON r.area_id = a.id
          WHERE r.approved = 0 
          ORDER BY r.id DESC";
$result = $conn->query($query);

if (!$result) {
    die("Ошибка выполнения запроса: " . $conn->error);
}

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $requestId = htmlspecialchars($row['id']);
        $requestName = htmlspecialchars($row['name']);
        $requestDesc = htmlspecialchars($row['description']);
        $requestArea = htmlspecialchars($row['area_name']);
        $clientLogin = htmlspecialchars($row['login']);
        
        // Фото дела всегда не загружено, так как удалили photoURL
        $photoHTML = "<p class='task-photo'>Фото дела не загружено</p>";
        ?>
        <div class='task request' data-request-id='<?php echo $requestId ?>'>
            <h2 class='task-title'><?php echo $requestName ?></h2>
            <p class='task-desc'>Описание: <?php echo $requestDesc ?></p>
            <span class='task-client'>Клиент: <?php echo $clientLogin ?></span>
            <button class='task-details-btn'>Одобрить/отклонить...</button>

            <div class="details__overlay">
                <div class="details__pop-up">
                    <span class="task-id">#<?php echo $requestId; ?></span>
                    <button class="pop-up__close-btn">×</button>
                    <h3 class="pop-up__title">Одобрить/отклонить заявку</h3>
                    <div class="pop-up__inner">
                        <?php echo $photoHTML; ?>
                        <p class="pop-up__row"><strong>Название:</strong> <?php echo $requestName; ?></p>
                        <p class="pop-up__row"><strong>Описание:</strong> <?php echo $requestDesc; ?></p>
                        <p class="pop-up__row"><strong>Направление:</strong> <?php echo $requestArea; ?></p>
                        <p class="pop-up__row"><strong>Клиент:</strong> <?php echo $clientLogin; ?></p>
                    </div>

                    <h3 class="pop-up__title">Установить сложность</h3>
                    <div class="pop-up__inner">
                        <select name='complexity_select' class='complexity-select'>
                            <option value=''>Выберите сложность</option>
                            <?php 
                            foreach ($complexities as $complexity) {
                                echo "<option value='{$complexity['id']}'>{$complexity['name']}</option>";
                            }
                            ?>
                        </select>
                    </div>

                    <h3 class="pop-up__title">Рекомендация по выбору работника</h3>
                    <div class="pop-up__inner">
                        <div class="recs-table">
                            <div class="recs-table__row">
                                <span>Работник</span>
                                <span>Загруженность</span>
                                <span>Направления</span>
                                <span>Мэтч</span>
                            </div>
                            <?php 
                            $employeeQuery = "SELECT DISTINCT u.id, u.login FROM user u LEFT JOIN task t ON u.id = t.employee_id WHERE u.role = 'employee' ";
                            $employeeResult = $conn->query($employeeQuery);

                            $employeesData = [];
                            $requestAreaId = htmlspecialchars($row['area_id']);

                            // Загружаем коэффициенты из таблицы coefficients (один раз для всех работников)
                            $coefQuery = "SELECT name, value FROM coefficients";
                            $coefResult = $conn->query($coefQuery);

                            $coefficients = [];
                            if ($coefResult && $coefResult->num_rows > 0) {
                                while ($coefRow = $coefResult->fetch_assoc()) {
                                    $coefficients[$coefRow['name']] = $coefRow['value'];
                                }
                            }

                            // Устанавливаем коэффициенты
                            $coef1 = $coefficients['coef1'] ?? 0.4; // Значения по умолчанию
                            $coef2 = $coefficients['coef2'] ?? 0.3;
                            $coef3 = $coefficients['coef3'] ?? 0.3;

                            if ($employeeResult && $employeeResult->num_rows > 0) {
                                while ($employee = $employeeResult->fetch_assoc()) {
                                    $employeeId = $employee['id'];
                                    $employeeLogin = htmlspecialchars($employee['login']);

                                    // Извлечение аватара из таблицы emp_avatars
                                    $avatarQuery = "SELECT avatar FROM emp_avatars WHERE emp_id = ?";
                                    $avatarStmt = $conn->prepare($avatarQuery);
                                    $avatarStmt->bind_param("i", $employeeId);
                                    $avatarStmt->execute();
                                    $avatarResult = $avatarStmt->get_result();
                                    
                                    $avatarData = null;
                                    if ($avatarResult && $avatarResult->num_rows > 0) {
                                        $avatarRow = $avatarResult->fetch_assoc();
                                        $avatarData = $avatarRow['avatar'] ?? null;
                                    }
                                    
                                    // Количество дел с не завершенным статусом для текущего исполнителя
                                    $taskCountQuery = "SELECT COUNT(*) AS ongoing_tasks 
                                                    FROM task 
                                                    WHERE employee_id = ? AND status != 'Завершено'";
                                    $stmt = $conn->prepare($taskCountQuery);
                                    $stmt->bind_param("i", $employeeId);
                                    $stmt->execute();
                                    $taskCountResult = $stmt->get_result()->fetch_assoc();
                                    $ongoingTasks = $taskCountResult['ongoing_tasks'];

                                    // Направления завершенных дел для текущего исполнителя
                                    $areaQuery = "SELECT DISTINCT a.name AS area_name 
                                                FROM task t
                                                LEFT JOIN area a ON t.area_id = a.id
                                                WHERE t.employee_id = ? AND t.status = 'Завершено'";
                                    $stmt = $conn->prepare($areaQuery);
                                    $stmt->bind_param("i", $employeeId);
                                    $stmt->execute();
                                    $areaResult = $stmt->get_result();
                                    $areas = [];
                                    while ($areaRow = $areaResult->fetch_assoc()) {
                                        $areas[] = htmlspecialchars($areaRow['area_name']);
                                    }
                                    $areaList = implode(", ", $areas);

                                    // Подсчет завершенных задач по области для компетенции
                                    $completedTasksQuery = "SELECT COUNT(*) AS completed_tasks 
                                                            FROM task 
                                                            WHERE employee_id = ? AND status = 'Завершено' AND area_id = ?";
                                    $stmt = $conn->prepare($completedTasksQuery);
                                    $stmt->bind_param("ii", $employeeId, $requestAreaId);
                                    $stmt->execute();
                                    $completedTasksResult = $stmt->get_result()->fetch_assoc();
                                    $completedTasksCount = $completedTasksResult['completed_tasks'];
                                    
                                    if ($completedTasksCount >= 5) {
                                        $competence = 1;
                                    } elseif ($completedTasksCount < 5 && $completedTasksCount >= 3) {
                                        $competence = 0.5;
                                    } elseif ($completedTasksCount < 3 && $completedTasksCount >= 1) {
                                        $competence = 0.2;
                                    } else {
                                        $competence = 0;
                                    }

                                    // Вычисление нагруженности
                                    $standardNumberOfTasks = 10;
                                    $nagruz = $ongoingTasks / $standardNumberOfTasks;

                                    if ($nagruz <= 0.2) {
                                        $workload = 1;
                                    } elseif ($nagruz > 0.2 && $nagruz <= 0.5) {
                                        $workload = 0.5;
                                    } elseif ($nagruz > 0.5 && $nagruz <= 0.8) {
                                        $workload = 0.2;
                                    } else {
                                        $workload = 0;
                                    }

                                    // Проверка на количество выполненных сложных задач
                                    $hardTaskComplexityId = 3;
                                    $hardTaskQuery = "SELECT COUNT(*) AS hard_tasks
                                                    FROM task
                                                    WHERE employee_id = ? AND status = 'Завершено' AND complexity_id = ?";
                                    
                                    $stmt = $conn->prepare($hardTaskQuery);
                                    $stmt->bind_param("ii", $employeeId, $hardTaskComplexityId);
                                    $stmt->execute();
                                    $hardTaskResult = $stmt->get_result();
                                    
                                    $completedHardTasks = 0;
                                    if ($hardTaskResult && $hardTaskResult->num_rows > 0) {
                                        $hardTaskRow = $hardTaskResult->fetch_assoc();
                                        $completedHardTasks = $hardTaskRow['hard_tasks'];
                                    }

                                    // Устанавливаем значение hard_task на основе количества сложных задач
                                    if ($completedHardTasks >= 3) {
                                        $hard_task = 1;
                                    } elseif ($completedHardTasks == 1 || $completedHardTasks == 2) {
                                        $hard_task = 0.5;
                                    } else {
                                        $hard_task = 0;
                                    }

                                    // Обновленная формула мэтча
                                    $matchValue = $competence * $coef1 + $workload * $coef2 + $hard_task * $coef3;

                                    // Сохранение данных работника для последующей сортировки
                                    $employeesData[] = [
                                        'login' => $employeeLogin,
                                        'nagruz' => round($nagruz * 100),
                                        'areas' => $areaList,
                                        'matchValue' => round($matchValue * 100),
                                        'avatar' => $avatarData 
                                    ];
                                }
                            }

                            // Сортировка работников по мэтчу в порядке убывания
                            usort($employeesData, function ($a, $b) {
                                return $b['matchValue'] <=> $a['matchValue'];
                            });

                            // Вывод информации о каждом работнике
                            foreach ($employeesData as $data) {
                                echo "<div class='recs-table__row'>";
                                if ($data['avatar']) {
                                    echo "<img class='emp-avatar--table' src='data:image/jpeg;base64," . base64_encode($data['avatar']) . "'>";
                                } else {
                                    echo "<div class='emp-avatar--table placeholder'></div>";
                                }
                                echo "<p>{$data['login']}</p>
                                    <p>{$data['nagruz']}%</p>
                                    <p>" . (!empty($data['areas']) ? $data['areas'] : '<span style="font-weight: 400; opacity: 0.4;">нет направлений</span>') . "</p>
                                    <p>{$data['matchValue']}%</p>
                                    </div>";
                            }
                            ?>
                        </div>
                    </div>

                    <h3 class="pop-up__title">Выберите работника</h3>
                    <div class="pop-up__inner">
                        <select name='employee_select' class='employee-select'>
                            <option value=''>Выберите работника</option>
                            <?php 
                            foreach ($employees as $employee) {
                                $employeeSafe = htmlspecialchars($employee);
                                echo "<option value='$employeeSafe'>$employeeSafe</option>";
                            }
                            ?>
                        </select>
                    </div>
                    <div class='pop-up__buttons'>
                        <button data-task-id='<?php echo $requestId ?>' type='submit' class='admin-btn accept-btn'>Одобрить заявку</button>

                        <form action='CancelRequest.php' method='POST' style='display:inline;'>
                            <input type='hidden' name='id' value='<?php echo $requestId ?>'>
                            <button type='submit' class='admin-btn cancel-btn'>Отклонить заявку</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
        <?php
    }
} else {
    echo "<p class='no-tasks'>Заявок нет.</p>";
}

// Закрываем соединение
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

    // Добавление обработчика нажатия клавиши
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