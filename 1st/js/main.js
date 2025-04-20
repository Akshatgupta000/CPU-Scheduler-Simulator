document.addEventListener('DOMContentLoaded', function() {
    // Form handling
    const processForm = document.getElementById('processForm');
    const uploadBtn = document.getElementById('uploadBtn');
    const startSimulation = document.getElementById('startSimulation');
    const exportResults = document.getElementById('exportResults');
    const toggleDarkMode = document.getElementById('toggleDarkMode');

    let processes = [];
    let simulationResults = null;

    // Process form submission
    processForm.addEventListener('submit', function(e) {
        e.preventDefault();
        const formData = new FormData(processForm);
        const process = {
            pid: formData.get('pid'),
            arrival_time: parseInt(formData.get('arrival_time')),
            burst_time: parseInt(formData.get('burst_time')),
            process_type: formData.get('process_type'),
            priority: parseInt(formData.get('priority')),
            io_time: parseInt(formData.get('io_time'))
        };

        processes.push(process);
        updateProcessQueues();
        processForm.reset();
    });

    // File upload handling
    uploadBtn.addEventListener('click', function() {
        const input = document.createElement('input');
        input.type = 'file';
        input.accept = '.csv,.json';
        
        input.addEventListener('change', function(e) {
            const file = e.target.files[0];
            const reader = new FileReader();
            
            reader.onload = function(e) {
                try {
                    if (file.name.endsWith('.json')) {
                        processes = JSON.parse(e.target.result);
                    } else {
                        processes = parseCSV(e.target.result);
                    }
                    updateProcessQueues();
                } catch (error) {
                    alert('Error parsing file: ' + error.message);
                }
            };
            
            if (file.name.endsWith('.json')) {
                reader.readAsText(file);
            } else {
                reader.readAsText(file);
            }
        });
        
        input.click();
    });

    // Start simulation
    startSimulation.addEventListener('click', function() {
        if (processes.length === 0) {
            alert('Please add processes before starting simulation');
            return;
        }

        fetch('api/simulate.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({ processes })
        })
        .then(response => response.json())
        .then(data => {
            simulationResults = data;
            updateGanttChart(data.gantt_chart);
            updateMetrics(data.metrics);
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Error running simulation');
        });
    });

    // Export results
    exportResults.addEventListener('click', function() {
        if (!simulationResults) {
            alert('No simulation results to export');
            return;
        }

        const dataStr = JSON.stringify(simulationResults, null, 2);
        const dataUri = 'data:application/json;charset=utf-8,' + encodeURIComponent(dataStr);
        
        const exportLink = document.createElement('a');
        exportLink.setAttribute('href', dataUri);
        exportLink.setAttribute('download', 'simulation_results.json');
        exportLink.click();
    });

    // Dark mode toggle
    toggleDarkMode.addEventListener('click', function() {
        document.body.classList.toggle('dark');
    });

    // Helper functions
    function updateProcessQueues() {
        const queues = {
            'REAL_TIME': document.querySelector('#realTimeQueue .process-list'),
            'INTERACTIVE': document.querySelector('#interactiveQueue .process-list'),
            'BATCH': document.querySelector('#batchQueue .process-list')
        };

        // Clear existing lists
        Object.values(queues).forEach(queue => queue.innerHTML = '');

        // Group processes by type
        processes.forEach(process => {
            const element = document.createElement('div');
            element.className = 'process-item p-2 mb-2 bg-gray-100 dark:bg-gray-700 rounded';
            element.innerHTML = `
                <div class="flex justify-between">
                    <span class="font-medium">PID: ${process.pid}</span>
                    <span>Burst: ${process.burst_time}</span>
                </div>
                <div class="text-sm text-gray-600 dark:text-gray-300">
                    Arrival: ${process.arrival_time} | Priority: ${process.priority}
                </div>
            `;
            queues[process.process_type].appendChild(element);
        });
    }

    function parseCSV(csvText) {
        const lines = csvText.split('\n');
        const header = lines[0].split(',').map(h => h.trim());
        return lines.slice(1)
            .filter(line => line.trim())
            .map(line => {
                const values = line.split(',').map(v => v.trim());
                const process = {};
                header.forEach((key, index) => {
                    const value = values[index];
                    process[key] = ['arrival_time', 'burst_time', 'priority', 'io_time'].includes(key) 
                        ? parseInt(value) 
                        : value;
                });
                return process;
            });
    }
}); 