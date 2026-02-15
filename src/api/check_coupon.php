<?php
/**
 * API: Gutschein prüfen
 */
header('Content-Type: application/json');

require_once '../config/database.php';

// POST-Daten lesen
$input = json_decode(file_get_contents('php://input'), true);
$code = strtoupper(trim($input['code'] ?? ''));

if (empty($code)) {
    echo json_encode(['success' => false, 'error' => 'Kein Code angegeben']);
    exit;
}

try {
    $pdo = getDbConnection();
    
    $stmt = $pdo->prepare("
        SELECT * FROM coupons 
        WHERE code = ? 
        AND aktiv = 1 
        AND (gueltig_bis IS NULL OR gueltig_bis >= CURDATE())
        AND (max_verwendungen IS NULL OR aktuelle_verwendungen < max_verwendungen)
    ");
    $stmt->execute([$code]);
    $coupon = $stmt->fetch();
    
    if ($coupon) {
        echo json_encode([
            'success' => true,
            'discount' => (int)$coupon['rabatt_prozent'],
            'message' => $coupon['rabatt_prozent'] . '% Rabatt angewendet!'
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'error' => 'Ungültiger oder abgelaufener Gutscheincode'
        ]);
    }
    
} catch (PDOException $e) {
    echo json_encode([
        'success' => false,
        'error' => 'Datenbankfehler'
    ]);
}
