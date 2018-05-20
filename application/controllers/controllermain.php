<?php

class ControllerMain extends Controller
{

    public function __construct()
    {
        $this->model = new ModelMain();
        $this->view = new View();
        $this->options = new Options();
    }

    public function actionindex()
    {
        if (isset($_SESSION["access_token"])) {
            $this->options->TWISettings["oauth_access_token"] = $_SESSION["access_token"]["oauth_token"];
            $this->options->TWISettings["oauth_access_token_secret"] = $_SESSION["access_token"]["oauth_token_secret"];
        }
        //Подписка на пользователей
        if (isset($_POST["action"]) && $_POST["action"] === "Subscribe for all") {
            $users = $_SESSION["list"];
            $link = $_SESSION["link"];
            $data = $this->model->followTwitter($users, $link, $this->options->TWISettings);
            $this->view->generate('mainview.php', 'templateview.php', $data);
        } elseif (isset($_POST["action"]) && $_POST["action"] === "View") {
            if ($this->model->chooseService($_POST["productName"]) == "twitter") {
                $data = $this->model->getListTwitter($_POST["productName"], $this->options->TWISettings);
            } elseif ($this->model->chooseService($_POST["productName"]) == "www.producthunt") {
                $data = $this->model->getListProductHunt($_POST["productName"], $this->options->PHAppClientId, $this->options->PHAppClientSecret);
            }
            if (isset($data["list"])) {
                $_SESSION["list"] = $data["list"];
                $_SESSION["link"] = $data["link"];
            }
            $this->view->generate('mainview.php', 'templateview.php', $data);
        } else {
            $this->view->generate('mainview.php', 'templateview.php');
        }
    }

    public function actiontwitteroauth()
    {
        $this->model->twitterOauth();
    }

    public function actioncallback()
    {
        $this->model->twitterCallback();
    }

    public function actionlogout()
    {
        $this->model->twitterLogout();
    }
    public function actionhistory()
    {
        $data = $this->model->historyOfFollowing();
        $this->view->generate('mainhistoryview.php', 'templateview.php', $data);
    }
}
