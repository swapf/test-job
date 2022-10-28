<?php

$dbh = new PDO('mysql:host=mysql;dbname=kbase_my', 'root', 'root');

for($i=1; $i<=100000; $i++) {

    $email_checked = rand(0, 1);
    $email_valid = 0;
    if ($email_checked) {
        $email_valid = rand(0, 1);
    }

    $data = [
        'username' => 'user_'.$i,
        'email' => 'user_'.$i.'@gmail.com',
        'validts' => time()+(60*60*24*rand(1,20)),
        'confirmed' => rand(0,1),
        'email_checked' => $email_checked,
        'email_valid' => $email_valid
    ];

    $stmt = $dbh->prepare("INSERT INTO users (username, email, validts, confirmed, email_checked, email_valid)
        VALUES (:username, :email, :validts, :confirmed, :email_checked, :email_valid)");

    $stmt->execute($data);

    echo "added ".$i.PHP_EOL;
}