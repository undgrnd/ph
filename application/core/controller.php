<?php

class Controller
{

    public $model;
    public $view;
    public $db;

    public function __construct()
    {
        $this->view = new View();
        $this->db = new Db();
    }

    // действие (action), вызываемое по умолчанию
    public function actionIndex()
    {
        // todo
    }
}
