<?php
require_once 'includes/init.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CPU Scheduler</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <div class="container py-4">
        <div class="card mb-4 bg-primary text-white">
            <div class="card-body">
                <h1 class="card-title">CPU Scheduler</h1>
                <p class="card-text">Visualize and compare different CPU scheduling algorithms</p>
            </div>
        </div>

        <div class="row">
            <!-- Algorithm Selection -->
            <div class="col-md-4 mb-4">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title"><i class="bi bi-gear"></i> Select Algorithm</h5>
                        <select id="algorithm" class="form-select">
                            <option value="fcfs">First Come First Serve (FCFS)</option>
                            <option value="sjf">Shortest Job First (SJF)</option>
                            <option value="priority">Priority Scheduling</option>
                            <option value="rr">Round Robin</option>
                        </select>
                        <small class="text-muted mt-2 d-block">*CPU Processes are executed in the order they arrive</small>
                    </div>
                </div>
            </div>

            <!-- Add Process Form -->
            <div class="col-md-8 mb-4">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title"><i class="bi bi-plus-circle"></i> Add Process</h5>
                        <form id="processForm" class="row g-3">
                            <div class="col-md-4">
                                <label class="form-label">Arrival Time</label>
                                <input type="number" class="form-control" id="arrivalTime" min="0" required>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Burst Time</label>
                                <input type="number" class="form-control" id="burstTime" min="1" required>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Priority</label>
                                <input type="number" class="form-control" id="priority" min="1">
                            </div>
                            <div class="col-12">
                                <button type="submit" class="btn btn-primary">Add Process</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <!-- Process Table -->
        <div class="card mb-4">
            <div class="card-body">
                <h5 class="card-title"><i class="bi bi-list"></i> Processes</h5>
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Process ID</th>
                                <th>Arrival Time</th>
                                <th>Burst Time</th>
                                <th>Priority</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody id="processTable">
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Run Scheduler Button -->
        <div class="d-grid gap-2 mb-4">
            <button id="runScheduler" class="btn btn-primary btn-lg">Run Scheduler</button>
        </div>

        <!-- Gantt Chart -->
        <div class="card mb-4">
            <div class="card-body">
                <h5 class="card-title"><i class="bi bi-bar-chart"></i> Gantt Chart</h5>
                <div id="ganttChart" class="mt-3">
                    <!-- Gantt chart will be rendered here -->
                </div>
                <div class="text-muted mt-2">
                    Current Time: <span id="currentTime">0</span>
                </div>
            </div>
        </div>

        <!-- Performance Metrics -->
        <div class="card">
            <div class="card-body">
                <h5 class="card-title"><i class="bi bi-graph-up"></i> Performance Metrics</h5>
                <div class="row">
                    <div class="col-md-4">
                        <div class="metric-card">
                            <h6>Average Waiting Time</h6>
                            <p id="avgWaitingTime">0 time units</p>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="metric-card">
                            <h6>Average Turnaround Time</h6>
                            <p id="avgTurnaroundTime">0 time units</p>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="metric-card">
                            <h6>CPU Utilization</h6>
                            <p id="cpuUtilization">0%</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="assets/js/script.js"></script>
</body>
</html> 