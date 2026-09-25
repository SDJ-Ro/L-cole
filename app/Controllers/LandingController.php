<?php
class LandingController extends Controller {
    
    // Main landing page: http://localhost:8040/landing or http://localhost:8040/
    public function index() {
        try {
            require_once __DIR__ . '/../../core/Database.php';
            $db = Database::getConnection();
            $db->exec("
                INSERT INTO daily_stats (stat_date, landing_views, portal_logins) 
                VALUES (CURDATE(), 1, 0) 
                ON DUPLICATE KEY UPDATE landing_views = landing_views + 1
            ");
        } catch (\Throwable $e) {
            // Non-critical telemetry error
        }

        $this->view('landing_page/landing');
    }

    // Honours board page: http://localhost:8040/landing/achievements
    public function achievements() {
        $this->view('landing_page/achievements');
    }
}