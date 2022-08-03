<?php

use Phalcon\Loader;
use Phalcon\Mvc\Micro;
use Phalcon\Di\FactoryDefault;
use Phalcon\Http\Request;

//allowing cross-origin-requests
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST');
header("Access-Control-Allow-Headers: X-Requested-With");

$loader = new Loader();

//requiring autoload file
require_once('./vendor/autoload.php');

//registering namespaces
$loader->registerNamespaces(
    [
        'Api\Handlers' => './handlers'
    ]
);

$loader->register();
$container = new FactoryDefault();

//setting up mongo db
$container->set(
    'mongo',
    function () {
        $mongo =  new MongoDB\Client('mongodb://mongo', array('username' => 'root', "password" => 'password123'));
        return $mongo->AuthorCafe;
    },
    true
);

$secure = new \Api\Handlers\Secure();
$feedback = new \Api\Handlers\Feedback();

$app = new Micro($container);

//using before middleware to performs checks
$app->before(function () use ($app) {
    $request = new Request();

    $check = explode('/', $request->getURI());
    //checking if the user is trying to get access token
    //and allowing the user to hit API without token if he/she is
    if ($check[1] == 'getToken') {
        return;
    }

    //checking for bearer token
    $bearer = $request->get('bearer');
    if ($bearer) {
        $secure = new \Api\Handlers\Secure();
        $secure->verification($bearer);
    } else {
        $error = ["error" => "Access Token not provided"];
        print_r(json_encode($error));
        die;
    }
});

//Defining routes
$app->get(
    '/feedbacks',
    [
        $feedback,
        'getFeedbacks'
    ]
);
$app->post(
    '/feedbacks',
    [
        $feedback,
        'submitFeedback'
    ]
);
$app->get(
    '/getToken/{email}',
    [
        $secure,
        'getToken'
    ]
);

//if endpoint is not valid
$app->notFound(function () use ($app) {
    die('Invalid API end point');
});

//handling the request
$_SERVER["REQUEST_URI"] = str_replace("/api", "", $_SERVER["REQUEST_URI"]);
$app->handle(
    $_SERVER["REQUEST_URI"]
);
