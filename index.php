<?php
require_once __DIR__ . '/vendor/autoload.php';

use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;


$dbh = new PDO('mysql:host=mysql;dbname=kbase_my', 'root', 'root');

$r = new AMQPStreamConnection('rabbitmq', '5672', 'rabbituser', 'password', 'rabbit-vh');

$channel = $r->channel();
$channel->queue_declare('user_notification', false, false, false, false);

$start_user_id = 0;
$stop_user_id = 1000;
$limit = 1000;

while (true) {
    $users = $dbh->query("select res.* from (
                               select * from users as u
                               where u.user_id > {$start_user_id} and u.user_id <= {$stop_user_id}) as res
                               where res.validts > UNIX_TIMESTAMP(NOW()) 
                               and res.validts < (UNIX_TIMESTAMP(NOW())+60*60*24*3)
                               and res.confirmed = 1;");

    if(!$users->rowCount()) {
        break;
    }

    foreach ($users as $u) {
        echo $u['user_id'] . $u['email'] . PHP_EOL;
        $msg = new AMQPMessage(json_encode($u));
        $channel->basic_publish($msg, '', 'user_notification');
    }

    $start_user_id = $start_user_id + $limit;
    $stop_user_id = $stop_user_id + $limit;
}

$channel->close();
$r->close();
