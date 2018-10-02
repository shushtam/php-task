<?php
require __DIR__ . '/vendor/autoload.php';

$connection = new \MongoDB\Client( "mongodb://php-task.loc" );
$collection = $connection->movies->movie;


// get the HTTP method, path and body of the request
$method = $_SERVER['REQUEST_METHOD'];
$request_uri = explode('?', $_SERVER['REQUEST_URI'], 2);
$request =[$request_uri[0], $method];

switch ($request) {
    case ['/movies','GET']:
        $cursor = $collection->find();
        echo nl2br("All movies => \n");
        var_dump(gettype($cursor));
        foreach ($cursor as $document) {
            echo nl2br($document->Name.' '.$document->Description.' '.$document->IsAdult."\n");
        }
        break;
    // About page
    case '/about':
        var_dump('about');
        break;
    // Everything else
    default:
        var_dump('default');
/*        header('HTTP/1.0 404 Not Found');
        require '../views/404.php';*/
        break;
}

exit;

