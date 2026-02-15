<?php
/**
 * Meine gespeicherten Drinks
 */
require_once 'config/database.php';
startSession();

// Login erforderlich
if (!isLoggedIn()) {
    header('Location: login.php?redirect=meine-drinks.php');
    exit;
}

$userId = getUserId();
$configurations = [];

// Löschen wenn angefordert
if (isset($_GET['delete'])) {
    $deleteId = (int)$_GET['delete'];
    try {
        $pdo = getDbConnection();
        $stmt = $pdo->prepare("DELETE FROM configurations WHERE id = ? AND user_id = ?");
        $stmt->execute([$deleteId, $userId]);
    } catch (PDOException $e) {
        // Fehler ignorieren
    }
    header('Location: meine-drinks.php');
    exit;
}

// Konfigurationen laden
try {
    $pdo = getDbConnection();
    
    $stmt = $pdo->prepare("
        SELECT c.*, cl.name as caffeine_name, cl.menge_mg, s.name as sweetener_name
        FROM configurations c
        JOIN caffeine_levels cl ON c.caffeine_level_id = cl.id
        JOIN sweeteners s ON c.sweetener_id = s.id
        WHERE c.user_id = ?
        ORDER BY c.created_at DESC
    ");
    $stmt->execute([$userId]);
    $configurations = $stmt->fetchAll();
    
    // Flavors und Additives für jede Konfiguration laden
    foreach ($configurations as &$config) {
        // Flavors
        $stmt = $pdo->prepare("
            SELECT f.name, f.farbe
            FROM configuration_flavors cf
            JOIN flavors f ON cf.flavor_id = f.id
            WHERE cf.configuration_id = ?
        ");
        $stmt->execute([$config['id']]);
        $config['flavors'] = $stmt->fetchAll();
        
        // Additives
        $stmt = $pdo->prepare("
            SELECT a.name
            FROM configuration_additives ca
            JOIN additives a ON ca.additive_id = a.id
            WHERE ca.configuration_id = ?
        ");
        $stmt->execute([$config['id']]);
        $config['additives'] = $stmt->fetchAll();
    }
    
} catch (PDOException $e) {
    $configurations = [];
}
?>
<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Meine Drinks - Energy Drink Konfigurator</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
    <link href="css/style.css" rel="stylesheet">
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark fixed-top">
        <div class="container">
            <a class="navbar-brand" href="index.php">
                <i class="bi bi-lightning-charge-fill text-warning"></i> 
                <span class="brand-text">ENERGY<span class="text-neon">MIX</span></span>
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="konfigurator.php">Konfigurator</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link active" href="meine-drinks.php">Meine Drinks</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="logout.php">
                            <i class="bi bi-box-arrow-right"></i> Logout (<?= escape(getUserName()) ?>)
                        </a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="drinks-container">
        <div class="container py-5">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1><i class="bi bi-collection"></i> Meine Drinks</h1>
                <a href="konfigurator.php" class="btn btn-neon">
                    <i class="bi bi-plus-lg"></i> Neuer Drink
                </a>
            </div>
            
            <?php if (empty($configurations)): ?>
                <div class="text-center py-5">
                    <i class="bi bi-cup-straw text-muted" style="font-size: 5rem;"></i>
                    <h3 class="mt-4">Noch keine Drinks gespeichert</h3>
                    <p class="text-muted">Erstelle deinen ersten individuellen Energy Drink!</p>
                    <a href="konfigurator.php" class="btn btn-neon btn-lg mt-3">
                        <i class="bi bi-lightning-charge"></i> Jetzt konfigurieren
                    </a>
                </div>
            <?php else: ?>
                <div class="row g-4">
                    <?php foreach ($configurations as $config): ?>
                    <div class="col-md-6 col-lg-4">
                        <div class="drink-card">
                            <div class="drink-header">
                                <div class="drink-mini-can" style="background: <?= escape($config['dosen_farbe']) ?>; border-radius: 5px;"></div>
                                <div>
                                    <h5 class="mb-0"><?= escape($config['name']) ?></h5>
                                    <small class="text-muted">
                                        <?= date('d.m.Y', strtotime($config['created_at'])) ?>
                                    </small>
                                </div>
                            </div>
                            <div class="drink-body">
                                <div class="mb-2">
                                    <span class="badge bg-warning text-dark">
                                        <i class="bi bi-lightning"></i> <?= $config['menge_mg'] ?>mg
                                    </span>
                                    <span class="badge bg-secondary">
                                        <?= escape($config['sweetener_name']) ?>
                                    </span>
                                </div>
                                <div class="mb-2">
                                    <small class="text-muted">Geschmack:</small><br>
                                    <?php foreach ($config['flavors'] as $flavor): ?>
                                        <span class="badge me-1" style="background: <?= escape($flavor['farbe']) ?>">
                                            <?= escape($flavor['name']) ?>
                                        </span>
                                    <?php endforeach; ?>
                                </div>
                                <?php if (!empty($config['additives'])): ?>
                                <div>
                                    <small class="text-muted">Zusätze:</small><br>
                                    <?php foreach ($config['additives'] as $additive): ?>
                                        <span class="badge bg-info me-1"><?= escape($additive['name']) ?></span>
                                    <?php endforeach; ?>
                                </div>
                                <?php endif; ?>
                            </div>
                            <div class="drink-footer">
                                <span class="text-neon fw-bold"><?= formatPrice($config['gesamtpreis']) ?></span>
                                <div>
                                    <a href="api/load_config.php?id=<?= $config['id'] ?>" class="btn btn-sm btn-outline-neon">
                                        <i class="bi bi-pencil"></i> Bearbeiten
                                    </a>
                                    <a href="meine-drinks.php?delete=<?= $config['id'] ?>" 
                                       class="btn btn-sm btn-outline-danger"
                                       onclick="return confirm('Wirklich löschen?')">
                                        <i class="bi bi-trash"></i>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
