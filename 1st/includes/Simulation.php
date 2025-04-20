<?php

class Simulation {
    private $pdo;
    private $scheduler;
    private $id;
    private $name;
    private $description;

    public function __construct($pdo) {
        $this->pdo = $pdo;
        $this->scheduler = new Scheduler();
    }

    // Create new simulation
    public function create($name, $description = '') {
        $stmt = $this->pdo->prepare("
            INSERT INTO simulations (name, description)
            VALUES (:name, :description)
        ");
        
        $stmt->execute([
            'name' => $name,
            'description' => $description
        ]);

        $this->id = $this->pdo->lastInsertId();
        $this->name = $name;
        $this->description = $description;

        return $this->id;
    }

    // Add process to simulation
    public function addProcess($processData) {
        $process = Process::fromArray($processData);
        
        $stmt = $this->pdo->prepare("
            INSERT INTO processes (
                simulation_id, pid, arrival_time, burst_time,
                process_type, priority, io_time
            ) VALUES (
                :simulation_id, :pid, :arrival_time, :burst_time,
                :process_type, :priority, :io_time
            )
        ");

        $stmt->execute([
            'simulation_id' => $this->id,
            'pid' => $process->getPid(),
            'arrival_time' => $process->getArrivalTime(),
            'burst_time' => $process->getBurstTime(),
            'process_type' => $process->getProcessType(),
            'priority' => $process->getPriority(),
            'io_time' => $process->getIOTime()
        ]);

        $process->setId($this->pdo->lastInsertId());
        $this->scheduler->addProcess($process);

        return $process->getId();
    }

    // Run simulation
    public function run() {
        $this->scheduler->run();
        $this->saveResults();
        return $this->scheduler->toArray();
    }

    // Save simulation results
    private function saveResults() {
        $metrics = $this->scheduler->getMetrics();
        $ganttChart = $this->scheduler->getGanttChart();
        $completedProcesses = $this->scheduler->getCompletedProcesses();

        // Update simulation metrics
        $stmt = $this->pdo->prepare("
            UPDATE simulations SET
                total_processes = :total_processes,
                total_time = :total_time,
                avg_turnaround_time = :avg_turnaround_time,
                avg_waiting_time = :avg_waiting_time,
                avg_response_time = :avg_response_time,
                cpu_utilization = :cpu_utilization
            WHERE simulation_id = :simulation_id
        ");

        $stmt->execute([
            'simulation_id' => $this->id,
            'total_processes' => count($completedProcesses),
            'total_time' => $this->scheduler->getCurrentTime(),
            'avg_turnaround_time' => $metrics['avg_turnaround_time'],
            'avg_waiting_time' => $metrics['avg_waiting_time'],
            'avg_response_time' => $metrics['avg_response_time'],
            'cpu_utilization' => $metrics['cpu_utilization']
        ]);

        // Save Gantt chart data
        $stmt = $this->pdo->prepare("
            INSERT INTO gantt_chart_data (
                simulation_id, process_id, start_time, end_time, queue_level
            ) VALUES (
                :simulation_id, :process_id, :start_time, :end_time, :queue_level
            )
        ");

        foreach ($ganttChart as $entry) {
            if ($entry['process'] !== 'IDLE') {
                $stmt->execute([
                    'simulation_id' => $this->id,
                    'process_id' => $this->findProcessId($entry['process']),
                    'start_time' => $entry['start_time'],
                    'end_time' => $entry['end_time'],
                    'queue_level' => $this->getQueueLevel($entry['queue'])
                ]);
            }
        }

        // Update process results
        $stmt = $this->pdo->prepare("
            UPDATE processes SET
                start_time = :start_time,
                finish_time = :finish_time,
                turnaround_time = :turnaround_time,
                waiting_time = :waiting_time,
                response_time = :response_time
            WHERE process_id = :process_id
        ");

        foreach ($completedProcesses as $process) {
            $stmt->execute([
                'process_id' => $process->getId(),
                'start_time' => $process->getStartTime(),
                'finish_time' => $process->getFinishTime(),
                'turnaround_time' => $process->getTurnaroundTime(),
                'waiting_time' => $process->getWaitingTime(),
                'response_time' => $process->getResponseTime()
            ]);
        }
    }

    // Helper methods
    private function findProcessId($pid) {
        $stmt = $this->pdo->prepare("
            SELECT process_id FROM processes
            WHERE simulation_id = :simulation_id AND pid = :pid
        ");
        
        $stmt->execute([
            'simulation_id' => $this->id,
            'pid' => $pid
        ]);

        return $stmt->fetchColumn();
    }

    private function getQueueLevel($queueName) {
        $levels = [
            'realtime' => 1,
            'interactive' => 2,
            'batch' => 3
        ];
        return $levels[$queueName] ?? 0;
    }

    // Load simulation from database
    public function load($simulationId) {
        $stmt = $this->pdo->prepare("
            SELECT * FROM simulations WHERE simulation_id = :simulation_id
        ");
        
        $stmt->execute(['simulation_id' => $simulationId]);
        $simulation = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$simulation) {
            throw new Exception("Simulation not found");
        }

        $this->id = $simulation['simulation_id'];
        $this->name = $simulation['name'];
        $this->description = $simulation['description'];

        // Load processes
        $stmt = $this->pdo->prepare("
            SELECT * FROM processes WHERE simulation_id = :simulation_id
        ");
        
        $stmt->execute(['simulation_id' => $simulationId]);
        $processes = $stmt->fetchAll(PDO::FETCH_ASSOC);

        foreach ($processes as $processData) {
            $process = Process::fromArray($processData);
            $process->setId($processData['process_id']);
            $this->scheduler->addProcess($process);
        }

        return $this;
    }

    // Get simulation data
    public function getData() {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'description' => $this->description,
            'scheduler' => $this->scheduler->toArray()
        ];
    }
} 