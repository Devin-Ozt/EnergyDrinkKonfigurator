<?php
/**
 * Datenbank-Konfiguration
 * Verwendet die Docker-Vorlage Einstellungen
 */

define('DB_HOST', 'db');
define('DB_NAME', 'meine_db');
define('DB_USER', 'benutzer');
define('DB_PASS', 'benutzerpasswort');

/**
 * Erstellt eine PDO-Datenbankverbindung
 * @return PDO
 */
function getDbConnection(): PDO {
    static $pdo = null;
    
    if ($pdo === null) {
        try {
            $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4";
            $pdo = new PDO($dsn, DB_USER, DB_PASS, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false
            ]);
        } catch (PDOException $e) {
            die("Datenbankverbindung fehlgeschlagen: " . $e->getMessage());
        }
    }
    
    return $pdo;
}

/**
 * Session starten wenn noch nicht gestartet
 */
function startSession(): void {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
}

/**
 * Prüft ob ein User eingeloggt ist
 * @return bool
 */
function isLoggedIn(): bool {
    startSession();
    return isset($_SESSION['user_id']);
}

/**
 * Holt die User-ID aus der Session
 * @return int|null
 */
function getUserId(): ?int {
    startSession();
    return $_SESSION['user_id'] ?? null;
}

/**
 * Holt den User-Namen aus der Session
 * @return string|null
 */
function getUserName(): ?string {
    startSession();
    return $_SESSION['user_name'] ?? null;
}

/**
 * Formatiert einen Preis in Euro
 * @param float $price
 * @return string
 */
function formatPrice(float $price): string {
    return number_format($price, 2, ',', '.') . ' €';
}

/**
 * Sicheres Escapen von HTML-Output
 * @param string $str
 * @return string
 */
function escape(string $str): string {
    return htmlspecialchars($str, ENT_QUOTES, 'UTF-8');
}
