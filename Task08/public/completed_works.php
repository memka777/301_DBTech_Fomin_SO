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

// Получение выполненных работ
$stmt = $db->prepare("
    SELECT * FROM completed_works 
    WHERE employee_id = ? 
    ORDER BY work_date DESC
");
$stmt->execute([$employeeId]);
$works = $stmt->fetchAll();

// Обработка добавления работы
if (isPost() && isset($_POST['add_work'])) {
    $serviceName = post('service_name');
    $workDate = post('work_date');
    $price = post('price');
    $duration = post('duration_minutes');
    $clientName = post('client_name');
    $carModel = post('car_model');
    $notes = post('notes');
    
    try {
        $stmt = $db->prepare("
            INSERT INTO completed_works 
            (employee_id, service_name, work_date, price, duration_minutes, client_name, car_model, notes)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?)
        ");
        $stmt->execute([
            $employeeId, $serviceName, $workDate, $price, 
            $duration, $clientName, $carModel, $notes
        ]);
        redirect("completed_works.php?employee_id=$employeeId");
    } catch (PDOException $e) {
        $error = "Ошибка при добавлении: " . $e->getMessage();
    }
}

// Обработка удаления работы
if (isPost() && isset($_POST['delete_work'])) {
    $id = post('id');
    $stmt = $db->prepare("DELETE FROM completed_works WHERE id = ? AND employee_id = ?");
    $stmt->execute([$id, $employeeId]);
    redirect("completed_works.php?employee_id=$employeeId");
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Выполненные работы</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-4">
        <h1>Выполненные работы мастера</h1>
        <h3><?= escape($employee['first_name'] . ' ' . $employee['last_name']) ?></h3>
        
        <?php if (isset($error)): ?>
        <div class="alert alert-danger"><?= escape($error) ?></div>
        <?php endif; ?>
        
        <div class="card mt-4">
            <div class="card-header">
                <h5 class="mb-0">Добавить выполненную работу</h5>
            </div>
            <div class="card-body">
                <form method="post">
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label for="service_name" class="form-label">Услуга *</label>
                            <input type="text" class="form-control" id="service_name" name="service_name" 
                                   required>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label for="work_date" class="form-label">Дата выполнения *</label>
                            <input type="date" class="form-control" id="work_date" name="work_date" 
                                   value="<?= date('Y-m-d') ?>" required>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label for="price" class="form-label">Стоимость (руб) *</label>
                            <input type="number" step="0.01" class="form-control" id="price" name="price" 
                                   required min="0">
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-3 mb-3">
                            <label for="duration_minutes" class="form-label">Длительность (мин) *</label>
                            <input type="number" class="form-control" id="duration_minutes" name="duration_minutes" 
                                   required min="1">
                        </div>
                        <div class="col-md-3 mb-3">
                            <label for="client_name" class="form-label">Клиент</label>
                            <input type="text" class="form-control" id="client_name" name="client_name">
                        </div>
                        <div class="col-md-3 mb-3">
                            <label for="car_model" class="form-label">Модель авто</label>
                            <input type="text" class="form-control" id="car_model" name="car_model">
                        </div>
                        <div class="col-md-3 mb-3">
                            <label for="notes" class="form-label">Примечания</label>
                            <input type="text" class="form-control" id="notes" name="notes">
                        </div>
                    </div>
                    
                    <button type="submit" name="add_work" class="btn btn-primary">Добавить</button>
                </form>
            </div>
        </div>
        
        <div class="card mt-4">
            <div class="card-header">
                <h5 class="mb-0">Список выполненных работ</h5>
            </div>
            <div class="card-body">
                <?php if (empty($works)): ?>
                <p class="text-muted">Нет выполненных работ</p>
                <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>Дата</th>
                                <th>Услуга</th>
                                <th>Стоимость</th>
                                <th>Длительность</th>
                                <th>Клиент</th>
                                <th>Авто</th>
                                <th>Заработок</th>
                                <th>Действия</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                            $totalRevenue = 0;
                            $totalEarnings = 0;
                            ?>
                            <?php foreach ($works as $work): ?>
                            <?php
                            $revenue = $work['price'];
                            $earnings = $revenue * ($employee['salary_percent'] / 100);
                            $totalRevenue += $revenue;
                            $totalEarnings += $earnings;
                            ?>
                            <tr>
                                <td><?= formatDate($work['work_date']) ?></td>
                                <td><?= escape($work['service_name']) ?></td>
                                <td><?= number_format($work['price'], 2) ?> ₽</td>
                                <td><?= $work['duration_minutes'] ?> мин</td>
                                <td><?= escape($work['client_name'] ?? '—') ?></td>
                                <td><?= escape($work['car_model'] ?? '—') ?></td>
                                <td><?= number_format($earnings, 2) ?> ₽</td>
                                <td>
                                    <form method="post" style="display: inline;">
                                        <input type="hidden" name="id" value="<?= $work['id'] ?>">
                                        <button type="submit" name="delete_work" class="btn btn-danger btn-sm"
                                                onclick="return confirm('Удалить эту запись?')">
                                            Удалить
                                        </button>
                                    </form>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                        <tfoot>
                            <tr class="table-info">
                                <td colspan="2"><strong>Итого:</strong></td>
                                <td><strong><?= number_format($totalRevenue, 2) ?> ₽</strong></td>
                                <td></td>
                                <td></td>
                                <td></td>
                                <td><strong><?= number_format($totalEarnings, 2) ?> ₽</strong></td>
                                <td></td>
                            </tr>
                        </tfoot>
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