<?php

require_once __DIR__ . '/../Models/CiberModel.php';

class CiberController
{
    /** @var CiberModel */
    private $model;

    public function __construct()
    {
        $this->model = new CiberModel();
    }

    public function index(): array
    {
        return $this->model->obtenerComputadoras();
    }
}
