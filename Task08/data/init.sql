-- Создание базы данных для автомойки
PRAGMA foreign_keys = ON;

-- Таблица сотрудников
CREATE TABLE employees (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    first_name TEXT NOT NULL,
    last_name TEXT NOT NULL,
    phone TEXT NOT NULL UNIQUE,
    email TEXT UNIQUE,
    hire_date DATE NOT NULL DEFAULT (date('now')),
    dismissal_date DATE,
    salary_percent REAL DEFAULT 30.0,
    is_active INTEGER DEFAULT 1 CHECK (is_active IN (0, 1))
);

-- Таблица графика работы
CREATE TABLE schedule (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    employee_id INTEGER NOT NULL,
    work_date DATE NOT NULL,
    start_time TEXT NOT NULL,
    end_time TEXT NOT NULL,
    notes TEXT,
    FOREIGN KEY (employee_id) REFERENCES employees(id) ON DELETE CASCADE,
    UNIQUE(employee_id, work_date)
);

-- Таблица выполненных работ
CREATE TABLE completed_works (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    employee_id INTEGER NOT NULL,
    service_name TEXT NOT NULL,
    work_date DATE NOT NULL,
    price REAL NOT NULL,
    duration_minutes INTEGER NOT NULL,
    client_name TEXT,
    car_model TEXT,
    notes TEXT,
    FOREIGN KEY (employee_id) REFERENCES employees(id) ON DELETE CASCADE
);

-- Вставка тестовых данных
INSERT INTO employees (first_name, last_name, phone, email, hire_date, salary_percent) VALUES
('Иван', 'Петров', '+79161234567', 'ivan@carwash.ru', '2023-01-15', 30.0),
('Анна', 'Сидорова', '+79162345678', 'anna@carwash.ru', '2023-02-20', 35.0),
('Сергей', 'Козлов', '+79163456789', 'sergey@carwash.ru', '2022-11-10', 28.0),
('Мария', 'Иванова', '+79164567890', 'maria@carwash.ru', '2024-03-05', 32.0);

INSERT INTO schedule (employee_id, work_date, start_time, end_time, notes) VALUES
(1, '2024-03-20', '09:00', '18:00', 'Стандартная смена'),
(1, '2024-03-21', '10:00', '19:00', 'Вечерняя смена'),
(2, '2024-03-20', '08:00', '17:00', 'Утренняя смена'),
(3, '2024-03-20', '12:00', '21:00', 'Дневная смена');

INSERT INTO completed_works (employee_id, service_name, work_date, price, duration_minutes, client_name, car_model) VALUES
(1, 'Полная мойка', '2024-03-19', 1500, 45, 'Александр Новиков', 'Toyota Camry'),
(1, 'Полировка', '2024-03-18', 3000, 120, 'Елена Кузнецова', 'Honda CR-V'),
(2, 'Химчистка', '2024-03-19', 2500, 90, 'Павел Орлов', 'BMW X5'),
(3, 'Экспресс-мойка', '2024-03-18', 500, 15, 'Ольга Семенова', 'Kia Rio');

-- Создание индексов
CREATE INDEX idx_employees_name ON employees(last_name, first_name);
CREATE INDEX idx_schedule_employee ON schedule(employee_id);
CREATE INDEX idx_schedule_date ON schedule(work_date);
CREATE INDEX idx_completed_works_employee ON completed_works(employee_id);
CREATE INDEX idx_completed_works_date ON completed_works(work_date);