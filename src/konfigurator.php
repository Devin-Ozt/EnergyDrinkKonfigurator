<?php
/**
 * Hauptkonfigurator - 5-Schritte Energy Drink Konfiguration
 */
require_once 'config/database.php';
startSession();

// Daten aus der Datenbank laden
try {
    $pdo = getDbConnection();
    
    // Koffein-Level
    $caffeineLevels = $pdo->query("SELECT * FROM caffeine_levels ORDER BY menge_mg")->fetchAll();
    
    // Geschmacksrichtungen
    $flavors = $pdo->query("SELECT * FROM flavors ORDER BY kategorie, name")->fetchAll();
    
    // Zusätze
    $additives = $pdo->query("SELECT * FROM additives ORDER BY kategorie, name")->fetchAll();
    
    // Süßungsmittel
    $sweeteners = $pdo->query("SELECT * FROM sweeteners ORDER BY id")->fetchAll();
    
    // Preset laden falls übergeben
    $preset = null;
    $presetFlavors = [];
    $presetAdditives = [];
    
    if (isset($_GET['preset'])) {
        $presetId = (int)$_GET['preset'];
        $stmt = $pdo->prepare("SELECT * FROM presets WHERE id = ?");
        $stmt->execute([$presetId]);
        $preset = $stmt->fetch();
        
        if ($preset) {
            // Preset Flavors
            $stmt = $pdo->prepare("SELECT flavor_id FROM preset_flavors WHERE preset_id = ?");
            $stmt->execute([$presetId]);
            $presetFlavors = $stmt->fetchAll(PDO::FETCH_COLUMN);
            
            // Preset Additives
            $stmt = $pdo->prepare("SELECT additive_id FROM preset_additives WHERE preset_id = ?");
            $stmt->execute([$presetId]);
            $presetAdditives = $stmt->fetchAll(PDO::FETCH_COLUMN);
        }
    }
    
    // Kategorien für Filter
    $flavorCategories = array_unique(array_column($flavors, 'kategorie'));
    $additiveCategories = array_unique(array_column($additives, 'kategorie'));
    
} catch (PDOException $e) {
    // Datenbank nicht verfügbar
    $caffeineLevels = [];
    $flavors = [];
    $additives = [];
    $sweeteners = [];
}
?>
<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Konfigurator - Energy Drink Konfigurator</title>
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
                        <a class="nav-link active" href="konfigurator.php">Konfigurator</a>
                    </li>
                    <?php if (isLoggedIn()): ?>
                        <li class="nav-item">
                            <a class="nav-link" href="meine-drinks.php">Meine Drinks</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="logout.php">
                                <i class="bi bi-box-arrow-right"></i> Logout
                            </a>
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

    <div class="konfigurator-container">
        <div class="container">
            <?php if (empty($caffeineLevels)): ?>
                <div class="alert alert-warning text-center">
                    <h4>Datenbank nicht eingerichtet</h4>
                    <p>Bitte führe zuerst die Installation durch.</p>
                    <a href="install.php" class="btn btn-neon">Installation starten</a>
                </div>
            <?php else: ?>
            
            <!-- Progress Steps -->
            <div class="progress-steps">
                <div class="step active" data-step="1">
                    <span class="step-number">1</span>
                    <span class="step-title">Koffein</span>
                </div>
                <div class="step" data-step="2">
                    <span class="step-number">2</span>
                    <span class="step-title">Geschmack</span>
                </div>
                <div class="step" data-step="3">
                    <span class="step-number">3</span>
                    <span class="step-title">Zusätze</span>
                </div>
                <div class="step" data-step="4">
                    <span class="step-number">4</span>
                    <span class="step-title">Süßung</span>
                </div>
                <div class="step" data-step="5">
                    <span class="step-number">5</span>
                    <span class="step-title">Design</span>
                </div>
            </div>

            <div class="konfigurator-main">
                <!-- Linke Seite: Optionen -->
                <div class="konfigurator-options">
                    
                    <!-- Schritt 1: Koffein-Level -->
                    <div class="step-content active" id="step-1">
                        <h3><i class="bi bi-lightning-charge text-warning"></i> Wähle dein Koffein-Level</h3>
                        <p class="text-muted">Wie viel Power brauchst du?</p>
                        
                        <div class="options-grid">
                            <?php foreach ($caffeineLevels as $level): ?>
                            <div class="option-card" 
                                 data-id="<?= $level['id'] ?>" 
                                 data-type="caffeine"
                                 data-price="<?= $level['preis'] ?>"
                                 data-name="<?= escape($level['name']) ?>"
                                 data-mg="<?= $level['menge_mg'] ?>">
                                <div class="option-icon">
                                    <?php
                                    $icons = ['50' => '⚡', '100' => '⚡⚡', '150' => '⚡⚡⚡', '200' => '🔥'];
                                    echo $icons[$level['menge_mg']] ?? '⚡';
                                    ?>
                                </div>
                                <div class="option-name"><?= escape($level['name']) ?></div>
                                <div class="option-desc"><?= $level['menge_mg'] ?>mg Koffein</div>
                                <div class="option-price">
                                    <?= $level['preis'] > 0 ? '+' . formatPrice($level['preis']) : 'Inklusive' ?>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                        
                        <div class="konfigurator-nav">
                            <div></div>
                            <button class="btn btn-neon" onclick="nextStep()">
                                Weiter <i class="bi bi-arrow-right"></i>
                            </button>
                        </div>
                    </div>

                    <!-- Schritt 2: Geschmacksrichtungen -->
                    <div class="step-content" id="step-2">
                        <h3><i class="bi bi-palette text-info"></i> Wähle deine Geschmacksrichtungen</h3>
                        <p class="text-muted">Kombiniere bis zu 3 Flavors für deinen perfekten Mix!</p>
                        
                        <!-- Suche -->
                        <div class="search-box">
                            <i class="bi bi-search"></i>
                            <input type="text" id="flavor-search" placeholder="Geschmack suchen...">
                        </div>
                        
                        <!-- Filter -->
                        <div class="filter-buttons">
                            <button class="filter-btn active" data-filter="all">Alle</button>
                            <?php foreach ($flavorCategories as $cat): ?>
                            <button class="filter-btn" data-filter="<?= escape($cat) ?>"><?= escape($cat) ?></button>
                            <?php endforeach; ?>
                        </div>
                        
                        <div class="flavor-grid" id="flavor-grid">
                            <?php foreach ($flavors as $flavor): ?>
                            <div class="flavor-card <?= $flavor['ist_premium'] ? 'premium' : '' ?>" 
                                 data-id="<?= $flavor['id'] ?>"
                                 data-type="flavor"
                                 data-price="<?= $flavor['preis'] ?>"
                                 data-name="<?= escape($flavor['name']) ?>"
                                 data-color="<?= escape($flavor['farbe']) ?>"
                                 data-category="<?= escape($flavor['kategorie']) ?>"
                                 style="border-color: <?= escape($flavor['farbe']) ?>">
                                <?php if ($flavor['ist_premium']): ?>
                                    <span class="premium-badge">PREMIUM</span>
                                <?php endif; ?>
                                <div class="flavor-color" style="background: <?= escape($flavor['farbe']) ?>"></div>
                                <div class="flavor-name"><?= escape($flavor['name']) ?></div>
                                <div class="flavor-category"><?= escape($flavor['kategorie']) ?></div>
                                <?php if ($flavor['preis'] > 0): ?>
                                    <small class="text-warning">+<?= formatPrice($flavor['preis']) ?></small>
                                <?php endif; ?>
                            </div>
                            <?php endforeach; ?>
                        </div>
                        
                        <div class="mt-3 text-center">
                            <span class="badge bg-info" id="flavor-count">0 / 3 Flavors gewählt</span>
                        </div>
                        
                        <div class="konfigurator-nav">
                            <button class="btn btn-outline-light" onclick="prevStep()">
                                <i class="bi bi-arrow-left"></i> Zurück
                            </button>
                            <button class="btn btn-neon" onclick="nextStep()">
                                Weiter <i class="bi bi-arrow-right"></i>
                            </button>
                        </div>
                    </div>

                    <!-- Schritt 3: Funktionale Zusätze -->
                    <div class="step-content" id="step-3">
                        <h3><i class="bi bi-capsule text-success"></i> Funktionale Zusätze</h3>
                        <p class="text-muted">Boost deine Performance mit speziellen Zutaten</p>
                        
                        <div class="options-grid">
                            <?php foreach ($additives as $additive): ?>
                            <div class="option-card" 
                                 data-id="<?= $additive['id'] ?>"
                                 data-type="additive"
                                 data-price="<?= $additive['preis'] ?>"
                                 data-name="<?= escape($additive['name']) ?>"
                                 data-category="<?= escape($additive['kategorie']) ?>">
                                <div class="option-icon">
                                    <?php
                                    $icons = [
                                        'vitamin' => '💊',
                                        'bolt' => '⚡',
                                        'leaf' => '🌿',
                                        'brain' => '🧠',
                                        'dumbbell' => '💪',
                                        'droplet' => '💧',
                                        'shield' => '🛡️'
                                    ];
                                    echo $icons[$additive['icon']] ?? '✨';
                                    ?>
                                </div>
                                <div class="option-name"><?= escape($additive['name']) ?></div>
                                <div class="option-desc"><?= escape($additive['beschreibung']) ?></div>
                                <div class="option-price">+<?= formatPrice($additive['preis']) ?></div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                        
                        <div class="konfigurator-nav">
                            <button class="btn btn-outline-light" onclick="prevStep()">
                                <i class="bi bi-arrow-left"></i> Zurück
                            </button>
                            <button class="btn btn-neon" onclick="nextStep()">
                                Weiter <i class="bi bi-arrow-right"></i>
                            </button>
                        </div>
                    </div>

                    <!-- Schritt 4: Süßungsmittel -->
                    <div class="step-content" id="step-4">
                        <h3><i class="bi bi-droplet text-primary"></i> Süßungsmittel</h3>
                        <p class="text-muted">Wie süß soll dein Drink sein?</p>
                        
                        <div class="options-grid">
                            <?php foreach ($sweeteners as $sweetener): ?>
                            <div class="option-card" 
                                 data-id="<?= $sweetener['id'] ?>"
                                 data-type="sweetener"
                                 data-price="<?= $sweetener['preis'] ?>"
                                 data-name="<?= escape($sweetener['name']) ?>"
                                 data-calories="<?= $sweetener['kalorien_pro_100ml'] ?>">
                                <div class="option-icon">
                                    <?php
                                    $icons = ['Zucker' => '🍬', 'Stevia' => '🌱', 'Erythrit' => '🧊', 'Zuckerfrei' => '🚫'];
                                    echo $icons[$sweetener['name']] ?? '🍬';
                                    ?>
                                </div>
                                <div class="option-name"><?= escape($sweetener['name']) ?></div>
                                <div class="option-desc">
                                    <?= $sweetener['kalorien_pro_100ml'] ?> kcal/100ml
                                </div>
                                <div class="option-price">
                                    <?= $sweetener['preis'] > 0 ? '+' . formatPrice($sweetener['preis']) : 'Inklusive' ?>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                        
                        <div class="konfigurator-nav">
                            <button class="btn btn-outline-light" onclick="prevStep()">
                                <i class="bi bi-arrow-left"></i> Zurück
                            </button>
                            <button class="btn btn-neon" onclick="nextStep()">
                                Weiter <i class="bi bi-arrow-right"></i>
                            </button>
                        </div>
                    </div>

                    <!-- Schritt 5: Personalisierung -->
                    <div class="step-content" id="step-5">
                        <h3><i class="bi bi-brush text-warning"></i> Personalisierung</h3>
                        <p class="text-muted">Mach deinen Drink einzigartig!</p>
                        
                        <div class="personalization-section">
                            <h5>Dosenfarbe wählen</h5>
                            <div class="color-picker-container">
                                <input type="color" id="can-color" value="#00ff88">
                                <div class="color-presets">
                                    <div class="color-preset" style="background: #00ff88" data-color="#00ff88"></div>
                                    <div class="color-preset" style="background: #00ccff" data-color="#00ccff"></div>
                                    <div class="color-preset" style="background: #ff00ff" data-color="#ff00ff"></div>
                                    <div class="color-preset" style="background: #ff4444" data-color="#ff4444"></div>
                                    <div class="color-preset" style="background: #ffd700" data-color="#ffd700"></div>
                                    <div class="color-preset" style="background: #ffffff" data-color="#ffffff"></div>
                                    <div class="color-preset" style="background: #1e1e1e" data-color="#1e1e1e"></div>
                                    <div class="color-preset" style="background: #ff6b00" data-color="#ff6b00"></div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="name-input">
                            <h5>Name auf der Dose</h5>
                            <input type="text" id="can-name" placeholder="Dein Name oder Gamertag" maxlength="15">
                            <small class="text-muted">Max. 15 Zeichen</small>
                        </div>
                        
                        <div class="konfigurator-nav">
                            <button class="btn btn-outline-light" onclick="prevStep()">
                                <i class="bi bi-arrow-left"></i> Zurück
                            </button>
                            <button class="btn btn-neon" onclick="goToSummary()">
                                Zur Zusammenfassung <i class="bi bi-arrow-right"></i>
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Rechte Seite: Vorschau -->
                <div class="can-preview-container">
                    <div class="can-preview-card">
                        <h5 class="mb-3">Deine Kreation</h5>
                        
                        <div class="can-preview">
                            <svg viewBox="0 0 120 200" class="can-preview-svg" id="can-svg">
                                <defs>
                                    <linearGradient id="canPreviewGradient" x1="0%" y1="0%" x2="100%" y2="0%">
                                        <stop offset="0%" id="gradient-stop-1" style="stop-color:#00ff88;stop-opacity:1" />
                                        <stop offset="50%" id="gradient-stop-2" style="stop-color:#00ccff;stop-opacity:1" />
                                        <stop offset="100%" id="gradient-stop-3" style="stop-color:#00ff88;stop-opacity:1" />
                                    </linearGradient>
                                </defs>
                                <!-- Dose Körper -->
                                <rect x="15" y="25" width="90" height="160" rx="5" fill="url(#canPreviewGradient)" id="can-body" />
                                <!-- Dose Oberteil -->
                                <ellipse cx="60" cy="25" rx="45" ry="10" fill="#c0c0c0" />
                                <ellipse cx="60" cy="25" rx="40" ry="8" fill="#e0e0e0" />
                                <!-- Pull Tab -->
                                <ellipse cx="60" cy="25" rx="15" ry="5" fill="#a0a0a0" />
                                <rect x="55" y="15" width="10" height="15" rx="2" fill="#808080" />
                                <!-- Dose Unterteil -->
                                <ellipse cx="60" cy="185" rx="45" ry="10" id="can-bottom" fill="#00cc70" />
                                <!-- Label -->
                                <rect x="20" y="60" width="80" height="100" fill="rgba(0,0,0,0.3)" rx="3" />
                                <text x="60" y="90" text-anchor="middle" fill="#fff" font-size="10" font-weight="bold">ENERGY</text>
                                <text x="60" y="108" text-anchor="middle" fill="#fff" font-size="12" font-weight="bold">MIX</text>
                                <text x="60" y="130" text-anchor="middle" fill="#fff" font-size="7" id="can-name-text">DEIN NAME</text>
                                <text x="60" y="150" text-anchor="middle" fill="#fff" font-size="6" id="can-caffeine-text">100mg</text>
                            </svg>
                        </div>
                        
                        <!-- Ausgewählte Flavors -->
                        <div id="selected-flavors-preview" class="mb-3">
                            <small class="text-muted">Noch keine Flavors gewählt</small>
                        </div>
                        
                        <!-- Preis-Anzeige -->
                        <div class="price-display">
                            <div class="price-row">
                                <span>Basispreis</span>
                                <span>3,95 €</span>
                            </div>
                            <div class="price-row" id="price-caffeine" style="display: none;">
                                <span>Koffein</span>
                                <span>0,00 €</span>
                            </div>
                            <div class="price-row" id="price-flavors" style="display: none;">
                                <span>Flavors</span>
                                <span>0,00 €</span>
                            </div>
                            <div class="price-row" id="price-additives" style="display: none;">
                                <span>Zusätze</span>
                                <span>0,00 €</span>
                            </div>
                            <div class="price-row" id="price-sweetener" style="display: none;">
                                <span>Süßung</span>
                                <span>0,00 €</span>
                            </div>
                            <div class="price-row" id="price-discount" style="display: none; color: #00ff88;">
                                <span>Rabatt</span>
                                <span>-0,00 €</span>
                            </div>
                            <div class="price-row total">
                                <span>Gesamt</span>
                                <span id="total-price">3,95 €</span>
                            </div>
                        </div>
                        
                        <!-- Gutschein -->
                        <div class="coupon-section">
                            <label class="small mb-2">Gutscheincode</label>
                            <div class="coupon-input-group">
                                <input type="text" id="coupon-code" placeholder="CODE">
                                <button onclick="applyCoupon()">Einlösen</button>
                            </div>
                            <div class="coupon-success" id="coupon-success">
                                <i class="bi bi-check-circle"></i> <span id="coupon-message"></span>
                            </div>
                            <div class="coupon-error" id="coupon-error">
                                <i class="bi bi-x-circle"></i> Ungültiger Code
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <?php endif; ?>
        </div>
    </div>

    <!-- Hidden Form für Weiterleitung zur Zusammenfassung -->
    <form id="summary-form" action="summary.php" method="POST" style="display: none;">
        <input type="hidden" name="config_data" id="config-data">
    </form>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Preset-Daten falls vorhanden
        const presetData = <?= json_encode([
            'preset' => $preset,
            'flavors' => $presetFlavors,
            'additives' => $presetAdditives
        ]) ?>;
    </script>
    <script src="js/konfigurator.js"></script>
</body>
</html>
