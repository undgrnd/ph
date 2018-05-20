<?php

use Abraham\TwitterOAuth\TwitterOAuth;

class ModelMain extends Model
{

    public function __construct()
    {
        $this->db = new Db();
    }

    public function getListProductHunt($productName, $clientId, $clientSecret)
    {
        //создаем новый объект для запросов
        $productHunt = new ProductHunt($clientId, $clientSecret);

        preg_match('@(?<=posts\/).+@i', $productName, $matches);

        if ($matches == null) {
                return array("messageType" => "error",
                             "messageText" => "Incorrect link"
                         );
        }

        $link = $productName;

        $productName = $matches[0];

        //запрашиваем пост
        $postInfo = $productHunt->showPost($productName);

        if (isset($postInfo->{"error"}) && $postInfo->{"error"} === "not_found") {
            return array ("messagetype" => "error",
                "messageText" => "No project"
            );
        } else {
            //узнаем количество голосов
            $votesCount = $postInfo->{"post"}->{"votes_count"};

            //сколько запросов будем делать?
            $requestsCount = ceil($votesCount/50);

            //выясняем цифровой id поста
            $postId = $postInfo->{"post"}->{"id"};

            //Создаем массив под все голоса;
            $list = [];

            //запрашиваем голоса поста; несколько запросов по 50 голосов, пока не выберем все
            for ($i = 1; $i < $requestsCount + 1; $i = $i + 1) {
                $query = $productHunt->getPostVotes($postId, array("per_page"=>50,"page"=>$i));
                foreach ($query->{"votes"} as $vote) {
                    if ($vote->{"user"}->{"twitter_username"} !== null) {
                        $list[] = $vote->{"user"}->{"twitter_username"};
                    }
                }
            }

            if (count($list) < 1) {
                return array ("messagetype" => "error",
                    "messageText" => "No twitter users");
            } else {
                return array("list" => $list, "link" => $link);
            }
        }
    }

    public function getListTwitter($user, $settings)
    {
        if ($settings["oauth_access_token"] == null) {
            return array ("messagetype" => "error",
                "messageText" => "You must be authorized by Twitter.");
        }
        preg_match('@(?<=twitter.com\/).+@i', $user, $matches);

        if ($matches == null) {
                return array("messageType" => "error",
                             "messageText" => "Incorrect link"
                         );
        }

        $link = $user;

        $userName = $matches[0];

        //rate limit
        /*$url = "https://api.twitter.com/1.1/application/rate_limit_status.json";
        $getfield = "?resources=followers";
        $requestMethod = 'GET';

        $twitter = new TwitterAPIExchange($settings);
        $rateLimit = json_decode($twitter->setGetfield($getfield)
            ->buildOauth($url, $requestMethod)
            ->performRequest());

        var_dump($rateLimit);*/

        //Информация о пользователе
        $url = "https://api.twitter.com/1.1/users/show.json";
        $getfield = "?screen_name=$userName";
        $requestMethod = 'GET';

        $twitter = new TwitterAPIExchange($settings);
        $userInfo = json_decode($twitter->setGetfield($getfield)
            ->buildOauth($url, $requestMethod)
            ->performRequest());

        //узнаем количество голосов
        $followersCount = $userInfo->{"followers_count"};

        //сколько запросов будем делать?
        $requestsCount = ceil($followersCount/20);

        $list = [];
        $cursor = -1;

        $twitter2 = new TwitterAPIExchange($settings);

        for ($i = 1; $i < 2; $i = $i + 1) {
            $url2 = "https://api.twitter.com/1.1/followers/list.json";
            $getfield2 = "?screen_name=" . $userName ."&count=200&cursor=" . $cursor;
            $requestMethod2 = 'GET';
            $usersList = json_decode($twitter2->setGetfield($getfield2)
                ->buildOauth($url2, $requestMethod2)
                ->performRequest());

            foreach ($usersList->{"users"} as $user) {
                $list[] = $user->{"screen_name"};
            }
            if (isset($usersList->{"next_cursor"}) && $usersList->{"next_cursor"} !== 0) {
                $cursor = $usersList->{"next_cursor"};
            }
        }

        if (count($list) < 1) {
            return array ("messagetype" => "error",
                "messageText" => "No twitter users");
        } else {
            return array("list" => $list, "link"=>$link);
        }
    }

    public function twitterOauth()
    {
        define("CONSUMER_KEY", "vpsAdr49eMTdFKfZEtBshfrMD");
        define("CONSUMER_SECRET", "yi8mgWrciKcFu39IR1fzm1iI6P95cXqniNn4FvYY908xLgBFQc");
        define('OAUTH_CALLBACK', getenv('OAUTH_CALLBACK'));

        $connection = new TwitterOAuth(CONSUMER_KEY, CONSUMER_SECRET);

        $request_token = $connection->oauth('oauth/request_token', array('oauth_callback' => OAUTH_CALLBACK));

        $_SESSION['oauth_token'] = $request_token['oauth_token'];

        $_SESSION['oauth_token_secret'] = $request_token['oauth_token_secret'];

        $url = $connection->url('oauth/authorize', array('oauth_token' => $request_token['oauth_token']));

        header("Location: $url");
    }

    public function twitterCallback()
    {
        define("CONSUMER_KEY", "vpsAdr49eMTdFKfZEtBshfrMD");
        define("CONSUMER_SECRET", "yi8mgWrciKcFu39IR1fzm1iI6P95cXqniNn4FvYY908xLgBFQc");
        define('OAUTH_CALLBACK', getenv('OAUTH_CALLBACK'));

        $request_token = [];
        $request_token['oauth_token'] = $_SESSION['oauth_token'];
        $request_token['oauth_token_secret'] = $_SESSION['oauth_token_secret'];

        if (isset($_REQUEST['oauth_token']) && $request_token['oauth_token'] !== $_REQUEST['oauth_token']) {
            // Abort! Something is wrong.
        }

        $connection = new TwitterOAuth(CONSUMER_KEY, CONSUMER_SECRET, $request_token['oauth_token'], $request_token['oauth_token_secret']);

        $access_token = $connection->oauth("oauth/access_token", ["oauth_verifier" => $_REQUEST['oauth_verifier']]);

        $_SESSION['access_token'] = $access_token;
        unset($_SESSION["oauth_token"]);
        unset($_SESSION["oauth_token_secret"]);

        header("Location: /");
    }

    public function twitterLogout()
    {
        unset($_SESSION['access_token']);
        header("Location: /");
    }

    public function followTwitter($users, $link, $settings)
    {
        if ($settings["oauth_access_token"] == null) {
            return array ("messagetype" => "error",
                "messageText" => "You must be authorized by Twitter.");
        }
        $url = "https://api.twitter.com/1.1/friendships/create.json";
        $requestMethod = 'POST';
        $twitter = new TwitterAPIExchange($settings);
        foreach ($users as $user) {
            $postfields = array(
                'screen_name' => $user
            );
            $twitter->buildOauth($url, $requestMethod)
                    ->setPostfields($postfields)
                    ->performRequest();
        }
        $usersJsonToDb = json_encode($users);
        $date = date("Y-m-d");
        $stmt = $this->db->pdo->prepare("INSERT INTO subscriptions (username, subscription, link, date) VALUES (:username, :subscription, :link, :date)");
        $stmt->bindParam(':username', $_SESSION["access_token"]["screen_name"]);
        $stmt->bindParam(':link', $link);
        $stmt->bindParam(':subscription', $usersJsonToDb);
        $stmt->bindParam(':date', $date);
        $stmt->execute();
        return array("messageType" => "Success",
                     "messageText" => "Successful"
                 );
    }

    public function historyOfFollowing()
    {
        $stmt = $this->db->pdo->prepare('SELECT subscription, link, date FROM subscriptions WHERE username = :username');
        $stmt->execute(array('username' => $_SESSION["access_token"]["screen_name"]));
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function chooseService($sourceOfFollowing)
    {
        preg_match('@(?<=\/\/).+(?=.com)@i', $sourceOfFollowing, $matches);

        if ($matches == null) {
                return array("messageType" => "error",
                             "messageText" => "Incorrect link"
                         );
        }
        return $matches[0];
    }
}
