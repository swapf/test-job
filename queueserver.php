<?php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/vendor/autoload.php';

use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;

$dbh = new PDO('mysql:host=mysql;dbname=kbase_my', 'root', 'root');

function prepare_for_sending($jsonData)
{
    $template = "{username}, your subscription is expiring soon";
    $message_for_user = str_replace("{username}", $jsonData->username, $template);
    send_email($jsonData->email,
        'Notification Service',
        $jsonData->username,
        'SUBSCRIPTION EXPIRATION WARNING',
        $message_for_user);
}

function imitate_send_email(String $email, String $from, String $to, String $subj, String $body)
{
    sleep(rand(1, 10));
    file_put_contents(__DIR__ . '/data/emails/' . $email,
        "<EMAIL>" . $email . "</EMAIL>" .
        "<FROM>" . $from . "</FROM>" .
        "<TO>" . $to . "</TO>" .
        "<SUBJECT>" . $subj . "</SUBJECT>" .
        "<BODY>" . $body . "</BODY>");

    echo "Отправка email для ".$email.PHP_EOL;
}

function imitate_check_email(String $email)
{
    sleep(rand(1, 60));
    return rand(0, 1);
}

function send_email(String $email, String $from, String $to, String $subj, String $body)
{
    if (ENV == 'PROD') {
        //реальная логика отправки email для прод-окружения
    } else {
        imitate_send_email($email, $from, $to, $subj, $body);
    }
}

function check_email($email)
{
    $is_valid = 0;
    if (ENV == 'PROD') {
        //реальная логика проверки email для прод-окружения
    } else {
        $is_valid = imitate_check_email($email);
    }
    return $is_valid;
}

$r = new AMQPStreamConnection('rabbitmq', '5672', 'rabbituser', 'password', 'rabbit-vh');
$channel = $r->channel();
$channel->queue_declare('user_notification', false, false, false, false);
echo " Ждем сообщений..." . PHP_EOL;

/*
 * В этот момент мы будем получать только пользователей, у которых подтвержен email при регистрации
 * Нам нужно будет еще проверить валидный он или нет и при этой проверке также установить - что email чекнут
 * Если email чекнут и он невалидный - нет смысла его дальше проверять, проверка эта платная
*/
$callback = function ($msg) use ($dbh) {
    $jsonData = json_decode($msg->body);
    echo ' Обработка для: ' . $jsonData->email . PHP_EOL;

    if ($jsonData->email_checked) {
        if ($jsonData->email_valid) {
            //чекнут и валиден, можно готовиться к отправе email
            echo "Email уже был чекнут, он валиден, готовимся отправке" . PHP_EOL;
            prepare_for_sending($jsonData);
        } else {
            echo "Email уже был чекнут, он не валиден" . PHP_EOL;
        }
    } else {
        //тот случай, если не проверялся, значит нужна проверка
        echo "Email еще не был чекнут, проверям" . PHP_EOL;
        $is_valid = check_email($jsonData->email);

        echo ($is_valid == 1)?"Валиден" . PHP_EOL:"Не валиден" . PHP_EOL;

        //обновим инфу в БД, что email чекнут и установим статус - валиден или нет
        $data = [
            'user_id' => $jsonData->user_id,
            'email_checked' => 1,
            'email_valid' => $is_valid
        ];
        $sql = "UPDATE users SET email_checked=:email_checked, email_valid=:email_valid WHERE user_id=:user_id";
        $stmt = $dbh->prepare($sql);
        $stmt->execute($data);

        if ($is_valid) {
            //чекнут и валиден, можно готовиться к отправе email
            echo "Email только был чекнут, он валиден, готовимся отправке" . PHP_EOL;
            prepare_for_sending($jsonData);
        }
    }
};

$channel->basic_consume('user_notification', '', false, true, false, false, $callback);
while (count($channel->callbacks)) {
    $channel->wait();
}
$channel->close();
$r->close();

