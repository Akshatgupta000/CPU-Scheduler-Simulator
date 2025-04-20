<?php

class Scheduler {
    private $queues;
    private $currentTime;
    private $processes;
    private $completedProcesses;
    private $ganttChart;
    private $metrics;
    private $agingFactor;

    public function __construct() {
        $this->queues = [
            'realtime' => new Queue('realtime', 'REAL_TIME', Queue::ALGORITHM_PRIORITY, 3),
            'interactive' => new Queue('interactive', 'INTERACTIVE', Queue::ALGORITHM_ROUND_ROBIN, 2, 4),
            'batch' => new Queue('batch', 'BATCH', Queue::ALGORITHM_FCFS, 1)
        ];
        
        $this->currentTime = 0;
        $this->processes = [];
        $this->completedProcesses = [];
        $this->ganttChart = [];
        $this->metrics = [
            'cpu_utilization' => 0,
            'avg_turnaround_time' => 0,
            'avg_waiting_time' => 0,
            'avg_response_time' => 0
        ];
        $this->agingFactor = 1;
    }

    // Process management
    public function addProcess(Process $process) {
        $this->processes[] = $process;
        $this->assignProcessToQueue($process);
    }

    private function assignProcessToQueue(Process $process) {
        switch ($process->getProcessType()) {
            case 'REAL_TIME':
                $this->queues['realtime']->addProcess($process);
                break;
            case 'INTERACTIVE':
                $this->queues['interactive']->addProcess($process);
                break;
            case 'BATCH':
                $this->queues['batch']->addProcess($process);
                break;
        }
    }

    // Main scheduling loop
    public function run() {
        while (!$this->isSimulationComplete()) {
            $this->checkNewArrivals();
            $this->applyAging();
            
            $executed = false;
            foreach ($this->queues as $queue) {
                if (!$queue->isEmpty()) {
                    $result = $queue->executeProcess($this->currentTime);
                    if ($result) {
                        $this->updateGanttChart($result['process'], $result['executionTime']);
                        if ($result['process']->isCompleted()) {
                            $this->completedProcesses[] = $result['process'];
                        } else {
                            $this->checkProcessBehavior($result['process']);
                        }
                        $this->currentTime += $result['executionTime'];
                        $executed = true;
                        break;
                    }
                }
            }

            if (!$executed) {
                $this->currentTime++;
                $this->updateGanttChart(null, 1); // Idle time
            }
        }

        $this->calculateMetrics();
    }

    // Check for newly arrived processes
    private function checkNewArrivals() {
        foreach ($this->processes as $process) {
            if ($process->getState() === Process::STATE_NEW && 
                $process->getArrivalTime() <= $this->currentTime) {
                $process->setState(Process::STATE_READY);
                $this->assignProcessToQueue($process);
            }
        }
    }

    // Apply aging to prevent starvation
    private function applyAging() {
        foreach ($this->queues as $queue) {
            $queue->ageProcesses($this->agingFactor);
        }
    }

    // Check and adjust process behavior
    private function checkProcessBehavior(Process $process) {
        if ($process->isIOBound()) {
            // Move I/O bound process to interactive queue
            if ($process->getCurrentQueue() === 'batch') {
                $this->queues['batch']->removeProcess($process);
                $this->queues['interactive']->addProcess($process);
            }
        } else {
            // Move CPU bound process to batch queue
            if ($process->getCurrentQueue() === 'interactive') {
                $this->queues['interactive']->removeProcess($process);
                $this->queues['batch']->addProcess($process);
            }
        }
    }

    // Update Gantt chart
    private function updateGanttChart(Process $process = null, $duration) {
        $this->ganttChart[] = [
            'start_time' => $this->currentTime,
            'end_time' => $this->currentTime + $duration,
            'process' => $process ? $process->getPid() : 'IDLE',
            'queue' => $process ? $process->getCurrentQueue() : null
        ];
    }

    // Calculate final metrics
    private function calculateMetrics() {
        $totalTime = $this->currentTime;
        $busyTime = 0;
        $totalTurnaround = 0;
        $totalWaiting = 0;
        $totalResponse = 0;

        foreach ($this->completedProcesses as $process) {
            $busyTime += $process->getBurstTime();
            $totalTurnaround += $process->getTurnaroundTime();
            $totalWaiting += $process->getWaitingTime();
            $totalResponse += $process->getResponseTime();
        }

        $processCount = count($this->completedProcesses);
        $this->metrics = [
            'cpu_utilization' => ($busyTime / $totalTime) * 100,
            'avg_turnaround_time' => $processCount > 0 ? $totalTurnaround / $processCount : 0,
            'avg_waiting_time' => $processCount > 0 ? $totalWaiting / $processCount : 0,
            'avg_response_time' => $processCount > 0 ? $totalResponse / $processCount : 0
        ];
    }

    // Check if simulation is complete
    private function isSimulationComplete() {
        return count($this->completedProcesses) === count($this->processes);
    }

    // Getters
    public function getGanttChart() { return $this->ganttChart; }
    public function getMetrics() { return $this->metrics; }
    public function getCompletedProcesses() { return $this->completedProcesses; }
    public function getCurrentTime() { return $this->currentTime; }

    // Convert scheduler state to array for storage/transmission
    public function toArray() {
        return [
            'current_time' => $this->currentTime,
            'metrics' => $this->metrics,
            'gantt_chart' => $this->ganttChart,
            'queues' => array_map(function($queue) {
                return $queue->toArray();
            }, $this->queues),
            'completed_processes' => array_map(function($process) {
                return $process->toArray();
            }, $this->completedProcesses)
        ];
    }
} 