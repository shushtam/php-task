<?php
require __DIR__ . '/vendor/autoload.php';
require __DIR__ . '/classes/MovieController.php';


// get the HTTP method, path and body of the request
$method = $_SERVER['REQUEST_METHOD'];
$request_url = explode('?', $_SERVER['REQUEST_URI'], 2);
$request_uri = explode('/', $request_url[0]);

$id = count($request_uri) > 2 ? $request_uri[2] : '';
$request = [$request_uri[1], $id, $method];
$movie = new MovieController();
switch ($request) {
    case ['movies', '', 'GET']:
        $movie->index();
        break;
    case ['movies', '', 'POST']:
        $movie->store();
        break;
    case ['movies', true, 'GET']:
        var_dump($id);
        break;
    // Everything else
    default:
        var_dump('default');
        /*        header('HTTP/1.0 404 Not Found');
                require '../views/404.php';*/
        break;
}

exit;

