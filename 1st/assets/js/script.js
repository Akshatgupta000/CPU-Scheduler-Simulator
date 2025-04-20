document.addEventListener('DOMContentLoaded', function() {
    let processes = [];
    let currentTime = 0;
    let ganttChart = null;
    let nextProcessId = 1; // Initialize process ID counter

    // Initialize Chart.js for Gantt Chart
    function initGanttChart() {
        const ctx = document.createElement('canvas');
        document.getElementById('ganttChart').appendChild(ctx);
        
        ganttChart = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: [],
                datasets: [{
                    label: 'Process Execution',
                    data: [],
                    backgroundColor: [],
                    borderWidth: 1,
                    borderColor: 'rgba(0, 0, 0, 0.1)',
                    barPercentage: 0.6,
                    categoryPercentage: 0.8
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                indexAxis: 'y',
                layout: {
                    padding: {
                        top: 15,
                        right: 10,
                        bottom: 5,
                        left: 10
                    }
                },
                plugins: {
                    legend: {
                        display: false
                    },
                    tooltip: {
                        enabled: true,
                        displayColors: false,
                        callbacks: {
                            label: function(context) {
                                const process = context.raw;
                                return `Process ${process.pid} (${process.startTime} - ${process.endTime})`;
                            }
                        }
                    }
                },
                scales: {
                    x: {
                        position: 'top',
                        stacked: false,
                        min: 0,
                        grid: {
                            display: true,
                            color: 'rgba(0, 0, 0, 0.1)',
                            lineWidth: 0.5
                        },
                        ticks: {
                            stepSize: 1,
                            font: {
                                size: 10
                            },
                            padding: 5,
                            callback: function(value) {
                                return value;
                            }
                        },
                        title: {
                            display: false
                        }
                    },
                    y: {
                        stacked: true,
                        grid: {
                            display: false
                        },
                        ticks: {
                            font: {
                                size: 10
                            },
                            padding: 5,
                            callback: function(value, index) {
                                const process = this.chart.data.datasets[0].data[index];
                                return process ? `P${process.pid}` : '';
                            }
                        }
                    }
                }
            }
        });
    }

    // Handle form submission
    document.getElementById('processForm').addEventListener('submit', function(e) {
        e.preventDefault();
        
        const process = {
            pid: nextProcessId, // Use the current process ID
            arrivalTime: parseInt(document.getElementById('arrivalTime').value),
            burstTime: parseInt(document.getElementById('burstTime').value),
            priority: parseInt(document.getElementById('priority').value) || 1,
            remainingTime: parseInt(document.getElementById('burstTime').value)
        };

        processes.push(process);
        nextProcessId++; // Increment the process ID for the next process
        updateProcessTable();
        clearForm();
    });

    // Update process table
    function updateProcessTable() {
        const tableBody = document.getElementById('processTable');
        tableBody.innerHTML = '';

        processes.forEach((process, index) => {
            const row = document.createElement('tr');
            row.innerHTML = `
                <td>P${process.pid}</td>
                <td>${process.arrivalTime}</td>
                <td>${process.burstTime}</td>
                <td>${process.priority}</td>
                <td>
                    <button class="btn btn-remove" onclick="removeProcess(${index})">
                        <i class="bi bi-trash"></i>
                    </button>
                </td>
            `;
            tableBody.appendChild(row);
        });
    }

    // Clear form after submission
    function clearForm() {
        document.getElementById('arrivalTime').value = '';
        document.getElementById('burstTime').value = '';
        document.getElementById('priority').value = '';
    }

    // Remove process
    window.removeProcess = function(index) {
        processes.splice(index, 1);
        updateProcessTable();
    };

    // Run scheduler
    document.getElementById('runScheduler').addEventListener('click', function() {
        const algorithm = document.getElementById('algorithm').value;
        runSchedulingAlgorithm(algorithm);
    });

    // Scheduling algorithms
    function runSchedulingAlgorithm(algorithm) {
        let scheduledProcesses = [];
        let currentTime = 0;
        let completedProcesses = [];
        
        // Sort processes by arrival time
        let sortedProcesses = [...processes].sort((a, b) => a.arrivalTime - b.arrivalTime);
        
        switch(algorithm) {
            case 'fcfs':
                scheduledProcesses = runFCFS(sortedProcesses);
                break;
            case 'sjf':
                scheduledProcesses = runSJF(sortedProcesses);
                break;
            case 'priority':
                scheduledProcesses = runPriority(sortedProcesses);
                break;
            case 'rr':
                scheduledProcesses = runRoundRobin(sortedProcesses);
                break;
        }
        
        updateGanttChart(scheduledProcesses);
        updateMetrics(scheduledProcesses);
    }

    // First Come First Serve
    function runFCFS(processes) {
        let currentTime = 0;
        let scheduledProcesses = [];
        
        processes.forEach(process => {
            if (currentTime < process.arrivalTime) {
                currentTime = process.arrivalTime;
            }
            
            scheduledProcesses.push({
                pid: process.pid,
                startTime: currentTime,
                endTime: currentTime + process.burstTime
            });
            
            currentTime += process.burstTime;
        });
        
        return scheduledProcesses;
    }

    // Shortest Job First
    function runSJF(processes) {
        let currentTime = 0;
        let scheduledProcesses = [];
        let remainingProcesses = [...processes];
        
        while (remainingProcesses.length > 0) {
            let availableProcesses = remainingProcesses.filter(p => p.arrivalTime <= currentTime);
            
            if (availableProcesses.length === 0) {
                currentTime++;
                continue;
            }
            
            let shortestJob = availableProcesses.reduce((prev, curr) => 
                prev.burstTime < curr.burstTime ? prev : curr
            );
            
            scheduledProcesses.push({
                pid: shortestJob.pid,
                startTime: currentTime,
                endTime: currentTime + shortestJob.burstTime
            });
            
            currentTime += shortestJob.burstTime;
            remainingProcesses = remainingProcesses.filter(p => p.pid !== shortestJob.pid);
        }
        
        return scheduledProcesses;
    }

    // Priority Scheduling
    function runPriority(processes) {
        let currentTime = 0;
        let scheduledProcesses = [];
        let remainingProcesses = [...processes];
        
        while (remainingProcesses.length > 0) {
            let availableProcesses = remainingProcesses.filter(p => p.arrivalTime <= currentTime);
            
            if (availableProcesses.length === 0) {
                currentTime++;
                continue;
            }
            
            let highestPriority = availableProcesses.reduce((prev, curr) => 
                prev.priority < curr.priority ? prev : curr
            );
            
            scheduledProcesses.push({
                pid: highestPriority.pid,
                startTime: currentTime,
                endTime: currentTime + highestPriority.burstTime
            });
            
            currentTime += highestPriority.burstTime;
            remainingProcesses = remainingProcesses.filter(p => p.pid !== highestPriority.pid);
        }
        
        return scheduledProcesses;
    }

    // Round Robin
    function runRoundRobin(processes, quantum = 2) {
        let currentTime = 0;
        let scheduledProcesses = [];
        let remainingProcesses = processes.map(p => ({...p}));
        
        while (remainingProcesses.length > 0) {
            let executed = false;
            
            for (let i = 0; i < remainingProcesses.length; i++) {
                let process = remainingProcesses[i];
                
                if (process.arrivalTime <= currentTime) {
                    let executeTime = Math.min(quantum, process.remainingTime);
                    
                    scheduledProcesses.push({
                        pid: process.pid,
                        startTime: currentTime,
                        endTime: currentTime + executeTime
                    });
                    
                    process.remainingTime -= executeTime;
                    currentTime += executeTime;
                    executed = true;
                    
                    if (process.remainingTime === 0) {
                        remainingProcesses.splice(i, 1);
                        i--;
                    }
                }
            }
            
            if (!executed) currentTime++;
        }
        
        return scheduledProcesses;
    }

    // Update Gantt Chart
    function updateGanttChart(scheduledProcesses) {
        if (!ganttChart) {
            initGanttChart();
        }

        // Calculate the maximum end time for x-axis
        const maxEndTime = Math.max(...scheduledProcesses.map(p => p.endTime));

        // Prepare data for the chart
        const chartData = scheduledProcesses.map(p => ({
            pid: p.pid,
            x: [p.startTime, p.endTime],
            y: p.pid,
            startTime: p.startTime,
            endTime: p.endTime
        }));

        // Generate colors for each process
        const processColors = {};
        processes.forEach((p, index) => {
            processColors[p.pid] = `hsl(${(index * 137.5) % 360}, 70%, 50%)`;
        });

        // Update chart data
        ganttChart.data.datasets = [{
            label: 'Process Execution',
            data: chartData,
            backgroundColor: chartData.map(d => processColors[d.pid]),
            borderWidth: 1,
            borderColor: 'rgba(0, 0, 0, 0.1)',
            barPercentage: 0.6
        }];

        // Update x-axis configuration
        ganttChart.options.scales.x.max = maxEndTime;

        // Update chart
        ganttChart.update();

        // Update current time display
        document.getElementById('currentTime').textContent = maxEndTime;

        // Create compact timeline markers
        const timelineContainer = document.createElement('div');
        timelineContainer.className = 'timeline-markers';
        timelineContainer.style.display = 'flex';
        timelineContainer.style.justifyContent = 'space-between';
        timelineContainer.style.marginTop = '5px';
        timelineContainer.style.padding = '0 20px';

        // Only show markers at important intervals
        for (let i = 0; i <= maxEndTime; i++) {
            if (i === 0 || i === maxEndTime || i % 2 === 0) {
                const marker = document.createElement('div');
                marker.className = 'time-marker';
                marker.textContent = i;
                timelineContainer.appendChild(marker);
            }
        }

        // Replace existing timeline if any
        const existingTimeline = document.querySelector('.timeline-markers');
        if (existingTimeline) {
            existingTimeline.remove();
        }
        document.getElementById('ganttChart').appendChild(timelineContainer);
    }

    // Update Performance Metrics
    function updateMetrics(scheduledProcesses) {
        let totalWaitingTime = 0;
        let totalTurnaroundTime = 0;
        let totalBurstTime = 0;
        
        processes.forEach(process => {
            const scheduled = scheduledProcesses.find(sp => sp.pid === process.pid);
            if (scheduled) {
                totalWaitingTime += scheduled.startTime - process.arrivalTime;
                totalTurnaroundTime += scheduled.endTime - process.arrivalTime;
                totalBurstTime += process.burstTime;
            }
        });

        const avgWaitingTime = totalWaitingTime / processes.length;
        const avgTurnaroundTime = totalTurnaroundTime / processes.length;
        const cpuUtilization = (totalBurstTime / scheduledProcesses[scheduledProcesses.length - 1].endTime) * 100;

        document.getElementById('avgWaitingTime').textContent = `${avgWaitingTime.toFixed(2)} time units`;
        document.getElementById('avgTurnaroundTime').textContent = `${avgTurnaroundTime.toFixed(2)} time units`;
        document.getElementById('cpuUtilization').textContent = `${cpuUtilization.toFixed(2)}%`;
    }

    // Initialize Gantt Chart
    initGanttChart();
}); 