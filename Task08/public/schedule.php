<?php
require_once '../includes/config.php';
require_once '../includes/database.php';
require_once '../includes/functions.php';

$db = getDB();
$employeeId = get('employee_id');

if (!$employeeId) {
    redirect('index.php');
}

// Получение информации о мастере
$stmt = $db->prepare("SELECT * FROM employees WHERE id = ?");
$stmt->execute([$employeeId]);
$employee = $stmt->fetch();

if (!$employee) {
    redirect('index.php');
}

// Получение графика мастера
$stmt = $db->prepare("SELECT * FROM schedule WHERE employee_id = ? ORDER BY work_date DESC");
$stmt->execute([$employeeId]);
$schedule = $stmt->fetchAll();

// Обработка добавления записи
if (isPost() && isset($_POST['add_schedule'])) {
    $workDate = post('work_date');
    $startTime = post('start_time');
    $endTime = post('end_time');
    $notes = post('notes');
    
    try {
        $stmt = $db->prepare("
            INSERT INTO schedule (employee_id, work_date, start_time, end_time, notes)
            VALUES (?, ?, ?, ?, ?)
        ");
        $stmt->execute([$employeeId, $workDate, $startTime, $endTime, $notes]);
        redirect("schedule.php?employee_id=$employeeId");
    } catch (PDOException $e) {
        $error = "Ошибка при добавлении: " . $e->getMessage();
    }
}

// Обработка удаления записи
if (isPost() && isset($_POST['delete_schedule'])) {
    $id = post('id');
    $stmt = $db->prepare("DELETE FROM schedule WHERE id = ? AND employee_id = ?");
    $stmt->execute([$id, $employeeId]);
    redirect("schedule.php?employee_id=$employeeId");
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>График работы</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-4">
        <h1>График работы мастера</h1>
        <h3><?= escape($employee['first_name'] . ' ' . $employee['last_name']) ?></h3>
        
        <?php if (isset($error)): ?>
        <div class="alert alert-danger"><?= escape($error) ?></div>
        <?php endif; ?>
        
        <div class="card mt-4">
            <div class="card-header">
                <h5 class="mb-0">Добавить запись в график</h5>
            </div>
            <div class="card-body">
                <form method="post">
                    <div class="row">
                        <div class="col-md-3 mb-3">
                            <label for="work_date" class="form-label">Дата работы</label>
                            <input type="date" class="form-control" id="work_date" name="work_date" 
                                   value="<?= date('Y-m-d') ?>" required>
                        </div>
                        <div class="col-md-3 mb-3">
                            <label for="start_time" class="form-label">Начало работы</label>
                            <input type="time" class="form-control" id="start_time" name="start_time" 
                                   value="09:00" required>
                        </div>
                        <div class="col-md-3 mb-3">
                            <label for="end_time" class="form-label">Окончание работы</label>
                            <input type="time" class="form-control" id="end_time" name="end_time" 
                                   value="18:00" required>
                        </div>
                        <div class="col-md-3 mb-3">
                            <label for="notes" class="form-label">Примечания</label>
                            <input type="text" class="form-control" id="notes" name="notes">
                        </div>
                    </div>
                    <button type="submit" name="add_schedule" class="btn btn-primary">Добавить</button>
                </form>
            </div>
        </div>
        
        <div class="card mt-4">
            <div class="card-header">
                <h5 class="mb-0">Текущий график</h5>
            </div>
            <div class="card-body">
                <?php if (empty($schedule)): ?>
                <p class="text-muted">Нет записей в графике</p>
                <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>Дата</th>
                                <th>Начало</th>
                                <th>Окончание</th>
                                <th>Продолжительность</th>
                                <th>Примечания</th>
                                <th>Действия</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($schedule as $item): ?>
                            <tr>
                                <td><?= formatDate($item['work_date']) ?></td>
                                <td><?= formatTime($item['start_time']) ?></td>
                                <td><?= formatTime($item['end_time']) ?></td>
                                <td>
                                    <?php
                                    $start = strtotime($item['start_time']);
                                    $end = strtotime($item['end_time']);
                                    $hours = round(($end - $start) / 3600, 1);
                                    echo $hours . ' ч';
                                    ?>
                                </td>
                                <td><?= escape($item['notes'] ?? '—') ?></td>
                                <td>
                                    <form method="post" style="display: inline;">
                                        <input type="hidden" name="id" value="<?= $item['id'] ?>">
                                        <button type="submit" name="delete_schedule" class="btn btn-danger btn-sm"
                                                onclick="return confirm('Удалить эту запись?')">
                                            Удалить
                                        </button>
                                    </form>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <?php endif; ?>
            </div>
        </div>
        
        <div class="mt-3">
            <a href="index.php" class="btn btn-secondary">Назад к списку</a>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>