<?php

include 'db.php';

ini_set('error_log', 'bot.log');
ini_set('log_errors', 'On');
error_reporting(E_ALL);

// constants
define('TOKEN', '6995106650:AAF1OW7BA2RDWEywA3g0U8a1x4BRqNIp8EE');
$bot_token = '6995106650:AAF1OW7BA2RDWEywA3g0U8a1x4BRqNIp8EE';
$admins = array('5581608237', '568506833');
$chenal_id = '-1002096846403';
$key = '0JXQtNGW0Log0L/QtdC00ZbQuj8g0KfQuCDQvdC1Pw==';
$api_url = 'https://api.telegram.org/bot' . $bot_token . '/';

$input_data = json_decode(file_get_contents('php://input'), true);
//write to log
file_put_contents('log.txt', print_r($input_data, true) . PHP_EOL, FILE_APPEND);

function send_message($user_id, $message, $menu , $headers = [])
{
    if($menu == null)
    {
        $data = [
            'chat_id' => $user_id,
            'text' => $message,
        ];
    }
    else
    {
        $data = [
            'chat_id' => $user_id,
            'text' => $message,
            'reply_markup' => json_encode($menu),
        ];
    }
    $curl = curl_init();
    curl_setopt_array($curl, [
        CURLOPT_POST => 1,
        CURLOPT_HEADER => 0,
        CURLOPT_RETURNTRANSFER => 1,
        CURLOPT_URL => 'https://api.telegram.org/bot' . TOKEN . '/' . "sendMessage",
        CURLOPT_POSTFIELDS => json_encode($data),
        CURLOPT_HTTPHEADER => array_merge(array("Content-Type: application/json"), $headers)
    ]);   
    
    $result = curl_exec($curl);
    curl_close($curl);
    return (json_decode($result, 1) ? json_decode($result, 1) : $result);
}

if(isset($input_data['message'])) {
    $message_data = $input_data['message'];
    $chat_id = isset($message_data['chat']['id']) ? $message_data['chat']['id'] : null;                    
    $user_id = isset($message_data['from']['id']) ? $message_data['from']['id'] : null;                    
    $username = isset($message_data['from']['username']) ? $message_data['from']['username'] : null;              
    $first_name = isset($message_data['chat']['first_name']) ? $message_data['chat']['first_name'] : null;            
    $last_name = isset($message_data['chat']['last_name']) ? $message_data['chat']['last_name'] : null;             
    $chat_time = isset($message_data['date']) ? $message_data['date'] : null;                          
    $message = isset($message_data['text']) ? $message_data['text'] : null;                          
    $msg = $message ? mb_strtolower($message, "utf8") : null;   
} else {
    $chat_id = null;
    $user_id = null;
    $username = null;
    $first_name = null;
    $last_name = null;
    $chat_time = null;
    $message = null;
    $msg = null;
}

if(isset($input_data["callback_query"])) {
    $callback_query = $input_data["callback_query"];                           
    $data = isset($callback_query['data']) ? $callback_query['data'] : null;                              
    $message_id = isset($callback_query['message']['message_id']) ? $callback_query['message']['message_id'] : null;             
    $chat_id_in = isset($callback_query['message']['chat']['id']) ? $callback_query['message']['chat']['id'] : null;      
    $callback_query_user_id = isset($callback_query['from']['id']) ? $callback_query['from']['id'] : null; 
} else {
    $data = null;
    $message_id = null;
    $chat_id_in = null;
}
      


$admin_menu = [
    'inline_keyboard' => [
        [
            [
                'text' => '🤷‍♂️Очередь', 
                'callback_data' => 'current_queue'
            ],
            [ 
                'text' => '🙌Статус сервера', 
                'callback_data' => 'server_status'
            ],
        ],
        [
            ['text' => '🌟Статистика соц сети', 'callback_data' => 'social_stats'],
            ['text' => '🐣Статистика админы', 'callback_data' => 'admin_stats'],
        ],
    ]
];

function is_admin_standart($user_id)
{
    global $admins;
    return in_array($user_id, $admins);
}

function is_admin_calbek($user_id)
{
    global $admins;
    return in_array($user_id, $admins);
}



// check url type (instagram, youtube, tiktok)
function url_type($url)
{
    if ($url === null || $url === '') {
        return 'unknown';
    }
    
    if (strpos($url, 'instagram') !== false) {
        return 'INSTA_DEBILUS';
    } elseif (strpos($url, 'youtube') !== false) {
        return 'YOUTUBE_DEBILUS';
    } elseif (strpos($url, 'tiktok') !== false) {
        return 'TIKTOK_DEBILUS';
    } else {
        return 'unknown';
    }
}
if (is_admin_standart($user_id) || is_admin_calbek($callback_query_user_id)){

    switch ($msg){
        case '/start':
            send_message($chat_id, "Привет, админ!\n\n Як казав один філосоіф новий день новий Едік", $admin_menu);
            break;
        case '/help':
            send_message($chat_id, "Какой хелп ты шо децл?", $admin_menu);
            break;
        default:
            $url = url_type($message);
            if($url == 'unknown'){
                break;
            }
            send_message($chat_id, "Обнаружен " . $url. "\nДобавляю в очередь", $admin_menu);
            add_task_to_queue($message, $url, $user_id);
            break;
    }

    switch ($data){
        case 'current_queue':
            $queue_count = get_queue_count();
            $queue = get_queue();
            $message = "Очередь: " . $queue_count . "\n\n";
            if (!empty($queue)) {
                foreach ($queue as $task) {
                    $message .= "URL: " . $task['url'] . "\n";
                    $message .= "Тип URL: " . $task['url_type'] . "\n";
                    $message .= "Ответственный: " . $task['responsible'] . "\n";
                    $message .= "Статус: " . $task['status'] . "\n\n";
                }
            } else {
                $message .= "Очередь пуста.";
            }
            // Отправка сообщения
            send_message($chat_id_in, $message, null);
            break;        
        case 'server_status':
            $servers_status = servers_status();
            $message = "Статус серверов: \n\n";
            if (!empty($servers_status)) {
                foreach ($servers_status as $server) {
                    $message .= "Сервер: " . $server['name'] . "\n";
                    $message .= "Статус: " . $server['status'] . "\n\n";
                }
            } else {
                $message .= "Сервера в жопе.";
            }
            send_message($chat_id_in, $message, null);
            break;
        case 'social_stats':
            $get_servers_status = servers_status();
            $status = "Статус серверов: \n\n";
            $social_statistik = false; // Переменная для хранения информации о статусе социальной статистики
        
            if (!empty($get_servers_status)) {
                foreach ($get_servers_status as $server) {
                    // Проверяем статус сервера и задачи
                    if ($server['status'] === 'online' && $server['task'] === 'free') {
                        $social_statistik = true; // Устанавливаем социальную статистику в true, если хотя бы один сервер онлайн и задача свободна
                    }
                    $status .= "Сервер: " . $server['name'] . "\n";
                    $status .= "Статус: " . $server['status'] . "\n";
                    $status .= "Задача: " . $server['task'] . "\n\n";
                }
            } else {
                $status .= "Сервера в жопе.";
            }
        
            if ($social_statistik) {
                // Если есть сервер с социальной статистикой
                send_message($chat_id_in, "Сервер начал собирать статистику ожидайте сообщение.", null);
            } else {
                // Если все серверы заняты или офлайн
                send_message($chat_id_in, "Все серверы заняты или офлайн. Невозможно начать проверку статистики", null);
            }
            break;
        case 'admin_stats':
            // Выполняем запрос к базе данных для получения информации о статистике админов
            $admin_stats = get_admin_stats();
        
            // Проверяем, есть ли данные
            if ($admin_stats) {
                // Формируем сообщение со статистикой админов
                $message = "Статистика админов:\n";
                foreach ($admin_stats as $admin) {
                    $message .= "ID: " . $admin['tg_id'] . "\n";
                    $message .= "Всего постов: " . $admin['total_posts'] . "\n";
                    $message .= "ID самого популярного поста: " . $admin['id_most_popular'] . "\n\n";
                }
            } else {
                $message = "Нет доступной статистики админов.";
            }
        
            // Отправляем сообщение с информацией о статистике админов
            send_message($chat_id_in, $message, null);
            break;
        case null:
            send_message($chat_id_in, "Ошибка калбекка", null);
            break;
        default:
            send_message($chat_id_in, "Ошибка калбекка", null);
            break;
    }

}else{
    send_message($chat_id, "Ты не из правильных?", null);
}

?>
