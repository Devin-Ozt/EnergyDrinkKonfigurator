<?php
/**
 * Installationsskript - Erstellt alle Datenbanktabellen
 */

// Versuche Verbindung ohne Datenbank
try {
    $pdo = new PDO("mysql:host=db;charset=utf8mb4", "benutzer", "benutzerpasswort", [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
    ]);
} catch (PDOException $e) {
    die("<h2>Fehler:</h2><p>Kann keine Verbindung zur Datenbank herstellen. Ist Docker gestartet?</p><pre>" . $e->getMessage() . "</pre>");
}

// Datenbank erstellen falls nicht vorhanden
$pdo->exec("CREATE DATABASE IF NOT EXISTS meine_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
$pdo->exec("USE meine_db");

// Versuche SQL aus verschiedenen Pfaden zu laden
$sqlPaths = [
    __DIR__ . '/../database_dump.sql',
    __DIR__ . '/database_dump.sql',
    '/var/www/html/../database_dump.sql'
];

$sql = false;
foreach ($sqlPaths as $path) {
    if (file_exists($path)) {
        $sql = file_get_contents($path);
        break;
    }
}

if ($sql === false) {
    // Falls die SQL-Datei nicht im übergeordneten Ordner ist, SQL direkt ausführen
    $sql = "
    -- Benutzer-Tabelle
    CREATE TABLE IF NOT EXISTS users (
        id INT AUTO_INCREMENT PRIMARY KEY,
        vorname VARCHAR(100) NOT NULL,
        nachname VARCHAR(100) NOT NULL,
        email VARCHAR(255) NOT NULL UNIQUE,
        passwort VARCHAR(255) NOT NULL,
        strasse VARCHAR(255),
        hausnummer VARCHAR(20),
        plz VARCHAR(10),
        ort VARCHAR(100),
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    );

    -- Koffein-Level
    CREATE TABLE IF NOT EXISTS caffeine_levels (
        id INT AUTO_INCREMENT PRIMARY KEY,
        menge_mg INT NOT NULL,
        name VARCHAR(100) NOT NULL,
        beschreibung TEXT,
        preis DECIMAL(5,2) DEFAULT 0.00
    );

    -- Süßungsmittel
    CREATE TABLE IF NOT EXISTS sweeteners (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(100) NOT NULL,
        beschreibung TEXT,
        preis DECIMAL(5,2) DEFAULT 0.00,
        kalorien_pro_100ml INT DEFAULT 0
    );

    -- Geschmacksrichtungen
    CREATE TABLE IF NOT EXISTS flavors (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(100) NOT NULL,
        beschreibung TEXT,
        preis DECIMAL(5,2) DEFAULT 0.00,
        ist_premium BOOLEAN DEFAULT FALSE,
        farbe VARCHAR(7) DEFAULT '#00FF00',
        kategorie VARCHAR(50),
        bild VARCHAR(255)
    );

    -- Funktionale Zusätze
    CREATE TABLE IF NOT EXISTS additives (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(100) NOT NULL,
        beschreibung TEXT,
        preis DECIMAL(5,2) DEFAULT 0.30,
        kategorie VARCHAR(50),
        icon VARCHAR(50)
    );

    -- Gespeicherte Konfigurationen
    CREATE TABLE IF NOT EXISTS configurations (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT NOT NULL,
        name VARCHAR(100) NOT NULL,
        caffeine_level_id INT NOT NULL,
        sweetener_id INT NOT NULL,
        dosen_name VARCHAR(50),
        dosen_farbe VARCHAR(7) DEFAULT '#00FF00',
        gesamtpreis DECIMAL(7,2) NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
        FOREIGN KEY (caffeine_level_id) REFERENCES caffeine_levels(id),
        FOREIGN KEY (sweetener_id) REFERENCES sweeteners(id)
    );

    -- Gewählte Geschmacksrichtungen pro Konfiguration
    CREATE TABLE IF NOT EXISTS configuration_flavors (
        id INT AUTO_INCREMENT PRIMARY KEY,
        configuration_id INT NOT NULL,
        flavor_id INT NOT NULL,
        FOREIGN KEY (configuration_id) REFERENCES configurations(id) ON DELETE CASCADE,
        FOREIGN KEY (flavor_id) REFERENCES flavors(id)
    );

    -- Gewählte Zusätze pro Konfiguration
    CREATE TABLE IF NOT EXISTS configuration_additives (
        id INT AUTO_INCREMENT PRIMARY KEY,
        configuration_id INT NOT NULL,
        additive_id INT NOT NULL,
        FOREIGN KEY (configuration_id) REFERENCES configurations(id) ON DELETE CASCADE,
        FOREIGN KEY (additive_id) REFERENCES additives(id)
    );

    -- Gutschein-Codes
    CREATE TABLE IF NOT EXISTS coupons (
        id INT AUTO_INCREMENT PRIMARY KEY,
        code VARCHAR(50) NOT NULL UNIQUE,
        rabatt_prozent INT NOT NULL,
        gueltig_bis DATE,
        max_verwendungen INT DEFAULT NULL,
        aktuelle_verwendungen INT DEFAULT 0,
        aktiv BOOLEAN DEFAULT TRUE
    );

    -- Vorkonfigurierte Presets
    CREATE TABLE IF NOT EXISTS presets (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(100) NOT NULL,
        beschreibung TEXT,
        caffeine_level_id INT NOT NULL,
        sweetener_id INT NOT NULL,
        dosen_farbe VARCHAR(7) DEFAULT '#00FF00',
        bild VARCHAR(255),
        beliebt BOOLEAN DEFAULT FALSE,
        FOREIGN KEY (caffeine_level_id) REFERENCES caffeine_levels(id),
        FOREIGN KEY (sweetener_id) REFERENCES sweeteners(id)
    );

    -- Preset Geschmacksrichtungen
    CREATE TABLE IF NOT EXISTS preset_flavors (
        id INT AUTO_INCREMENT PRIMARY KEY,
        preset_id INT NOT NULL,
        flavor_id INT NOT NULL,
        FOREIGN KEY (preset_id) REFERENCES presets(id) ON DELETE CASCADE,
        FOREIGN KEY (flavor_id) REFERENCES flavors(id)
    );

    -- Preset Zusätze
    CREATE TABLE IF NOT EXISTS preset_additives (
        id INT AUTO_INCREMENT PRIMARY KEY,
        preset_id INT NOT NULL,
        additive_id INT NOT NULL,
        FOREIGN KEY (preset_id) REFERENCES presets(id) ON DELETE CASCADE,
        FOREIGN KEY (additive_id) REFERENCES additives(id)
    );
    ";
}

// SQL in einzelne Statements aufteilen und ausführen
$statements = array_filter(
    array_map('trim', explode(';', $sql)),
    fn($stmt) => !empty($stmt) && strpos($stmt, '--') !== 0
);

$erfolg = 0;
$fehler = 0;
$fehlerMeldungen = [];

foreach ($statements as $statement) {
    // Kommentare überspringen
    if (empty(trim($statement)) || strpos(trim($statement), '--') === 0) {
        continue;
    }
    
    try {
        $pdo->exec($statement);
        $erfolg++;
    } catch (PDOException $e) {
        // Duplicate entry und ähnliche Fehler ignorieren
        if (strpos($e->getMessage(), 'Duplicate entry') === false && 
            strpos($e->getMessage(), 'already exists') === false) {
            $fehler++;
            $fehlerMeldungen[] = $e->getMessage();
        }
    }
}

?>
<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Installation - Energy Drink Konfigurator</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #1a1a2e 0%, #16213e 100%);
            min-height: 100vh;
            color: #fff;
        }
        .card {
            background: rgba(255,255,255,0.1);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255,255,255,0.2);
        }
        .btn-neon {
            background: linear-gradient(45deg, #00ff88, #00ccff);
            border: none;
            color: #000;
            font-weight: bold;
        }
    </style>
</head>
<body>
    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card p-5 text-center">
                    <h1 class="mb-4">⚡ Energy Drink Konfigurator</h1>
                    <h2 class="mb-4">Installation</h2>
                    
                    <?php if ($fehler === 0): ?>
                        <div class="alert alert-success">
                            <h4>✅ Installation erfolgreich!</h4>
                            <p><?= $erfolg ?> SQL-Statements wurden ausgeführt.</p>
                        </div>
                        <a href="index.php" class="btn btn-neon btn-lg mt-3">Zur Startseite</a>
                    <?php else: ?>
                        <div class="alert alert-warning">
                            <h4>⚠️ Installation teilweise abgeschlossen</h4>
                            <p><?= $erfolg ?> erfolgreich, <?= $fehler ?> mit Fehlern</p>
                            <?php if (!empty($fehlerMeldungen)): ?>
                                <details>
                                    <summary>Fehlermeldungen anzeigen</summary>
                                    <ul class="text-start">
                                        <?php foreach ($fehlerMeldungen as $msg): ?>
                                            <li><?= htmlspecialchars($msg) ?></li>
                                        <?php endforeach; ?>
                                    </ul>
                                </details>
                            <?php endif; ?>
                        </div>
                        <a href="index.php" class="btn btn-neon btn-lg mt-3">Trotzdem zur Startseite</a>
                    <?php endif; ?>
                    
                    <div class="mt-4">
                        <h5>Test-Gutscheincodes:</h5>
                        <ul class="list-unstyled">
                            <li><code>WELCOME10</code> - 10% Rabatt</li>
                            <li><code>GAMER20</code> - 20% Rabatt</li>
                            <li><code>ENERGY15</code> - 15% Rabatt</li>
                            <li><code>STREAM25</code> - 25% Rabatt</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
