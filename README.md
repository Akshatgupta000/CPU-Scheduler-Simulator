# Smart CPU Scheduling Simulator

A web-based CPU scheduling simulator that implements advanced scheduling techniques including adaptive multilevel queue scheduling, dynamic prioritization, and real-time performance tracking.

## Features

- Process Input Interface with multiple input methods (Web form, CSV/JSON upload)
- Adaptive Process Classification using multilevel queues
- Dynamic Prioritization Logic with priority aging
- Interactive Gantt Chart Visualization
- Real-time Performance Metrics
- Dark Mode Support
- Export functionality for simulation results

## Tech Stack

- Frontend: HTML5, CSS3, JavaScript (Chart.js)
- Backend: PHP 8.x
- Database: MySQL
- CSS Framework: Tailwind CSS

## Installation

1. Clone this repository to your web server directory
2. Import the database schema from `database/schema.sql`
3. Configure database connection in `config/database.php`
4. Ensure PHP 8.x and MySQL are installed
5. Access the application through your web browser

## Project Structure

```
/
├── assets/           # Static assets (CSS, JS, images)
├── config/           # Configuration files
├── database/         # Database schema and migrations
├── includes/         # PHP class files and utilities
├── js/              # JavaScript files
├── uploads/         # Temporary directory for file uploads
├── index.php        # Main entry point
└── README.md        # This file
```

## Usage

1. Access the main interface through index.php
2. Enter process details manually or upload a CSV/JSON file
3. Configure scheduling parameters
4. Run the simulation
5. View results in the Gantt chart and metrics panel
6. Export results as needed

## License

MIT License

## Contributing

1. Fork the repository
2. Create your feature branch
3. Commit your changes
4. Push to the branch
5. Create a new Pull Request 
