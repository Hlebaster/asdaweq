<?php
// Подключение к базе данных MySQL
$db_host = 'localhost'; // Хост базы данных MySQL
$db_user = 'u566105613_memege'; // Пользователь базы данных MySQL
$db_pass = '32123212gG'; // Пароль базы данных MySQL
$db_name = 'u566105613_memege'; // Имя базы данных MySQL


$conn = new mysqli($db_host, $db_user, $db_pass, $db_name);
if ($conn->connect_error) {
    die('Ошибка подключения (' . $conn->connect_errno . ') ' . $conn->connect_error);
}

// Функция для добавления задачи в очередь
function add_task_to_queue($url, $url_type, $responsible)
{
    global $conn;
    $sql = "INSERT INTO queue (url, url_type, responsible, status) VALUES ('$url', '$url_type', '$responsible', 'NEW')";
    $result = $conn->query($sql);
    return $result;
}

// Функция для получения очереди
function get_queue(){
    global $conn;
    $sql = "SELECT * FROM queue WHERE status = 'NEW'";
    $result = $conn->query($sql);
    if ($result->num_rows > 0) {
        return $result->fetch_all(MYSQLI_ASSOC);
    } else {
        return array(); // Возвращаем пустой массив, если результат пустой
    }
}

// Функция для получения количества задач в очереди
function get_queue_count(){
    global $conn;
    $sql = "SELECT COUNT(*) FROM queue WHERE status = 'NEW'";
    $result = $conn->query($sql);
    return $result->fetch_row()[0];
}

function servers_status(){
    global $conn;
    $sql = "SELECT * FROM servers_status";
    $result = $conn->query($sql);
    if ($result->num_rows > 0) {
        return $result->fetch_all(MYSQLI_ASSOC);
    } else {
        return array(); // Возвращаем пустой массив, если результат пустой
    }
}

// Функция для выполнения запроса к базе данных и получения статистики админов
function get_admin_stats() {
    global $conn;
    $admin_stats = array();

    // Выполняем запрос к базе данных
    $sql = "SELECT tg_id, total_posts, id_most_popular FROM admin_stats";
    $result = $conn->query($sql);

    // Проверяем, есть ли результат
    if ($result && $result->num_rows > 0) {
        // Обрабатываем результат
        while ($row = $result->fetch_assoc()) {
            $admin_stats[] = $row;
        }
    }

    // Возвращаем статистику админов
    return $admin_stats;
}

function get_task_for_server(){
    global $conn;
    $sql = "SELECT * FROM queue WHERE status = 'NEW' LIMIT 1";
    $result = $conn->query($sql);
    if ($result->num_rows > 0) {
        $task = $result->fetch_assoc();
        // $task_id = $task['id'];
        // $sql = "UPDATE queue SET status = 'IN_PROGRESS' WHERE id = $task_id";
        // $conn->query($sql);
        return $task;
    } else {
        return null;
    }
}