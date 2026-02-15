<?php
/**
 * API: Konfiguration speichern
 */
header('Content-Type: application/json');

require_once '../config/database.php';
startSession();

if (!isLoggedIn()) {
    echo json_encode(['success' => false, 'error' => 'Nicht eingeloggt']);
    exit;
}

// POST-Daten lesen
$input = json_decode(file_get_contents('php://input'), true);

if (!$input) {
    echo json_encode(['success' => false, 'error' => 'Keine Daten übermittelt']);
    exit;
}

$userId = getUserId();
$configName = trim($input['name'] ?? 'Mein Drink');
$caffeine = $input['caffeine'] ?? null;
$sweetener = $input['sweetener'] ?? null;
$flavors = $input['flavors'] ?? [];
$additives = $input['additives'] ?? [];
$canColor = $input['canColor'] ?? '#00ff88';
$canName = $input['canName'] ?? 'Mein Drink';
$totalPrice = $input['totalPrice'] ?? 3.95;

// Validierung
if (!$caffeine || !$sweetener) {
    echo json_encode(['success' => false, 'error' => 'Unvollständige Konfiguration']);
    exit;
}

try {
    $pdo = getDbConnection();
    $pdo->beginTransaction();
    
    // Konfiguration speichern
    $stmt = $pdo->prepare("INSERT INTO configurations 
        (user_id, name, caffeine_level_id, sweetener_id, dosen_name, dosen_farbe, gesamtpreis) 
        VALUES (?, ?, ?, ?, ?, ?, ?)");
    $stmt->execute([
        $userId,
        $configName,
        $caffeine['id'],
        $sweetener['id'],
        $canName,
        $canColor,
        $totalPrice
    ]);
    
    $configId = $pdo->lastInsertId();
    
    // Flavors speichern
    $stmtFlavor = $pdo->prepare("INSERT INTO configuration_flavors (configuration_id, flavor_id) VALUES (?, ?)");
    foreach ($flavors as $flavor) {
        $stmtFlavor->execute([$configId, $flavor['id']]);
    }
    
    // Additives speichern
    $stmtAdditive = $pdo->prepare("INSERT INTO configuration_additives (configuration_id, additive_id) VALUES (?, ?)");
    foreach ($additives as $additive) {
        $stmtAdditive->execute([$configId, $additive['id']]);
    }
    
    $pdo->commit();
    
    echo json_encode([
        'success' => true,
        'configId' => $configId,
        'message' => 'Konfiguration erfolgreich gespeichert!'
    ]);
    
} catch (PDOException $e) {
    $pdo->rollBack();
    echo json_encode([
        'success' => false,
        'error' => 'Datenbankfehler: ' . $e->getMessage()
    ]);
}
