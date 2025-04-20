<?php

class Queue {
    private $name;
    private $type;
    private $processes;
    private $timeQuantum;
    private $algorithm;
    private $priority;

    const ALGORITHM_PRIORITY = 'priority';
    const ALGORITHM_ROUND_ROBIN = 'round_robin';
    const ALGORITHM_FCFS = 'fcfs';
    const ALGORITHM_SJF = 'sjf';

    public function __construct($name, $type, $algorithm, $priority, $timeQuantum = 0) {
        $this->name = $name;
        $this->type = $type;
        $this->algorithm = $algorithm;
        $this->priority = $priority;
        $this->timeQuantum = $timeQuantum;
        $this->processes = [];
    }

    // Queue management methods
    public function addProcess(Process $process) {
        $process->setCurrentQueue($this->name);
        $this->processes[] = $process;
        $this->sort();
    }

    public function removeProcess(Process $process) {
        foreach ($this->processes as $key => $p) {
            if ($p->getId() === $process->getId()) {
                unset($this->processes[$key]);
                $this->processes = array_values($this->processes);
                return true;
            }
        }
        return false;
    }

    public function getNextProcess() {
        return !empty($this->processes) ? $this->processes[0] : null;
    }

    // Sorting methods based on scheduling algorithm
    private function sort() {
        switch ($this->algorithm) {
            case self::ALGORITHM_PRIORITY:
                usort($this->processes, function($a, $b) {
                    return $b->getPriority() - $a->getPriority();
                });
                break;
            
            case self::ALGORITHM_SJF:
                usort($this->processes, function($a, $b) {
                    return $a->getRemainingTime() - $b->getRemainingTime();
                });
                break;
            
            case self::ALGORITHM_FCFS:
                usort($this->processes, function($a, $b) {
                    return $a->getArrivalTime() - $b->getArrivalTime();
                });
                break;
            
            case self::ALGORITHM_ROUND_ROBIN:
                // No sorting needed for Round Robin
                break;
        }
    }

    // Process aging to prevent starvation
    public function ageProcesses($agingFactor) {
        foreach ($this->processes as $process) {
            $process->adjustPriority($agingFactor);
        }
        if ($this->algorithm === self::ALGORITHM_PRIORITY) {
            $this->sort();
        }
    }

    // Queue execution methods
    public function executeProcess($currentTime) {
        $process = $this->getNextProcess();
        if (!$process) {
            return null;
        }

        $executionTime = 0;
        switch ($this->algorithm) {
            case self::ALGORITHM_ROUND_ROBIN:
                $executionTime = $process->execute($this->timeQuantum);
                if (!$process->isCompleted()) {
                    $this->removeProcess($process);
                    $this->addProcess($process);
                }
                break;

            default:
                $executionTime = $process->execute($process->getRemainingTime());
                break;
        }

        if ($process->isCompleted()) {
            $process->setFinishTime($currentTime + $executionTime);
            $this->removeProcess($process);
        }

        return [
            'process' => $process,
            'executionTime' => $executionTime
        ];
    }

    // Getters
    public function getName() { return $this->name; }
    public function getType() { return $this->type; }
    public function getProcesses() { return $this->processes; }
    public function getTimeQuantum() { return $this->timeQuantum; }
    public function getAlgorithm() { return $this->algorithm; }
    public function getPriority() { return $this->priority; }
    public function isEmpty() { return empty($this->processes); }
    public function getProcessCount() { return count($this->processes); }

    // Convert queue to array for storage/transmission
    public function toArray() {
        return [
            'name' => $this->name,
            'type' => $this->type,
            'algorithm' => $this->algorithm,
            'priority' => $this->priority,
            'time_quantum' => $this->timeQuantum,
            'process_count' => $this->getProcessCount(),
            'processes' => array_map(function($process) {
                return $process->toArray();
            }, $this->processes)
        ];
    }
} 