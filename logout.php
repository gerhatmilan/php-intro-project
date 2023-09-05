<?php
include_once('storages/user_storage.php');
include_once('storages/auth.php');

function redirect($page) {
    header("Location: ${page}");
    exit();
}

session_start();
$user_storage = new UserStorage();
$auth = new Auth($user_storage);

$auth->logout();
redirect('index.php');