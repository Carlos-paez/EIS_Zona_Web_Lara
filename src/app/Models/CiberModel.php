<?php

require_once __DIR__ . '/../../Config/database.php';

class CiberModel
{
    protected $pdo;

    public function __construct()
    {
        global $pdo;
        $this->pdo = $pdo;
    }

    public function obtenerComputadoras(): array
    {
        if (!$this->pdo) {
            return [];
        }

        try {
            $stmt = $this->pdo->query('SELECT * FROM computadoras');
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            return [];
        }
    }
}
