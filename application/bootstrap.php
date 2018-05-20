<?php
session_start();
// подключаем файлы ядра
require_once 'core/model.php';
require_once 'core/view.php';
require_once 'core/controller.php';
require_once 'core/producthunt.php';
require_once 'core/options.php';
require_once 'core/db.php';
require_once 'vendor/autoload.php';

require_once 'core/route.php';
Route::start(); // запускаем маршрутизатор
