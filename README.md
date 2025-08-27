# PulseWatch

**PulseWatch** — это учебный проект-мониторинг доступности сайтов и сервисов  
(мини-аналог UptimeRobot / Pingdom).

## Возможности
- Регистрация и авторизация пользователей
- Создание, редактирование и удаление мониторов
- Поддержка типов мониторинга: `HTTP`, `TCP`, `PING`
- Настройка интервала и таймаута проверки
- Включение / выключение мониторинга
- Отображение последних проверок и график времени ответа
- Расчёт uptime за последние 24 часа
- Простое API: `/api/monitor/results`
## Установка
1. Клонируйте репозиторий:
   ```bash
   git clone https://github.com/yourname/pulsewatch.git
   cd pulsewatch
   ```
   
2. Установите зависимости:
   ```bash
    composer install
   ```
   
3. Настройте окружение:
    ```bash
    cp .env.example .env
   ```
Укажите параметры подключения к базе данных. 

4. Создайте таблицы (пример):
    ```sql
    CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(255) UNIQUE NOT NULL,
    password_hash VARCHAR(255) NOT NULL
    );
    ```

5. Запустите локальный сервер:

    ```bash
    php -S localhost:8000 -t public
    ```

6. Запускайте воркер для проверки:

    ```bash
    php bin/worker.php
   ```

## Скриншоты

- Список мониторов
- Детальная страница с графиком
- Форма редактирования

## Стек

- PHP 8+
- Composer (PSR-4 автозагрузка)
- SQLite/MySQL (через PDO)
- Chart.js (для графиков)

## Идеи для доработки
- Email/Telegram уведомления о падениях
- Docker для запуска
- Юнит-тесты (PHPUnit)
- Более красивый интерфейс (CSS/фреймворки)