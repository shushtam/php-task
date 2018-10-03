<?php
require __DIR__ . '/vendor/autoload.php';
require __DIR__ . '/classes/MovieController.php';

$method = $_SERVER['REQUEST_METHOD'];
$request_url = explode('?', $_SERVER['REQUEST_URI'], 2);
$request_uri = explode('/', $request_url[0]);

$id = count($request_uri) > 2 ? $request_uri[2] : '';
$request = [$request_uri[1], $id, $method];
$movie = new MovieController();

switch ($request) {
    case ['', '', 'GET']:
        echo json_encode([
            'status' => 200,
            'message' => 'home',
        ]);
        break;
    case ['movies', '', 'GET']:
        $movie->index();
        break;
    case ['movies', '', 'POST']:
        $movie->store();
        break;
    case ['movies', true, 'PUT']:
        $movie->update($id);
        break;
    case ['movies', true, 'GET']:
        $movie->show($id);
        break;
    case ['movies', true, 'DELETE']:
        $movie->destroy($id);
        break;
    default:
        echo json_encode([
            'status' => 404,
            'message' => 'Not found',
        ]);
        break;
}


