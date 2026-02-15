<?php
/**
 * API: Presets laden
 */
header('Content-Type: application/json');

require_once '../config/database.php';

try {
    $pdo = getDbConnection();
    
    // Presets mit allen Details laden
    $stmt = $pdo->query("
        SELECT p.*, cl.menge_mg, cl.name as caffeine_name, s.name as sweetener_name
        FROM presets p
        JOIN caffeine_levels cl ON p.caffeine_level_id = cl.id
        JOIN sweeteners s ON p.sweetener_id = s.id
        ORDER BY p.beliebt DESC, p.name
    ");
    $presets = $stmt->fetchAll();
    
    // Für jedes Preset die Flavors und Additives laden
    foreach ($presets as &$preset) {
        // Flavors
        $stmt = $pdo->prepare("
            SELECT f.id, f.name, f.farbe
            FROM preset_flavors pf
            JOIN flavors f ON pf.flavor_id = f.id
            WHERE pf.preset_id = ?
        ");
        $stmt->execute([$preset['id']]);
        $preset['flavors'] = $stmt->fetchAll();
        
        // Additives
        $stmt = $pdo->prepare("
            SELECT a.id, a.name
            FROM preset_additives pa
            JOIN additives a ON pa.additive_id = a.id
            WHERE pa.preset_id = ?
        ");
        $stmt->execute([$preset['id']]);
        $preset['additives'] = $stmt->fetchAll();
    }
    
    echo json_encode([
        'success' => true,
        'presets' => $presets
    ]);
    
} catch (PDOException $e) {
    echo json_encode([
        'success' => false,
        'error' => 'Datenbankfehler: ' . $e->getMessage()
    ]);
}
