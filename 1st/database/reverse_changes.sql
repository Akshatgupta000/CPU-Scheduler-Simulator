-- Drop the tables in reverse order (to handle foreign key constraints)
DROP TABLE IF EXISTS reports;
DROP TABLE IF EXISTS analytics;
DROP TABLE IF EXISTS saved_configurations;
DROP TABLE IF EXISTS users;

-- Drop the indexes we created
DROP INDEX IF EXISTS idx_user_email ON users;
DROP INDEX IF EXISTS idx_user_configs ON saved_configurations;
DROP INDEX IF EXISTS idx_analytics_simulation ON analytics;
DROP INDEX IF EXISTS idx_analytics_user ON analytics;
DROP INDEX IF EXISTS idx_reports_simulation ON reports;
DROP INDEX IF EXISTS idx_reports_user ON reports; 