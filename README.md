# test-job
PHP Developer. Test Cases

PHP
MySQL
nginx
RabbitMQ

Установка
1. Развертывание докера: docker-compose up -d
2. Немного библиотек, нужно для RabbitMQ: composer install
3. создание таблицы пользователей в БД: php create_users_table.php

Как проверить
1. Создание 100к пользователей для тестов: php createusers.php
2. Запуск слушателя очереди: php queueserver.php
3. Запуск сбора всех доходящих пользователей для рассылки и отправка их в очередь на обработку: php index.php
В этот момент они все попадут в очередь и она начнет разбираться и отправляться в работу, посмотреть это можно в админке рэбита http://127.0.0.1:15672/
4. Т.к. у нас эмуляция отправки, то вместо отправки содержание писем, будет попадать в папку ./data/emails 

Пример описания одного из писем
<EMAIL>user_16@gmail.com</EMAIL><FROM>Notification Service</FROM><TO>user_16</TO><SUBJECT>SUBSCRIPTION EXPIRATION WARNING</SUBJECT><BODY>user_16, your subscription is expiring soon</BODY>

Т.е. в целом решение получается у меня такое: каждый день, например, в 2:00 запускать крон с командой php index.php, этот скрипт будет выбирать пользователей частями по условиям и отправлять их в очередь для дальнейшей обработки. (0 2 * * *)
