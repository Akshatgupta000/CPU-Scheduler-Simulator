-- CPU Scheduler Simulator Database Schema

CREATE DATABASE IF NOT EXISTS cpu_scheduler;
USE cpu_scheduler;

-- Simulations table to store simulation metadata
CREATE TABLE simulations (
    simulation_id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100),
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    total_processes INT,
    total_time INT,
    avg_turnaround_time FLOAT,
    avg_waiting_time FLOAT,
    avg_response_time FLOAT,
    cpu_utilization FLOAT
);

-- Processes table to store process information
CREATE TABLE processes (
    process_id INT AUTO_INCREMENT PRIMARY KEY,
    simulation_id INT,
    pid VARCHAR(50),
    arrival_time INT,
    burst_time INT,
    process_type ENUM('REAL_TIME', 'INTERACTIVE', 'BATCH'),
    priority INT,
    io_time INT,
    start_time INT,
    finish_time INT,
    turnaround_time INT,
    waiting_time INT,
    response_time INT,
    queue_level INT,
    FOREIGN KEY (simulation_id) REFERENCES simulations(simulation_id) ON DELETE CASCADE
);

-- Queue History table to track process queue movements
CREATE TABLE queue_history (
    id INT AUTO_INCREMENT PRIMARY KEY,
    simulation_id INT,
    process_id INT,
    queue_level INT,
    timestamp INT,
    reason VARCHAR(255),
    FOREIGN KEY (simulation_id) REFERENCES simulations(simulation_id) ON DELETE CASCADE,
    FOREIGN KEY (process_id) REFERENCES processes(process_id) ON DELETE CASCADE
);

-- Gantt Chart Data table to store timeline information
CREATE TABLE gantt_chart_data (
    id INT AUTO_INCREMENT PRIMARY KEY,
    simulation_id INT,
    process_id INT,
    start_time INT,
    end_time INT,
    queue_level INT,
    FOREIGN KEY (simulation_id) REFERENCES simulations(simulation_id) ON DELETE CASCADE,
    FOREIGN KEY (process_id) REFERENCES processes(process_id) ON DELETE CASCADE
);

-- Create indexes for better performance
CREATE INDEX idx_simulation_process ON processes(simulation_id);
CREATE INDEX idx_queue_history_simulation ON queue_history(simulation_id);
CREATE INDEX idx_gantt_simulation ON gantt_chart_data(simulation_id); 