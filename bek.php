<?php
include 'db.php';


// Проверяем, был ли отправлен GET запрос
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    // Проверяем наличие ключа 'ping' в GET параметрах
    if (isset($_GET['ping'])) {
        $eveible_tasks = get_task_for_server();
        if ($eveible_tasks != null) {
            echo json_encode($eveible_tasks);
        } else {
            //json status:pong
            echo json_encode(array('status' => 'pong'));
        }
    } else if(isset($_GET['status'])) {
        $status = $_GET['status'];
        $url = $_GET['url'];
        if($status == "TG_DONE"){
            $sql = "UPDATE queue SET status = 'Load_to_facebook' WHERE url = '$url'";
            $result = $conn->query($sql);
            if($result){
                echo json_encode(array('status' => 'ok'));
            }else {
                echo json_encode(array('status' => 'error'));
            }

            // get responsible tg id from queue end send to tg message
            $sql = "SELECT responsible FROM queue WHERE url = '$url'";
            $result = $conn->query($sql);
            $result->fetch_row()[0];
        }
    }else {
        echo 'Ключ "ping" не найден в запросе';
    }
} else {
    echo 'Хто ти воїн? Я тебе не знаю!';
}


?>
