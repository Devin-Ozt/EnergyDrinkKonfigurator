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

$erfolg = 0;
$fehler = 0;
$fehlerMeldungen = [];

// Tabellen erstellen
$tables = [
    "CREATE TABLE IF NOT EXISTS users (
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
    )",
    
    "CREATE TABLE IF NOT EXISTS caffeine_levels (
        id INT AUTO_INCREMENT PRIMARY KEY,
        menge_mg INT NOT NULL,
        name VARCHAR(100) NOT NULL,
        beschreibung TEXT,
        preis DECIMAL(5,2) DEFAULT 0.00
    )",
    
    "CREATE TABLE IF NOT EXISTS sweeteners (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(100) NOT NULL,
        beschreibung TEXT,
        preis DECIMAL(5,2) DEFAULT 0.00,
        kalorien_pro_100ml INT DEFAULT 0
    )",
    
    "CREATE TABLE IF NOT EXISTS flavors (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(100) NOT NULL,
        beschreibung TEXT,
        preis DECIMAL(5,2) DEFAULT 0.00,
        ist_premium BOOLEAN DEFAULT FALSE,
        farbe VARCHAR(7) DEFAULT '#00FF00',
        kategorie VARCHAR(50),
        bild VARCHAR(255)
    )",
    
    "CREATE TABLE IF NOT EXISTS additives (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(100) NOT NULL,
        beschreibung TEXT,
        preis DECIMAL(5,2) DEFAULT 0.30,
        kategorie VARCHAR(50),
        icon VARCHAR(50)
    )",
    
    "CREATE TABLE IF NOT EXISTS configurations (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT NOT NULL,
        name VARCHAR(100) NOT NULL,
        caffeine_level_id INT NOT NULL,
        sweetener_id INT NOT NULL,
        groesse INT DEFAULT 300,
        dosen_name VARCHAR(50),
        dosen_farbe VARCHAR(7) DEFAULT '#00FF00',
        gesamtpreis DECIMAL(7,2) NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )",
    
    "CREATE TABLE IF NOT EXISTS configuration_flavors (
        id INT AUTO_INCREMENT PRIMARY KEY,
        configuration_id INT NOT NULL,
        flavor_id INT NOT NULL
    )",
    
    "CREATE TABLE IF NOT EXISTS configuration_additives (
        id INT AUTO_INCREMENT PRIMARY KEY,
        configuration_id INT NOT NULL,
        additive_id INT NOT NULL
    )",
    
    "CREATE TABLE IF NOT EXISTS coupons (
        id INT AUTO_INCREMENT PRIMARY KEY,
        code VARCHAR(50) NOT NULL UNIQUE,
        rabatt_prozent INT NOT NULL,
        gueltig_bis DATE,
        max_verwendungen INT DEFAULT NULL,
        aktuelle_verwendungen INT DEFAULT 0,
        aktiv BOOLEAN DEFAULT TRUE
    )",
    
    "CREATE TABLE IF NOT EXISTS presets (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(100) NOT NULL,
        beschreibung TEXT,
        caffeine_level_id INT NOT NULL,
        sweetener_id INT NOT NULL,
        dosen_farbe VARCHAR(7) DEFAULT '#00FF00',
        bild VARCHAR(255),
        beliebt BOOLEAN DEFAULT FALSE
    )",
    
    "CREATE TABLE IF NOT EXISTS preset_flavors (
        id INT AUTO_INCREMENT PRIMARY KEY,
        preset_id INT NOT NULL,
        flavor_id INT NOT NULL
    )",
    
    "CREATE TABLE IF NOT EXISTS preset_additives (
        id INT AUTO_INCREMENT PRIMARY KEY,
        preset_id INT NOT NULL,
        additive_id INT NOT NULL
    )"
];

foreach ($tables as $sql) {
    try {
        $pdo->exec($sql);
        $erfolg++;
    } catch (PDOException $e) {
        $fehler++;
        $fehlerMeldungen[] = $e->getMessage();
    }
}

// Prüfen ob Daten bereits existieren
$check = $pdo->query("SELECT COUNT(*) FROM caffeine_levels")->fetchColumn();

if ($check == 0) {
    // Beispieldaten einfügen
    $inserts = [
        // Koffein-Level
        "INSERT INTO caffeine_levels (menge_mg, name, beschreibung, preis) VALUES
        (50, 'Light', 'Leichter Energiekick für Einsteiger', 0.00),
        (100, 'Regular', 'Standard-Koffeingehalt wie ein Espresso', 0.00),
        (150, 'Strong', 'Für lange Gaming-Sessions', 0.30),
        (200, 'Extreme', 'Maximaler Fokus für Hardcore-Gamer', 0.50)",
        
        // Süßungsmittel
        "INSERT INTO sweeteners (name, beschreibung, preis, kalorien_pro_100ml) VALUES
        ('Zucker', 'Klassische Süße mit Zucker', 0.00, 45),
        ('Stevia', 'Natürliche Süße aus der Stevia-Pflanze', 0.00, 0),
        ('Erythrit', 'Zuckeralkohol ohne Kalorien', 0.20, 0),
        ('Zuckerfrei', 'Komplett ohne Süßungsmittel', 0.00, 0)",
        
        // Geschmacksrichtungen (22 Optionen)
        "INSERT INTO flavors (name, beschreibung, preis, ist_premium, farbe, kategorie) VALUES
        ('Tropical Thunder', 'Exotische Mango-Ananas-Kombi', 0.00, FALSE, '#FFD700', 'Fruchtig'),
        ('Berry Blast', 'Intensive Beerenmischung', 0.00, FALSE, '#8B008B', 'Fruchtig'),
        ('Citrus Storm', 'Erfrischende Zitrus-Explosion', 0.00, FALSE, '#FFA500', 'Fruchtig'),
        ('Watermelon Wave', 'Sommerliche Wassermelone', 0.00, FALSE, '#FF6B6B', 'Fruchtig'),
        ('Green Apple Rush', 'Knackiger grüner Apfel', 0.00, FALSE, '#32CD32', 'Fruchtig'),
        ('Grape Galaxy', 'Süße Weintrauben-Power', 0.00, FALSE, '#9370DB', 'Fruchtig'),
        ('Cherry Champion', 'Intensive Kirsche', 0.30, TRUE, '#DC143C', 'Fruchtig'),
        ('Peach Paradise', 'Saftige Pfirsich-Süße', 0.00, FALSE, '#FFDAB9', 'Fruchtig'),
        ('Lemon Lightning', 'Saure Zitronen-Frische', 0.00, FALSE, '#FFFF00', 'Fruchtig'),
        ('Orange Overdrive', 'Klassische Orange', 0.00, FALSE, '#FF8C00', 'Fruchtig'),
        ('Blue Raspberry Rage', 'Süß-saure blaue Himbeere', 0.30, TRUE, '#1E90FF', 'Premium'),
        ('Cotton Candy Cloud', 'Süße Zuckerwatte', 0.30, TRUE, '#FFB6C1', 'Premium'),
        ('Bubblegum Boost', 'Nostalgischer Kaugummi-Geschmack', 0.30, TRUE, '#FF69B4', 'Premium'),
        ('Cola Classic', 'Der Klassiker neu interpretiert', 0.00, FALSE, '#8B4513', 'Klassisch'),
        ('Vanilla Victory', 'Cremige Vanille', 0.30, TRUE, '#F5DEB3', 'Premium'),
        ('Mint Mayhem', 'Erfrischende Pfefferminze', 0.00, FALSE, '#98FB98', 'Frisch'),
        ('Ginger Gamer', 'Würziger Ingwer-Kick', 0.30, TRUE, '#DAA520', 'Würzig'),
        ('Passion Fruit Power', 'Exotische Passionsfrucht', 0.30, TRUE, '#FF1493', 'Exotisch'),
        ('Coconut Crush', 'Tropische Kokosnuss', 0.00, FALSE, '#FFFAF0', 'Exotisch'),
        ('Kiwi Kombat', 'Fruchtige Kiwi', 0.00, FALSE, '#9ACD32', 'Fruchtig'),
        ('Strawberry Strike', 'Süße Erdbeere', 0.00, FALSE, '#FF4500', 'Fruchtig'),
        ('Blackcurrant Blitz', 'Intensive schwarze Johannisbeere', 0.30, TRUE, '#4B0082', 'Premium')",
        
        // Funktionale Zusätze
        "INSERT INTO additives (name, beschreibung, preis, kategorie, icon) VALUES
        ('Vitamin B-Komplex', 'Unterstützt den Energiestoffwechsel', 0.30, 'Vitamine', 'vitamin'),
        ('Vitamin C', 'Stärkt das Immunsystem', 0.30, 'Vitamine', 'vitamin'),
        ('Vitamin D', 'Das Sonnenvitamin', 0.30, 'Vitamine', 'vitamin'),
        ('Taurin', 'Klassischer Energy-Zusatz', 0.30, 'Aminosäuren', 'bolt'),
        ('Guarana', 'Natürliche Koffein-Quelle', 0.30, 'Pflanzlich', 'leaf'),
        ('L-Theanin', 'Für fokussierte Entspannung', 0.30, 'Aminosäuren', 'brain'),
        ('Ginseng', 'Traditionelle Heilpflanze', 0.30, 'Pflanzlich', 'leaf'),
        ('BCAA', 'Verzweigtkettige Aminosäuren', 0.50, 'Aminosäuren', 'dumbbell'),
        ('Elektrolyte', 'Für optimale Hydration', 0.30, 'Mineralien', 'droplet'),
        ('Zink', 'Wichtig für das Immunsystem', 0.30, 'Mineralien', 'shield')",
        
        // Gutschein-Codes
        "INSERT INTO coupons (code, rabatt_prozent, gueltig_bis, max_verwendungen, aktiv) VALUES
        ('WELCOME10', 10, '2026-12-31', 1000, TRUE),
        ('GAMER20', 20, '2026-06-30', 500, TRUE),
        ('ENERGY15', 15, '2026-03-31', 200, TRUE),
        ('STREAM25', 25, '2026-12-31', 100, TRUE)",
        
        // Vorkonfigurierte Presets
        "INSERT INTO presets (name, beschreibung, caffeine_level_id, sweetener_id, dosen_farbe, beliebt) VALUES
        ('Pro Gamer', 'Maximale Konzentration für E-Sports', 4, 3, '#FF0000', TRUE),
        ('Night Owl', 'Für lange Nächte am PC', 3, 2, '#1E1E1E', TRUE),
        ('Fitness Boost', 'Power für dein Workout', 2, 4, '#00FF00', FALSE),
        ('Tropical Chill', 'Entspannt durch den Tag', 1, 1, '#00CED1', TRUE),
        ('Berry Focus', 'Konzentration mit Beerengeschmack', 2, 2, '#8B008B', FALSE)",
        
        // Preset-Flavors
        "INSERT INTO preset_flavors (preset_id, flavor_id) VALUES
        (1, 11), (1, 14),
        (2, 3), (2, 16),
        (3, 1), (3, 8),
        (4, 1), (4, 19),
        (5, 2), (5, 21)",
        
        // Preset-Additives
        "INSERT INTO preset_additives (preset_id, additive_id) VALUES
        (1, 4), (1, 6), (1, 1),
        (2, 5), (2, 6),
        (3, 8), (3, 9), (3, 1),
        (4, 2), (4, 9),
        (5, 1), (5, 6)"
    ];
    
    foreach ($inserts as $sql) {
        try {
            $pdo->exec($sql);
            $erfolg++;
        } catch (PDOException $e) {
            if (strpos($e->getMessage(), 'Duplicate') === false) {
                $fehler++;
                $fehlerMeldungen[] = $e->getMessage();
            }
        }
    }
}

?>
<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Installation - Energy Pulver Konfigurator</title>
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
                    <h1 class="mb-4">⚡ Energy Pulver Konfigurator</h1>
                    <h2 class="mb-4">Installation</h2>
                    
                    <?php if ($fehler === 0): ?>
                        <div class="alert alert-success">
                            <h4>✅ Installation erfolgreich!</h4>
                            <p><?= $erfolg ?> Operationen wurden ausgeführt.</p>
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
