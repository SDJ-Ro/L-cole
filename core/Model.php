<?php
/**
 * =========================================================================
 * L'ÉCOLE — BASE MODEL
 * =========================================================================
 * Foundational model inherited by all domain entities.
 * Automatically injects the singleton PDO database instance and provides
 * transaction management primitives.
 * =========================================================================
 */

require_once __DIR__ . '/Database.php';

abstract class Model {

    /**
     * Shared PDO connection instance.
     */
    protected PDO $db;

    /**
     * Initializes the model with the singleton PDO connection.
     */
    public function __construct() {
        $this->db = Database::getConnection();
    }

    /**
     * Begin a database transaction.
     */
    public function beginTransaction(): bool {
        return $this->db->beginTransaction();
    }

    /**
     * Commit the active transaction.
     */
    public function commit(): bool {
        return $this->db->commit();
    }

    /**
     * Roll back the active transaction on error.
     */
    public function rollBack(): bool {
        if ($this->db->inTransaction()) {
            return $this->db->rollBack();
        }
        return false;
    }

    /**
     * Get the auto-increment ID of the most recently inserted row.
     */
    public function lastInsertId(): string|false {
        return $this->db->lastInsertId();
    }
}
