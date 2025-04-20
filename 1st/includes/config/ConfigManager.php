<?php
class ConfigManager {
    private $db;

    public function __construct($db) {
        $this->db = $db;
    }

    public function saveConfiguration($userId, $name, $config, $isPublic = false) {
        try {
            $stmt = $this->db->prepare("
                INSERT INTO saved_configurations 
                (user_id, name, configuration, is_public) 
                VALUES (?, ?, ?, ?)
            ");
            return $stmt->execute([
                $userId,
                $name,
                json_encode($config),
                $isPublic
            ]);
        } catch (PDOException $e) {
            return false;
        }
    }

    public function loadConfiguration($configId, $userId) {
        try {
            $stmt = $this->db->prepare("
                SELECT * FROM saved_configurations 
                WHERE config_id = ? AND (user_id = ? OR is_public = TRUE)
            ");
            $stmt->execute([$configId, $userId]);
            $config = $stmt->fetch();
            return $config ? json_decode($config['configuration'], true) : null;
        } catch (PDOException $e) {
            return null;
        }
    }

    public function getUserConfigurations($userId) {
        try {
            $stmt = $this->db->prepare("
                SELECT * FROM saved_configurations 
                WHERE user_id = ? OR is_public = TRUE 
                ORDER BY created_at DESC
            ");
            $stmt->execute([$userId]);
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            return [];
        }
    }
} 