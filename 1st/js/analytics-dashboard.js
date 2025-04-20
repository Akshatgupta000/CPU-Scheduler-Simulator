// Initialize charts
document.addEventListener('DOMContentLoaded', function() {
    initializeCPUUtilizationChart();
    initializeProcessDistributionChart();
    initializeResponseTimesChart();
    loadMetricsTable();
});

function initializeCPUUtilizationChart() {
    const ctx = document.getElementById('cpuUtilizationChart').getContext('2d');
    new Chart(ctx, {
        type: 'line',
        data: {
            labels: [], // Will be populated with timestamps
            datasets: [{
                label: 'CPU Utilization %',
                data: [], // Will be populated with utilization data
                borderColor: 'rgb(75, 192, 192)',
                tension: 0.1
            }]
        },
        options: {
            responsive: true,
            scales: {
                y: {
                    beginAtZero: true,
                    max: 100
                }
            }
        }
    });
}

function initializeProcessDistributionChart() {
    const ctx = document.getElementById('processDistributionChart').getContext('2d');
    new Chart(ctx, {
        type: 'pie',
        data: {
            labels: ['Real-time', 'Interactive', 'Batch'],
            datasets: [{
                data: [30, 40, 30], // Example data
                backgroundColor: [
                    'rgb(255, 99, 132)',
                    'rgb(54, 162, 235)',
                    'rgb(255, 205, 86)'
                ]
            }]
        },
        options: {
            responsive: true
        }
    });
}

function initializeResponseTimesChart() {
    const ctx = document.getElementById('responseTimesChart').getContext('2d');
    new Chart(ctx, {
        type: 'bar',
        data: {
            labels: ['P1', 'P2', 'P3', 'P4', 'P5'],
            datasets: [{
                label: 'Response Time (ms)',
                data: [12, 19, 3, 5, 2],
                backgroundColor: 'rgb(75, 192, 192)'
            }]
        },
        options: {
            responsive: true
        }
    });
}

function loadMetricsTable() {
    // Fetch metrics data from server
    fetch('api/metrics.php')
        .then(response => response.json())
        .then(data => {
            const table = document.getElementById('metricsTable');
            table.innerHTML = data.metrics
        });
} 