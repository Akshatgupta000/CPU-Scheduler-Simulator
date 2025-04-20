let ganttChart = null;

function updateGanttChart(data) {
    const ctx = document.getElementById('ganttChart').getContext('2d');
    
    if (ganttChart) {
        ganttChart.destroy();
    }

    // Process data for Chart.js
    const datasets = [];
    const queueColors = {
        'realtime': 'rgba(239, 68, 68, 0.8)',     // Red
        'interactive': 'rgba(59, 130, 246, 0.8)',  // Blue
        'batch': 'rgba(16, 185, 129, 0.8)',       // Green
        'idle': 'rgba(156, 163, 175, 0.4)'        // Gray
    };

    // Group data by process
    const processList = [...new Set(data.map(entry => entry.process))];
    processList.forEach((process, index) => {
        const processData = data.filter(entry => entry.process === process);
        
        const dataset = {
            label: process,
            data: processData.map(entry => ({
                x: [entry.start_time, entry.end_time],
                y: index
            })),
            backgroundColor: process === 'IDLE' ? queueColors.idle : queueColors[processData[0].queue],
            barPercentage: 0.8
        };
        
        datasets.push(dataset);
    });

    // Create chart configuration
    const config = {
        type: 'bar',
        data: {
            datasets: datasets
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            indexAxis: 'y',
            scales: {
                x: {
                    position: 'top',
                    stacked: false,
                    title: {
                        display: true,
                        text: 'Time Units'
                    }
                },
                y: {
                    stacked: true,
                    title: {
                        display: true,
                        text: 'Processes'
                    }
                }
            },
            plugins: {
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            const entry = data[context.dataIndex];
                            return [
                                `Process: ${entry.process}`,
                                `Queue: ${entry.queue || 'IDLE'}`,
                                `Start: ${entry.start_time}`,
                                `End: ${entry.end_time}`,
                                `Duration: ${entry.end_time - entry.start_time}`
                            ];
                        }
                    }
                },
                legend: {
                    display: false
                }
            }
        }
    };

    // Create new chart
    ganttChart = new Chart(ctx, config);

    // Adjust canvas height based on number of processes
    const canvas = document.getElementById('ganttChart');
    canvas.style.height = `${Math.max(200, processList.length * 40)}px`;
} 