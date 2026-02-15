<?php
/**
 * API: Konfiguration laden und zum Konfigurator weiterleiten
 */
require_once '../config/database.php';
startSession();

if (!isLoggedIn()) {
    header('Location: ../login.php');
    exit;
}

$configId = (int)($_GET['id'] ?? 0);
$userId = getUserId();

if (!$configId) {
    header('Location: ../meine-drinks.php');
    exit;
}

try {
    $pdo = getDbConnection();
    
    // Konfiguration laden
    $stmt = $pdo->prepare("
        SELECT c.*, cl.menge_mg, cl.preis as caffeine_price, cl.name as caffeine_name,
               s.name as sweetener_name, s.preis as sweetener_price
        FROM configurations c
        JOIN caffeine_levels cl ON c.caffeine_level_id = cl.id
        JOIN sweeteners s ON c.sweetener_id = s.id
        WHERE c.id = ? AND c.user_id = ?
    ");
    $stmt->execute([$configId, $userId]);
    $config = $stmt->fetch();
    
    if (!$config) {
        header('Location: ../meine-drinks.php');
        exit;
    }
    
    // Flavors laden
    $stmt = $pdo->prepare("
        SELECT f.id, f.name, f.preis as price, f.farbe as color
        FROM configuration_flavors cf
        JOIN flavors f ON cf.flavor_id = f.id
        WHERE cf.configuration_id = ?
    ");
    $stmt->execute([$configId]);
    $flavors = $stmt->fetchAll();
    
    // Additives laden
    $stmt = $pdo->prepare("
        SELECT a.id, a.name, a.preis as price
        FROM configuration_additives ca
        JOIN additives a ON ca.additive_id = a.id
        WHERE ca.configuration_id = ?
    ");
    $stmt->execute([$configId]);
    $additives = $stmt->fetchAll();
    
    // Preise berechnen
    $flavorsPrice = array_sum(array_column($flavors, 'price'));
    $additivesPrice = array_sum(array_column($additives, 'price'));
    
    // Size-Preisliste
    $sizePrices = [300 => 14.95, 400 => 18.95, 500 => 22.95];
    $size = (int)($config['groesse'] ?? 300);
    $sizePrice = $sizePrices[$size] ?? 14.95;
    
    // Config-Daten für die Zusammenfassungsseite vorbereiten
    $configData = [
        'size' => $size,
        'caffeine' => [
            'id' => $config['caffeine_level_id'],
            'name' => $config['caffeine_name'],
            'price' => (float)$config['caffeine_price'],
            'mg' => $config['menge_mg']
        ],
        'flavors' => $flavors,
        'additives' => $additives,
        'sweetener' => [
            'id' => $config['sweetener_id'],
            'name' => $config['sweetener_name'],
            'price' => (float)$config['sweetener_price']
        ],
        'canColor' => $config['dosen_farbe'],
        'canName' => $config['dosen_name'],
        'prices' => [
            'size' => $sizePrice,
            'caffeine' => (float)$config['caffeine_price'],
            'flavors' => $flavorsPrice,
            'additives' => $additivesPrice,
            'sweetener' => (float)$config['sweetener_price'],
            'discount' => 0,
            'total' => (float)$config['gesamtpreis']
        ]
    ];
    
    // In Session speichern und zur Zusammenfassung weiterleiten
    $_SESSION['config_data'] = $configData;
    header('Location: ../summary.php');
    exit;
    
} catch (PDOException $e) {
    header('Location: ../meine-drinks.php');
    exit;
}
