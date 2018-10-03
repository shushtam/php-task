<?php

class Movie
{
    private $fields = ['Name', 'Description', 'IsAdult'];
    private $fieldType = ['Name' => 'string', 'Description' => 'string', 'IsAdult' => 'boolean'];

    public function getFields()
    {
        return $this->fields;
    }

    public function getFieldType()
    {
        return $this->fieldType;
    }
}