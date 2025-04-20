function updateMetrics(metrics) {
    // Update CPU Utilization
    const cpuUtilization = document.getElementById('cpuUtilization');
    cpuUtilization.textContent = `${metrics.cpu_utilization.toFixed(2)}%`;
    updateMetricColor(cpuUtilization, metrics.cpu_utilization);

    // Update Average Turnaround Time
    const avgTurnaround = document.getElementById('avgTurnaround');
    avgTurnaround.textContent = `${metrics.avg_turnaround_time.toFixed(2)} units`;
    updateMetricColor(avgTurnaround, 100 - (metrics.avg_turnaround_time / 2));

    // Update Average Waiting Time
    const avgWaiting = document.getElementById('avgWaiting');
    avgWaiting.textContent = `${metrics.avg_waiting_time.toFixed(2)} units`;
    updateMetricColor(avgWaiting, 100 - metrics.avg_waiting_time);

    // Update Average Response Time
    const avgResponse = document.getElementById('avgResponse');
    avgResponse.textContent = `${metrics.avg_response_time.toFixed(2)} units`;
    updateMetricColor(avgResponse, 100 - metrics.avg_response_time);
}

function updateMetricColor(element, value) {
    // Remove existing color classes
    element.classList.remove('text-red-500', 'text-yellow-500', 'text-green-500');
    
    // Add appropriate color class based on value
    if (value >= 80) {
        element.classList.add('text-green-500');
    } else if (value >= 60) {
        element.classList.add('text-yellow-500');
    } else {
        element.classList.add('text-red-500');
    }
} 