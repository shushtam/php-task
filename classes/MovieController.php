<?php

class MovieController
{
    private $connection;
    private $collection;

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

    private function validateInput($input, $type)
    {
        if (!isset($_POST[$input])) {
            return 'The ' . $input . ' field is required';
        } elseif (empty($_POST[$input])) {
            return 'The ' . $input . ' field is empty';
        } elseif (gettype($_POST[$input]) !== $type) {
            return 'The ' . $input . ' should be ' . $type;
        }
        return false;
    }

    public function __construct()
    {
        $this->connection = new \MongoDB\Client("mongodb://php-task.loc");
        $this->collection = $this->connection->movies->movie;
    }

    public function index()
    {
        $cursor = $this->collection->find();
        $cursorArr = [];
        foreach ($cursor as $document) {
            $cursorArr[] = $document;
        }
        header('Content-type: application/json');
        echo(json_encode($cursorArr));
    }

    public function store()
    {
        $message = [];
        $status = 422;
        $name_error = $this->validateInput('Name', 'string');
        if ($name_error) {
            $message['Name'] = $name_error;
        }
        $description_error = $this->validateInput('Description', 'string');
        if ($description_error) {
            $message['Description'] = $description_error;
        }
        $adult_error = $this->validateInput('IsAdult', 'string');
        if ($adult_error) {
            $message['IsAdult'] = $adult_error;
        }
        if (!empty($message)) {
            $this->response($message, $status);
        } else {
            try {
                $insertOneResult = $this->collection->insertOne([
                    'Name' => $_POST['Name'],
                    'Description' => $_POST['Description'],
                    'IsAdult' => $_POST['IsAdult']
                ]);

                $document = $this->collection->findOne(['_id' => $insertOneResult->getInsertedId()]);

                $this->responseWithData($document, 'Inserted successfully', 200);
            }
            catch (Exception $e){
                $this->response($e->getMessage(), 400);
            }
        }
    }
}