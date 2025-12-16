DROP TABLE IF EXISTS WorkRecords;
DROP TABLE IF EXISTS Appointments;
DROP TABLE IF EXISTS SalaryRates;
DROP TABLE IF EXISTS Services;
DROP TABLE IF EXISTS CarCategories;
DROP TABLE IF EXISTS Boxes;
DROP TABLE IF EXISTS Employees;

CREATE TABLE Employees (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    name TEXT NOT NULL,
    position TEXT NOT NULL CHECK(position IN ('Мастер', 'Администратор', 'Менеджер', 'Директор')),
    hire_date DATE NOT NULL DEFAULT (date('now')),
    dismissal_date DATE,
    is_active INTEGER NOT NULL DEFAULT 1 CHECK(is_active IN (0, 1)),
    phone TEXT UNIQUE,
    email TEXT,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CHECK(dismissal_date IS NULL OR dismissal_date >= hire_date),
    CHECK(phone IS NOT NULL OR email IS NOT NULL)
);

CREATE TABLE CarCategories (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    name TEXT NOT NULL UNIQUE,
    description TEXT,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE Services (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    name TEXT NOT NULL,
    car_category_id INTEGER NOT NULL,
    duration_minutes INTEGER NOT NULL CHECK(duration_minutes > 0),
    price DECIMAL(10, 2) NOT NULL CHECK(price >= 0),
    description TEXT,
    is_active INTEGER NOT NULL DEFAULT 1 CHECK(is_active IN (0, 1)),
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (car_category_id) REFERENCES CarCategories(id) ON DELETE RESTRICT,
    UNIQUE(name, car_category_id)
);

CREATE TABLE Boxes (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    number INTEGER NOT NULL UNIQUE CHECK(number > 0),
    is_active INTEGER NOT NULL DEFAULT 1 CHECK(is_active IN (0, 1)),
    description TEXT,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE SalaryRates (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    employee_id INTEGER NOT NULL,
    rate_percent DECIMAL(5, 2) NOT NULL CHECK(rate_percent > 0 AND rate_percent <= 100),
    effective_from DATE NOT NULL DEFAULT (date('now')),
    effective_to DATE,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (employee_id) REFERENCES Employees(id) ON DELETE RESTRICT,
    CHECK(effective_to IS NULL OR effective_to >= effective_from)
);

CREATE TABLE Appointments (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    employee_id INTEGER NOT NULL,
    box_id INTEGER NOT NULL,
    service_id INTEGER NOT NULL,
    appointment_date DATE NOT NULL,
    appointment_time TIME NOT NULL,
    status TEXT NOT NULL DEFAULT 'Запланировано' CHECK(status IN ('Запланировано', 'Выполнено', 'Отменено', 'Неявка')),
    client_name TEXT NOT NULL,
    client_phone TEXT,
    notes TEXT,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (employee_id) REFERENCES Employees(id) ON DELETE RESTRICT,
    FOREIGN KEY (box_id) REFERENCES Boxes(id) ON DELETE RESTRICT,
    FOREIGN KEY (service_id) REFERENCES Services(id) ON DELETE RESTRICT
);

CREATE TABLE WorkRecords (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    appointment_id INTEGER,
    employee_id INTEGER NOT NULL,
    box_id INTEGER NOT NULL,
    service_id INTEGER NOT NULL,
    work_date DATE NOT NULL DEFAULT (date('now')),
    work_time TIME NOT NULL DEFAULT (time('now')),
    actual_price DECIMAL(10, 2) NOT NULL CHECK(actual_price >= 0),
    completed_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    notes TEXT,
    FOREIGN KEY (appointment_id) REFERENCES Appointments(id) ON DELETE SET NULL,
    FOREIGN KEY (employee_id) REFERENCES Employees(id) ON DELETE RESTRICT,
    FOREIGN KEY (box_id) REFERENCES Boxes(id) ON DELETE RESTRICT,
    FOREIGN KEY (service_id) REFERENCES Services(id) ON DELETE RESTRICT
);

CREATE INDEX idx_employees_active ON Employees(is_active);
CREATE INDEX idx_employees_position ON Employees(position);
CREATE INDEX idx_appointments_date ON Appointments(appointment_date, appointment_time);
CREATE INDEX idx_appointments_status ON Appointments(status);
CREATE INDEX idx_appointments_employee ON Appointments(employee_id);
CREATE INDEX idx_appointments_box ON Appointments(box_id);
CREATE INDEX idx_workrecords_date ON WorkRecords(work_date);
CREATE INDEX idx_workrecords_employee ON WorkRecords(employee_id);
CREATE INDEX idx_workrecords_service ON WorkRecords(service_id);
CREATE INDEX idx_salaryrates_employee ON SalaryRates(employee_id);
CREATE INDEX idx_salaryrates_dates ON SalaryRates(effective_from, effective_to);

BEGIN TRANSACTION;

INSERT INTO Employees (name, position, hire_date, phone, email) VALUES ('Кузнецов Андрей Викторович', 'Мастер', '2023-03-12', '+7-901-555-44-33', 'kuznetsov@carwash.ru');
INSERT INTO Employees (name, position, hire_date, phone, email) VALUES ('Соколова Мария Игоревна', 'Мастер', '2023-05-18', '+7-902-666-77-88', 'sokolova@carwash.ru');
INSERT INTO Employees (name, position, hire_date, phone, email) VALUES ('Лебедев Сергей Дмитриевич', 'Мастер', '2023-04-22', '+7-903-888-99-00', 'lebedev@carwash.ru');
INSERT INTO Employees (name, position, hire_date, phone, email) VALUES ('Попова Ольга Николаевна', 'Администратор', '2023-02-14', '+7-904-111-22-33', 'popova@carwash.ru');
INSERT INTO Employees (name, position, hire_date, phone, email) VALUES ('Морозов Иван Петрович', 'Менеджер', '2023-06-10', '+7-905-444-55-66', 'morozov@carwash.ru');
INSERT INTO Employees (name, position, hire_date, phone, email) VALUES ('Волкова Анна Александровна', 'Мастер', '2023-07-05', '+7-906-777-88-99', 'volkova@carwash.ru');
INSERT INTO Employees (name, position, hire_date, dismissal_date, is_active, phone, email) VALUES ('Ковалев Павел Сергеевич', 'Мастер', '2022-12-10', '2024-07-15', 0, '+7-907-222-33-44', 'kovalev@carwash.ru');

INSERT INTO CarCategories (name, description) VALUES ('Седаны', 'Легковые автомобили класса седан');
INSERT INTO CarCategories (name, description) VALUES ('Внедорожники', 'Крупные внедорожники и SUV');
INSERT INTO CarCategories (name, description) VALUES ('Минивэны', 'Семейные минивэны');
INSERT INTO CarCategories (name, description) VALUES ('Фургоны', 'Грузопассажирские фургоны');
INSERT INTO CarCategories (name, description) VALUES ('Квадроциклы', 'Квадроциклы и багги');

INSERT INTO Services (name, car_category_id, duration_minutes, price, description) VALUES ('Экспресс мойка', 1, 10, 400.00, 'Быстрая наружная мойка');
INSERT INTO Services (name, car_category_id, duration_minutes, price, description) VALUES ('Стандарт мойка', 1, 20, 700.00, 'Мойка с сушкой');
INSERT INTO Services (name, car_category_id, duration_minutes, price, description) VALUES ('Премиум мойка', 1, 50, 1700.00, 'Комплексная мойка внутри и снаружи');
INSERT INTO Services (name, car_category_id, duration_minutes, price, description) VALUES ('Защитная полировка', 1, 110, 4500.00, 'Полировка с нанесением защиты');
INSERT INTO Services (name, car_category_id, duration_minutes, price, description) VALUES ('Глубокая чистка', 1, 100, 3800.00, 'Тщательная чистка салона');
INSERT INTO Services (name, car_category_id, duration_minutes, price, description) VALUES ('Экспресс мойка', 2, 25, 750.00, 'Быстрая наружная мойка');
INSERT INTO Services (name, car_category_id, duration_minutes, price, description) VALUES ('Стандарт мойка', 2, 35, 1200.00, 'Мойка с сушкой');
INSERT INTO Services (name, car_category_id, duration_minutes, price, description) VALUES ('Премиум мойка', 2, 65, 2200.00, 'Комплексная мойка внутри и снаружи');
INSERT INTO Services (name, car_category_id, duration_minutes, price, description) VALUES ('Защитная полировка', 2, 140, 6000.00, 'Полировка с нанесением защиты');
INSERT INTO Services (name, car_category_id, duration_minutes, price, description) VALUES ('Экспресс мойка', 3, 30, 950.00, 'Быстрая наружная мойка');
INSERT INTO Services (name, car_category_id, duration_minutes, price, description) VALUES ('Стандарт мойка', 3, 45, 1550.00, 'Мойка с сушкой');
INSERT INTO Services (name, car_category_id, duration_minutes, price, description) VALUES ('Премиум мойка', 3, 80, 2700.00, 'Комплексная мойка внутри и снаружи');
INSERT INTO Services (name, car_category_id, duration_minutes, price, description) VALUES ('Экспресс мойка', 4, 45, 1600.00, 'Быстрая наружная мойка');
INSERT INTO Services (name, car_category_id, duration_minutes, price, description) VALUES ('Стандарт мойка', 4, 65, 2400.00, 'Мойка с сушкой');
INSERT INTO Services (name, car_category_id, duration_minutes, price, description) VALUES ('Премиум мойка', 4, 130, 4200.00, 'Комплексная мойка внутри и снаружи');
INSERT INTO Services (name, car_category_id, duration_minutes, price, description) VALUES ('Базовый уход', 5, 15, 350.00, 'Мойка мототехники');
INSERT INTO Services (name, car_category_id, duration_minutes, price, description) VALUES ('Полный уход', 5, 35, 1300.00, 'Мойка с полировкой и смазкой');

INSERT INTO Boxes (number, description) VALUES (1, 'Бокс для седанов');
INSERT INTO Boxes (number, description) VALUES (2, 'Бокс для внедорожников');
INSERT INTO Boxes (number, description) VALUES (3, 'Бокс для минивэнов');
INSERT INTO Boxes (number, description) VALUES (4, 'Универсальный бокс');
INSERT INTO Boxes (number, description) VALUES (5, 'Бокс для мототехники');

INSERT INTO SalaryRates (employee_id, rate_percent, effective_from) VALUES (1, 26.50, '2023-03-12');
INSERT INTO SalaryRates (employee_id, rate_percent, effective_from) VALUES (2, 29.00, '2023-05-18');
INSERT INTO SalaryRates (employee_id, rate_percent, effective_from) VALUES (3, 27.50, '2023-04-22');
INSERT INTO SalaryRates (employee_id, rate_percent, effective_from) VALUES (4, 25.00, '2022-12-10');
INSERT INTO SalaryRates (employee_id, rate_percent, effective_from) VALUES (7, 28.50, '2023-07-05');

INSERT INTO Appointments (employee_id, box_id, service_id, appointment_date, appointment_time, client_name, client_phone, status) VALUES (1, 1, 1, date('now', '+2 days'), '09:30', 'Новиков Д.С.', '+7-912-123-45-67', 'Запланировано');
INSERT INTO Appointments (employee_id, box_id, service_id, appointment_date, appointment_time, client_name, client_phone, status) VALUES (1, 1, 2, date('now', '+2 days'), '11:15', 'Григорьев М.К.', '+7-912-234-56-78', 'Запланировано');
INSERT INTO Appointments (employee_id, box_id, service_id, appointment_date, appointment_time, client_name, client_phone, status) VALUES (2, 2, 6, date('now', '+2 days'), '10:45', 'Тихонов В.Л.', '+7-912-345-67-89', 'Запланировано');
INSERT INTO Appointments (employee_id, box_id, service_id, appointment_date, appointment_time, client_name, client_phone, status) VALUES (3, 1, 3, date('now', '+2 days'), '13:30', 'Федоров А.М.', '+7-912-456-78-90', 'Запланировано');
INSERT INTO Appointments (employee_id, box_id, service_id, appointment_date, appointment_time, client_name, client_phone, status) VALUES (7, 4, 1, date('now', '+3 days'), '08:45', 'Михайлов С.Н.', '+7-912-567-89-01', 'Запланировано');
INSERT INTO Appointments (employee_id, box_id, service_id, appointment_date, appointment_time, client_name, client_phone, status) VALUES (1, 2, 5, date('now', '+3 days'), '14:20', 'Алексеев П.О.', '+7-912-678-90-12', 'Запланировано');
INSERT INTO Appointments (employee_id, box_id, service_id, appointment_date, appointment_time, client_name, client_phone, status) VALUES (2, 1, 2, date('now', '-2 days'), '09:00', 'Семенов Р.Т.', '+7-912-789-01-23', 'Выполнено');
INSERT INTO Appointments (employee_id, box_id, service_id, appointment_date, appointment_time, client_name, client_phone, status) VALUES (3, 2, 1, date('now', '-2 days'), '12:15', 'Дмитриев У.Ф.', '+7-912-890-12-34', 'Выполнено');

INSERT INTO WorkRecords (appointment_id, employee_id, box_id, service_id, work_date, work_time, actual_price, notes) VALUES (7, 1, 1, 2, date('now', '-2 days'), '09:00', 700.00, 'Клиент приехал раньше');
INSERT INTO WorkRecords (appointment_id, employee_id, box_id, service_id, work_date, work_time, actual_price, notes) VALUES (8, 3, 2, 1, date('now', '-2 days'), '12:15', 400.00, 'Быстрая работа');
INSERT INTO WorkRecords (appointment_id, employee_id, box_id, service_id, work_date, work_time, actual_price, notes) VALUES (NULL, 2, 2, 6, date('now', '-3 days'), '15:30', 1200.00, 'Работа по приезду');
INSERT INTO WorkRecords (appointment_id, employee_id, box_id, service_id, work_date, work_time, actual_price, notes) VALUES (NULL, 1, 1, 3, date('now', '-4 days'), '17:00', 1700.00, 'Премиум мойка седана');
INSERT INTO WorkRecords (appointment_id, employee_id, box_id, service_id, work_date, work_time, actual_price, notes) VALUES (NULL, 7, 4, 1, date('now', '-5 days'), '11:30', 400.00, 'Пробная работа');
INSERT INTO WorkRecords (appointment_id, employee_id, box_id, service_id, work_date, work_time, actual_price, notes) VALUES (NULL, 2, 2, 7, date('now', '-6 days'), '09:45', 1200.00, 'Мойка внедорожника');
INSERT INTO WorkRecords (appointment_id, employee_id, box_id, service_id, work_date, work_time, actual_price, notes) VALUES (NULL, 3, 1, 5, date('now', '-7 days'), '14:20', 3800.00, 'Глубокая чистка');
INSERT INTO WorkRecords (appointment_id, employee_id, box_id, service_id, work_date, work_time, actual_price, notes) VALUES (NULL, 1, 1, 1, date('now', '-8 days'), '08:30', 400.00, 'Первая мойка дня');
INSERT INTO WorkRecords (appointment_id, employee_id, box_id, service_id, work_date, work_time, actual_price, notes) VALUES (NULL, 2, 2, 6, date('now', '-9 days'), '16:00', 750.00, 'Экспресс мойка');
INSERT INTO WorkRecords (appointment_id, employee_id, box_id, service_id, work_date, work_time, actual_price, notes) VALUES (NULL, 7, 4, 2, date('now', '-10 days'), '10:15', 700.00, 'Стандартная мойка');