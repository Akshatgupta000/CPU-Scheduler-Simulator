<?php

class Process {
    private $process_id;
    private $simulation_id;
    private $pid;
    private $arrival_time;
    private $burst_time;
    private $process_type;
    private $priority;
    private $io_time;
    private $start_time;
    private $finish_time;
    private $pdo;

    public function __construct($pdo) {
        $this->pdo = $pdo;
    }

    public function create($data) {
        $sql = "INSERT INTO processes (simulation_id, pid, arrival_time, burst_time, process_type, priority, io_time) 
                VALUES (:simulation_id, :pid, :arrival_time, :burst_time, :process_type, :priority, :io_time)";
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            ':simulation_id' => $data['simulation_id'],
            ':pid' => $data['pid'],
            ':arrival_time' => $data['arrival_time'],
            ':burst_time' => $data['burst_time'],
            ':process_type' => $data['process_type'],
            ':priority' => $data['priority'],
            ':io_time' => $data['io_time'] ?? 0
        ]);

        return $this->pdo->lastInsertId();
    }

    public function update($process_id, $data) {
        $sql = "UPDATE processes SET 
                start_time = :start_time,
                finish_time = :finish_time
                WHERE process_id = :process_id";
        
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([
            ':process_id' => $process_id,
            ':start_time' => $data['start_time'],
            ':finish_time' => $data['finish_time']
        ]);
    }

    public function getBySimulation($simulation_id) {
        $sql = "SELECT * FROM processes WHERE simulation_id = :simulation_id ORDER BY arrival_time";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':simulation_id' => $simulation_id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getById($process_id) {
        $sql = "SELECT * FROM processes WHERE process_id = :process_id";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':process_id' => $process_id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function delete($process_id) {
        $sql = "DELETE FROM processes WHERE process_id = :process_id";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([':process_id' => $process_id]);
    }

    public function getProcessMetrics($simulation_id) {
        $sql = "SELECT 
                AVG(finish_time - arrival_time) as avg_turnaround_time,
                AVG(start_time - arrival_time) as avg_waiting_time,
                AVG(start_time - arrival_time) as avg_response_time
                FROM processes 
                WHERE simulation_id = :simulation_id";
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':simulation_id' => $simulation_id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
} 