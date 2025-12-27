<?php
require_once '../includes/config.php';
require_once '../includes/database.php';
require_once '../includes/functions.php';

$db = getDB();
$errors = [];
$success = false;

$id = get('id');
if (!$id) {
    redirect('index.php');
}

// Получение данных мастера
$stmt = $db->prepare("SELECT * FROM employees WHERE id = ?");
$stmt->execute([$id]);
$employee = $stmt->fetch();

if (!$employee) {
    redirect('index.php');
}

if (isPost()) {
    $firstName = post('first_name');
    $lastName = post('last_name');
    $phone = post('phone');
    $email = post('email');
    $hireDate = post('hire_date');
    $salaryPercent = post('salary_percent');
    $isActive = post('is_active', 0);
    $dismissalDate = post('dismissal_date');
    
    // Валидация
    if (($error = validateRequired('Имя', $firstName)) !== null) $errors[] = $error;
    if (($error = validateRequired('Фамилия', $lastName)) !== null) $errors[] = $error;
    if (($error = validateRequired('Телефон', $phone)) !== null) $errors[] = $error;
    if (($error = validatePhone($phone)) !== null) $errors[] = $error;
    if (!empty($email) && ($error = validateEmail($email)) !== null) $errors[] = $error;
    
    if (empty($errors)) {
        try {
            $stmt = $db->prepare("
                UPDATE employees SET 
                first_name = ?, last_name = ?, phone = ?, email = ?, 
                hire_date = ?, salary_percent = ?, is_active = ?, dismissal_date = ?
                WHERE id = ?
            ");
            $stmt->execute([
                $firstName, $lastName, $phone, $email ?: null,
                $hireDate, $salaryPercent, $isActive, $dismissalDate ?: null,
                $id
            ]);
            $success = true;
        } catch (PDOException $e) {
            $errors[] = "Ошибка при обновлении: " . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Редактировать мастера</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-4">
        <h1>Редактировать мастера</h1>
        
        <?php if ($success): ?>
        <div class="alert alert-success">
            Данные мастера успешно обновлены!
        </div>
        <?php endif; ?>
        
        <?php if (!empty($errors)): ?>
        <div class="alert alert-danger">
            <ul class="mb-0">
                <?php foreach ($errors as $error): ?>
                <li><?= escape($error) ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
        <?php endif; ?>
        
        <form method="post" class="mt-3">
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label for="first_name" class="form-label">Имя *</label>
                    <input type="text" class="form-control" id="first_name" name="first_name" 
                           value="<?= escape(post('first_name', $employee['first_name'])) ?>" required>
                </div>
                <div class="col-md-6 mb-3">
                    <label for="last_name" class="form-label">Фамилия *</label>
                    <input type="text" class="form-control" id="last_name" name="last_name" 
                           value="<?= escape(post('last_name', $employee['last_name'])) ?>" required>
                </div>
            </div>
            
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label for="phone" class="form-label">Телефон *</label>
                    <input type="tel" class="form-control" id="phone" name="phone" 
                           value="<?= escape(post('phone', $employee['phone'])) ?>" required>
                </div>
                <div class="col-md-6 mb-3">
                    <label for="email" class="form-label">Email</label>
                    <input type="email" class="form-control" id="email" name="email" 
                           value="<?= escape(post('email', $employee['email'])) ?>">
                </div>
            </div>
            
            <div class="row">
                <div class="col-md-4 mb-3">
                    <label for="hire_date" class="form-label">Дата приема *</label>
                    <input type="date" class="form-control" id="hire_date" name="hire_date" 
                           value="<?= escape(post('hire_date', $employee['hire_date'])) ?>" required>
                </div>
                <div class="col-md-4 mb-3">
                    <label for="salary_percent" class="form-label">Процент (%)</label>
                    <input type="number" step="0.1" class="form-control" id="salary_percent" name="salary_percent" 
                           value="<?= escape(post('salary_percent', $employee['salary_percent'])) ?>" required>
                </div>
                <div class="col-md-4 mb-3">
                    <label for="dismissal_date" class="form-label">Дата увольнения</label>
                    <input type="date" class="form-control" id="dismissal_date" name="dismissal_date" 
                           value="<?= escape(post('dismissal_date', $employee['dismissal_date'])) ?>">
                </div>
            </div>
            
            <div class="mb-3">
                <div class="form-check">
                    <input type="checkbox" class="form-check-input" id="is_active" name="is_active" value="1"
                           <?= (post('is_active', $employee['is_active']) ? 'checked' : '') ?>>
                    <label class="form-check-label" for="is_active">Работает</label>
                </div>
            </div>
            
            <div class="mt-3">
                <button type="submit" class="btn btn-primary">Сохранить</button>
                <a href="index.php" class="btn btn-secondary">Отмена</a>
            </div>
        </form>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>