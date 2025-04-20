<?php
require_once __DIR__ . '/../includes/init.php';

// Set headers for JSON response
header('Content-Type: application/json');

try {
    // Get request body
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (!isset($input['processes']) || empty($input['processes'])) {
        throw new Exception('No processes provided');
    }

    // Create new simulation
    $simulation = new Simulation($pdo);
    $simulationId = $simulation->create('Simulation ' . date('Y-m-d H:i:s'));

    // Add processes to simulation
    foreach ($input['processes'] as $processData) {
        $simulation->addProcess($processData);
    }

    // Run simulation
    $results = $simulation->run();

    // Return results
    echo json_encode([
        'success' => true,
        'data' => $results
    ]);

} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
} 