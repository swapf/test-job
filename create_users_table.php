<?php

$dbh = new PDO('mysql:host=mysql;dbname=kbase_my', 'root', 'root');

$res = $dbh->query("
create table users ( 
    user_id int auto_increment primary key, 
    username varchar(512) null, 
    email varchar(512) null, 
    validts bigint null, 
    confirmed tinyint(1) null, 
    email_checked tinyint(1) null, 
    email_valid tinyint(1) null );

create index users_confirmed_index on users (confirmed);

create index users_validts_index on users (validts);");

var_dump($res);