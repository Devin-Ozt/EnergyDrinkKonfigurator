<?php
/**
 * Zusammenfassung der Konfiguration
 */
require_once 'config/database.php';
startSession();

// Konfigurationsdaten prüfen
if (!isset($_POST['config_data']) && !isset($_SESSION['config_data'])) {
    header('Location: konfigurator.php');
    exit;
}

// Daten aus POST oder Session
$configData = isset($_POST['config_data']) 
    ? json_decode($_POST['config_data'], true) 
    : $_SESSION['config_data'];

// In Session speichern für spätere Verwendung
$_SESSION['config_data'] = $configData;

// Validierung
if (!$configData || !isset($configData['caffeine']) || !isset($configData['sweetener'])) {
    header('Location: konfigurator.php');
    exit;
}

$caffeine = $configData['caffeine'];
$flavors = $configData['flavors'] ?? [];
$additives = $configData['additives'] ?? [];
$sweetener = $configData['sweetener'];
$size = $configData['size'] ?? 300;
$canColor = $configData['canColor'] ?? '#00ff88';
$canName = $configData['canName'] ?? 'Mein Mix';
$prices = $configData['prices'];

// Speichern-Nachricht
$saveMessage = '';
$saveError = '';

// Konfiguration speichern wenn angefordert
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_config'])) {
    if (!isLoggedIn()) {
        $saveError = 'Du musst eingeloggt sein, um die Konfiguration zu speichern.';
    } else {
        try {
            $pdo = getDbConnection();
            $userId = getUserId();
            $configName = trim($_POST['config_name'] ?? $canName);
            
            // Konfiguration speichern
            $stmt = $pdo->prepare("INSERT INTO configurations 
                (user_id, name, caffeine_level_id, sweetener_id, groesse, dosen_name, dosen_farbe, gesamtpreis) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([
                $userId,
                $configName,
                $caffeine['id'],
                $sweetener['id'],
                $size,
                $canName,
                $canColor,
                $prices['total']
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
            
            $saveMessage = 'Konfiguration erfolgreich gespeichert!';
            
        } catch (PDOException $e) {
            $saveError = 'Fehler beim Speichern: ' . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Zusammenfassung - Energy Pulver Dose Konfigurator</title>
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
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="konfigurator.php">Konfigurator</a>
                    </li>
                    <?php if (isLoggedIn()): ?>
                        <li class="nav-item">
                            <a class="nav-link" href="meine-drinks.php">Meine Mixes</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="logout.php">Logout</a>
                        </li>
                    <?php else: ?>
                        <li class="nav-item">
                            <a class="nav-link" href="login.php">Login</a>
                        </li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </nav>

    <div class="summary-container">
        <div class="container py-5">
            <h1 class="text-center mb-5">
                <i class="bi bi-check-circle text-success"></i> 
                Deine Konfiguration
            </h1>
            
            <?php if ($saveMessage): ?>
                <div class="alert alert-success text-center mb-4">
                    <i class="bi bi-check-circle"></i> <?= escape($saveMessage) ?>
                    <br><a href="meine-drinks.php">Zu meinen Mixes</a>
                </div>
            <?php endif; ?>
            
            <?php if ($saveError): ?>
                <div class="alert alert-danger text-center mb-4">
                    <i class="bi bi-exclamation-circle"></i> <?= escape($saveError) ?>
                </div>
            <?php endif; ?>
            
            <div class="row">
                <!-- Linke Seite: Details -->
                <div class="col-lg-7">
                    <div class="summary-card">
                        <h3 class="mb-4"><?= escape($canName) ?></h3>
                        
                        <!-- Dosengröße -->
                        <div class="summary-section">
                            <h4><i class="bi bi-box-seam"></i> Dosengröße</h4>
                            <div class="summary-items">
                                <span class="summary-item">
                                    <?= $size ?>g Dose (~<?= round($size / 15) ?> Portionen)
                                </span>
                            </div>
                        </div>
                        
                        <!-- Koffein -->
                        <div class="summary-section">
                            <h4><i class="bi bi-lightning-charge"></i> Koffein-Level</h4>
                            <div class="summary-items">
                                <span class="summary-item">
                                    <?= escape($caffeine['name']) ?> (<?= $caffeine['mg'] ?>mg)
                                </span>
                            </div>
                        </div>
                        
                        <!-- Geschmack -->
                        <div class="summary-section">
                            <h4><i class="bi bi-palette"></i> Geschmacksrichtungen</h4>
                            <div class="summary-items">
                                <?php foreach ($flavors as $flavor): ?>
                                    <span class="summary-item" style="border-left: 3px solid <?= escape($flavor['color']) ?>">
                                        <?= escape($flavor['name']) ?>
                                        <?php if ($flavor['price'] > 0): ?>
                                            <small class="text-warning">(+<?= formatPrice($flavor['price']) ?>)</small>
                                        <?php endif; ?>
                                    </span>
                                <?php endforeach; ?>
                            </div>
                        </div>
                        
                        <!-- Zusätze -->
                        <?php if (!empty($additives)): ?>
                        <div class="summary-section">
                            <h4><i class="bi bi-capsule"></i> Funktionale Zusätze</h4>
                            <div class="summary-items">
                                <?php foreach ($additives as $additive): ?>
                                    <span class="summary-item">
                                        <?= escape($additive['name']) ?>
                                        <small class="text-warning">(+<?= formatPrice($additive['price']) ?>)</small>
                                    </span>
                                <?php endforeach; ?>
                            </div>
                        </div>
                        <?php endif; ?>
                        
                        <!-- Süßung -->
                        <div class="summary-section">
                            <h4><i class="bi bi-droplet"></i> Süßungsmittel</h4>
                            <div class="summary-items">
                                <span class="summary-item"><?= escape($sweetener['name']) ?></span>
                            </div>
                        </div>
                        
                        <!-- Preis-Übersicht -->
                        <div class="summary-total">
                            <div class="row">
                                <div class="col-md-6">
                                    <table class="table table-borderless text-start mb-0 text-white">
                                        <tr>
                                            <td>Dose (<?= $size ?>g):</td>
                                            <td class="text-end"><?= formatPrice($prices['size'] ?? $prices['base'] ?? 14.95) ?></td>
                                        </tr>
                                        <?php if ($prices['caffeine'] > 0): ?>
                                        <tr>
                                            <td>Koffein-Upgrade:</td>
                                            <td class="text-end">+<?= formatPrice($prices['caffeine']) ?></td>
                                        </tr>
                                        <?php endif; ?>
                                        <?php if ($prices['flavors'] > 0): ?>
                                        <tr>
                                            <td>Premium-Flavors:</td>
                                            <td class="text-end">+<?= formatPrice($prices['flavors']) ?></td>
                                        </tr>
                                        <?php endif; ?>
                                        <?php if ($prices['additives'] > 0): ?>
                                        <tr>
                                            <td>Zusätze:</td>
                                            <td class="text-end">+<?= formatPrice($prices['additives']) ?></td>
                                        </tr>
                                        <?php endif; ?>
                                        <?php if ($prices['sweetener'] > 0): ?>
                                        <tr>
                                            <td>Süßungsmittel:</td>
                                            <td class="text-end">+<?= formatPrice($prices['sweetener']) ?></td>
                                        </tr>
                                        <?php endif; ?>
                                        <?php if ($prices['discount'] > 0): ?>
                                        <tr class="text-success">
                                            <td>Rabatt (<?= $prices['discount'] ?>%):</td>
                                            <td class="text-end">
                                                -<?= formatPrice((($prices['size'] ?? $prices['base'] ?? 14.95) + $prices['caffeine'] + $prices['flavors'] + $prices['additives'] + $prices['sweetener']) * $prices['discount'] / 100) ?>
                                            </td>
                                        </tr>
                                        <?php endif; ?>
                                    </table>
                                </div>
                                <div class="col-md-6 d-flex align-items-center justify-content-center">
                                    <div>
                                        <small class="d-block" style="color: #b8b8cc;">Gesamtpreis</small>
                                        <span class="summary-price"><?= formatPrice($prices['total']) ?></span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Aktionen -->
                    <div class="mt-4 d-flex gap-3 flex-wrap justify-content-center">
                        <a href="konfigurator.php" class="btn btn-outline-light btn-lg">
                            <i class="bi bi-arrow-left"></i> Zurück zum Konfigurator
                        </a>
                        
                        <?php if (isLoggedIn()): ?>
                            <button class="btn btn-outline-neon btn-lg" data-bs-toggle="modal" data-bs-target="#saveModal">
                                <i class="bi bi-save"></i> Speichern
                            </button>
                        <?php else: ?>
                            <a href="login.php?redirect=summary.php" class="btn btn-outline-neon btn-lg">
                                <i class="bi bi-save"></i> Einloggen & Speichern
                            </a>
                        <?php endif; ?>
                        
                        <button class="btn btn-neon btn-lg" data-bs-toggle="modal" data-bs-target="#orderModal">
                            <i class="bi bi-cart"></i> Jetzt bestellen
                        </button>
                    </div>
                </div>
                
                <!-- Rechte Seite: Vorschau -->
                <div class="col-lg-5">
                    <div class="summary-can-preview">
                        <div class="can-preview-card">
                            <h5>Deine Dose (<?= $size ?>g)</h5>
                            <svg viewBox="0 0 140 200" style="width: 200px; height: 280px; filter: drop-shadow(0 0 30px <?= escape($canColor) ?>80);">
                                <defs>
                                    <linearGradient id="summaryGradient" x1="0%" y1="0%" x2="100%" y2="100%">
                                        <stop offset="0%" style="stop-color:<?= escape($canColor) ?>;stop-opacity:1" />
                                        <stop offset="50%" style="stop-color:<?= escape(adjustColor($canColor, 30)) ?>;stop-opacity:1" />
                                        <stop offset="100%" style="stop-color:<?= escape($canColor) ?>;stop-opacity:1" />
                                    </linearGradient>
                                    <linearGradient id="summaryLidGrad" x1="0%" y1="0%" x2="0%" y2="100%">
                                        <stop offset="0%" style="stop-color:#e0e0e0;stop-opacity:1" />
                                        <stop offset="100%" style="stop-color:#a0a0a0;stop-opacity:1" />
                                    </linearGradient>
                                </defs>
                                <!-- Deckel oben -->
                                <ellipse cx="70" cy="22" rx="48" ry="12" fill="url(#summaryLidGrad)" />
                                <rect x="22" y="15" width="96" height="12" fill="#c0c0c0" />
                                <ellipse cx="70" cy="15" rx="48" ry="12" fill="#d0d0d0" />
                                <!-- Griff auf Deckel -->
                                <rect x="55" y="8" width="30" height="5" rx="2" fill="#b0b0b0" />
                                <!-- Dosen-Körper (Zylinder) -->
                                <rect x="22" y="22" width="96" height="145" fill="url(#summaryGradient)" />
                                <!-- Boden-Ellipse -->
                                <ellipse cx="70" cy="167" rx="48" ry="12" fill="url(#summaryGradient)" opacity="0.8" />
                                <!-- Label -->
                                <rect x="28" y="50" width="84" height="100" fill="rgba(0,0,0,0.3)" rx="5" />
                                <text x="70" y="78" text-anchor="middle" fill="#fff" font-size="10" font-weight="bold">ENERGY</text>
                                <text x="70" y="96" text-anchor="middle" fill="#fff" font-size="13" font-weight="bold">MIX</text>
                                <text x="70" y="116" text-anchor="middle" fill="#fff" font-size="7"><?= escape(strtoupper($canName)) ?></text>
                                <text x="70" y="130" text-anchor="middle" fill="#fff" font-size="6"><?= $caffeine['mg'] ?>mg KOFFEIN</text>
                                <text x="70" y="143" text-anchor="middle" fill="#fff" font-size="6"><?= $size ?>g</text>
                                <!-- Glanz-Effekt -->
                                <rect x="22" y="22" width="15" height="145" fill="rgba(255,255,255,0.08)" />
                            </svg>
                            
                            <div class="mt-4">
                                <h6>Geschmacksmix:</h6>
                                <div class="d-flex justify-content-center gap-2 flex-wrap">
                                    <?php foreach ($flavors as $flavor): ?>
                                        <span class="badge" style="background: <?= escape($flavor['color']) ?>; color: <?= getContrastColor($flavor['color']) ?>">
                                            <?= escape($flavor['name']) ?>
                                        </span>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Speichern Modal -->
    <div class="modal fade" id="saveModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content bg-dark text-white">
                <div class="modal-header border-secondary">
                    <h5 class="modal-title"><i class="bi bi-save"></i> Konfiguration speichern</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST" action="summary.php">
                    <div class="modal-body">
                        <input type="hidden" name="save_config" value="1">
                        <div class="mb-3">
                            <label for="config_name" class="form-label">Name für diese Konfiguration</label>
                            <input type="text" class="form-control bg-dark text-white border-secondary" 
                                   id="config_name" name="config_name" 
                                   value="<?= escape($canName) ?>" required>
                        </div>
                    </div>
                    <div class="modal-footer border-secondary">
                        <button type="button" class="btn btn-outline-light" data-bs-dismiss="modal">Abbrechen</button>
                        <button type="submit" class="btn btn-neon">Speichern</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Bestellen Modal -->
    <div class="modal fade" id="orderModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content bg-dark text-white">
                <div class="modal-header border-secondary">
                    <h5 class="modal-title"><i class="bi bi-cart-check"></i> Bestellung</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body text-center">
                    <div class="py-4">
                        <i class="bi bi-check-circle-fill text-success" style="font-size: 4rem;"></i>
                        <h4 class="mt-3">Vielen Dank!</h4>
                        <p class="text-muted">
                            Dies ist ein Demo-Konfigurator.<br>
                            In einer echten Anwendung würde jetzt der Checkout-Prozess starten.
                        </p>
                        <div class="summary-total mt-4 p-3" style="background: rgba(0,255,136,0.1); border-radius: 10px;">
                            <small style="color: #b8b8cc;">Bestellwert</small>
                            <div class="summary-price"><?= formatPrice($prices['total']) ?></div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-secondary justify-content-center">
                    <a href="index.php" class="btn btn-neon">Zur Startseite</a>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

<?php
/**
 * Hilfsfunktion: Farbe anpassen
 */
function adjustColor($color, $amount) {
    $color = ltrim($color, '#');
    $r = max(0, min(255, hexdec(substr($color, 0, 2)) + $amount));
    $g = max(0, min(255, hexdec(substr($color, 2, 2)) + $amount));
    $b = max(0, min(255, hexdec(substr($color, 4, 2)) + $amount));
    return sprintf("#%02x%02x%02x", $r, $g, $b);
}

/**
 * Hilfsfunktion: Kontrastfarbe berechnen
 */
function getContrastColor($hexcolor) {
    $hexcolor = ltrim($hexcolor, '#');
    $r = hexdec(substr($hexcolor, 0, 2));
    $g = hexdec(substr($hexcolor, 2, 2));
    $b = hexdec(substr($hexcolor, 4, 2));
    $yiq = (($r * 299) + ($g * 587) + ($b * 114)) / 1000;
    return ($yiq >= 128) ? '#000000' : '#ffffff';
}
?>
