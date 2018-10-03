<?php

require __DIR__ . '/Movie.php';
require __DIR__ . '/../config/db-config.php';

class MovieController
{
    private $connection;
    private $collection;
    private $movie;

    public function __construct()
    {
        $this->connection = new \MongoDB\Client("mongodb://" . CONFIG['uri']);
        $this->collection = $this->connection->moviesdb->movie;
        $this->movie = new Movie();
    }

    public function index()
    {
        $cursor = $this->collection->find();
        $documents = [];
        foreach ($cursor as $document) {
            $documents[] = $document;
        }
        if (!empty($documents)) {
            echo(json_encode($documents));
        } else {
            $this->response('No movies', 200);
        }
    }

    public function show($id)
    {
        try {
            $document = $this->collection->findOne(['_id' => new MongoDB\BSON\ObjectID($id)]);
            if ($document) {
                $this->responseWithData($document, 'success', 200);
            } else {
                $this->response('Not found', 404);
            }
        } catch (Exception $e) {
            $this->response('Not found', 404);
        }
    }

    public function store()
    {
        $message = [];
        $status = 422;
        parse_str(file_get_contents("php://input"), $post_inputs);
        $requiredMessage = $this->checkFields($post_inputs);
        if (!empty($requiredMessage)) {
            $this->response($requiredMessage, $status);
        } else {
            foreach ($post_inputs as $key => $input) {
                $error = $this->validateInput($input, $key, $this->movie->getFieldType()[$key]);
                if ($error) {
                    $message[$key] = $error;
                }
            }
            if (!empty($message)) {
                $this->response($message, $status);
            } else {
                try {
                    $insertDocument = $this->collection->insertOne([
                        'Name' => $post_inputs['Name'],
                        'Description' => $post_inputs['Description'],
                        'IsAdult' => filter_var($post_inputs['IsAdult'], FILTER_VALIDATE_BOOLEAN, ['flags' => FILTER_NULL_ON_FAILURE])
                    ]);

                    $document = $this->collection->findOne(['_id' => $insertDocument->getInsertedId()]);

                    $this->responseWithData($document, 'Inserted successfully', 200);
                } catch (Exception $e) {
                    $this->response($e->getMessage(), 400);
                }
            }
        }
    }

    public function update($id)
    {
        $message = [];
        $status = 422;
        parse_str(file_get_contents("php://input"), $put_inputs);
        $requiredMessage = $this->checkFields($put_inputs);
        if (!empty($requiredMessage)) {
            $this->response($requiredMessage, $status);
        } else {
            foreach ($put_inputs as $key => $input) {
                $error = $this->validateInput($input, $key, $this->movie->getFieldType()[$key]);
                if ($error) {
                    $message[$key] = $error;
                }
            }
            if (!empty($message)) {
                $this->response($message, $status);
            } else {
                try {
                    $updatedDocument = $this->collection->updateOne(
                        ['_id' => new MongoDB\BSON\ObjectID($id)],
                        ['$set' => ['Name' => $put_inputs['Name'],
                            'Description' => $put_inputs['Description'],
                            'IsAdult' => filter_var($put_inputs['IsAdult'], FILTER_VALIDATE_BOOLEAN, ['flags' => FILTER_NULL_ON_FAILURE])]
                        ]);
                    if ($updatedDocument->getModifiedCount() > 0 || $updatedDocument->getMatchedCount() > 0) {
                        $document = $this->collection->findOne(['_id' => new MongoDB\BSON\ObjectID($id)]);
                        $this->responseWithData($document, 'Updated successfully', 200);
                    } else {
                        $this->response('Not found', 404);
                    }

                } catch (Exception $e) {
                    $this->response($e->getMessage(), 400);
                }
            }
        }
    }

    public function destroy($id)
    {
        try {
            $deletedDocument = $this->collection->deleteOne(['_id' => new MongoDB\BSON\ObjectID($id)]);
            if ($deletedDocument->getDeletedCount() > 0) {
                $this->response('Deleted successfully', 200);
            } else {
                $this->response('Not found', 404);
            }
        } catch (Exception $e) {
            $this->response('Not found', 404);
        }
    }

    private function response($message, $status)
    {
        echo json_encode([
            'status' => $status,
            'message' => $message
        ]);
    }

    private function responseWithData($data, $message, $status)
    {
        echo json_encode([
            'status' => $status,
            'message' => $message,
            'data' => $data
        ]);
    }

    private function checkFields($inputs)
    {
        $message = [];
        $requiredFields = $this->movie->getFields();
        $missingFields = array_diff($requiredFields, array_keys($inputs));
        if (!empty($missingFields)) {
            foreach ($missingFields as $missingError) {
                $message[$missingError] = 'The ' . $missingError . ' field is required';;
            }
        }
        return $message;
    }

    private function validateInput($input, $key, $type)
    {
        if (($input !== '0' || $input !== '0') && empty($input)) {
            return 'The ' . $key . ' field is empty';
        } else {
            if ($type == 'boolean') {
                $inputType = gettype(filter_var($input, FILTER_VALIDATE_BOOLEAN, ['flags' => FILTER_NULL_ON_FAILURE]));
            } else {
                $inputType = gettype($input);
            }
            if ($inputType !== $type) {
                return 'The ' . $key . ' should be ' . $type;
            }
        }
        return false;
    }
}