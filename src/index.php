<?php
/**
 * Landing Page - Energy Pulver Konfigurator
 */
require_once 'config/database.php';
startSession();
?>
<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Energy Pulver Konfigurator - Erstelle dein eigenes Energy Pulver in der Dose</title>
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
                    <?php if (isLoggedIn()): ?>
                        <li class="nav-item">
                            <a class="nav-link" href="meine-drinks.php">Meine Mixes</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="logout.php">
                                <i class="bi bi-box-arrow-right"></i> Logout (<?= escape(getUserName()) ?>)
                            </a>
                        </li>
                    <?php else: ?>
                        <li class="nav-item">
                            <a class="nav-link" href="login.php">Login</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link btn btn-outline-neon btn-sm ms-2" href="register.php">Registrieren</a>
                        </li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <header class="hero-section">
        <div class="hero-overlay"></div>
        <div class="container hero-content">
            <div class="row align-items-center min-vh-100">
                <div class="col-lg-6">
                    <h1 class="display-3 fw-bold mb-4">
                        Dein <span class="text-neon">Energy Pulver</span><br>
                        Deine Regeln.
                    </h1>
                    <p class="lead mb-4">
                        Kreiere dein individuelles Energy Pulver in der Dose mit über 20 Geschmacksrichtungen, 
                        verschiedenen Koffein-Leveln und funktionalen Zusätzen. 
                        Wähle zwischen 300g, 400g und 500g Dosen.
                        Perfekt für Gamer, Streamer und alle, die mehr wollen.
                    </p>
                    <div class="d-flex gap-3 flex-wrap">
                        <a href="konfigurator.php" class="btn btn-neon btn-lg">
                            <i class="bi bi-lightning-charge"></i> Jetzt konfigurieren
                        </a>
                        <a href="#features" class="btn btn-outline-light btn-lg">
                            Mehr erfahren
                        </a>
                    </div>
                </div>
                <div class="col-lg-6 text-center">
                    <div class="hero-can-container">
                        <div class="hero-can">
                            <div class="can-glow"></div>
                            <svg viewBox="0 0 140 200" class="can-svg">
                                <!-- Pulverdose SVG -->
                                <defs>
                                    <linearGradient id="tinGradient" x1="0%" y1="0%" x2="100%" y2="100%">
                                        <stop offset="0%" style="stop-color:#00ff88;stop-opacity:1" />
                                        <stop offset="50%" style="stop-color:#00ccff;stop-opacity:1" />
                                        <stop offset="100%" style="stop-color:#00ff88;stop-opacity:1" />
                                    </linearGradient>
                                    <linearGradient id="tinLidGrad" x1="0%" y1="0%" x2="0%" y2="100%">
                                        <stop offset="0%" style="stop-color:#e0e0e0;stop-opacity:1" />
                                        <stop offset="100%" style="stop-color:#a0a0a0;stop-opacity:1" />
                                    </linearGradient>
                                </defs>
                                <!-- Deckel oben -->
                                <ellipse cx="70" cy="22" rx="48" ry="12" fill="url(#tinLidGrad)" />
                                <rect x="22" y="15" width="96" height="12" fill="#c0c0c0" />
                                <ellipse cx="70" cy="15" rx="48" ry="12" fill="#d0d0d0" />
                                <!-- Griff auf Deckel -->
                                <rect x="55" y="8" width="30" height="5" rx="2" fill="#b0b0b0" />
                                <!-- Dosen-Körper (Zylinder) -->
                                <rect x="22" y="22" width="96" height="145" fill="url(#tinGradient)" />
                                <!-- Boden-Ellipse -->
                                <ellipse cx="70" cy="167" rx="48" ry="12" fill="url(#tinGradient)" opacity="0.8" />
                                <!-- Label -->
                                <rect x="28" y="50" width="84" height="100" fill="rgba(0,0,0,0.3)" rx="5" />
                                <text x="70" y="78" text-anchor="middle" fill="#fff" font-size="11" font-weight="bold">ENERGY</text>
                                <text x="70" y="96" text-anchor="middle" fill="#fff" font-size="13" font-weight="bold">MIX</text>
                                <text x="70" y="118" text-anchor="middle" fill="#fff" font-size="7">DEIN PULVER</text>
                                <text x="70" y="134" text-anchor="middle" fill="#fff" font-size="6">300g | 400g | 500g</text>
                                <!-- Glanz-Effekt -->
                                <rect x="22" y="22" width="15" height="145" fill="rgba(255,255,255,0.08)" />
                            </svg>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </header>

    <!-- Features Section -->
    <section id="features" class="py-5 bg-dark">
        <div class="container">
            <h2 class="text-center mb-5 text-white">So funktioniert's</h2>
            <div class="row g-4">
                <div class="col-md-4">
                    <div class="feature-card text-center p-4">
                        <div class="feature-icon mb-3">
                            <i class="bi bi-lightning-charge text-warning"></i>
                        </div>
                        <h4>1. Koffein-Level wählen</h4>
                        <p class="text-muted">Von Light (50mg) bis Extreme (200mg) – du bestimmst die Power.</p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="feature-card text-center p-4">
                        <div class="feature-icon mb-3">
                            <i class="bi bi-palette text-info"></i>
                        </div>
                        <h4>2. Flavors kombinieren</h4>
                        <p class="text-muted">Über 20 Geschmacksrichtungen warten auf dich.</p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="feature-card text-center p-4">
                        <div class="feature-icon mb-3">
                            <i class="bi bi-stars text-success"></i>
                        </div>
                        <h4>3. Personalisieren</h4>
                        <p class="text-muted">Dein Name auf der Verpackung, deine Farbe, dein Style.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Presets Section -->
    <section class="py-5 bg-darker">
        <div class="container">
            <h2 class="text-center mb-2 text-white">Beliebte Kreationen</h2>
            <p class="text-center text-muted mb-5">Lass dich inspirieren oder starte mit einem Preset</p>
            <div class="row g-4">
                <?php
                try {
                    $pdo = getDbConnection();
                    $stmt = $pdo->query("SELECT p.*, cl.menge_mg, s.name as sweetener_name 
                                        FROM presets p 
                                        JOIN caffeine_levels cl ON p.caffeine_level_id = cl.id 
                                        JOIN sweeteners s ON p.sweetener_id = s.id 
                                        WHERE p.beliebt = 1 
                                        LIMIT 3");
                    $presets = $stmt->fetchAll();
                    
                    foreach ($presets as $preset):
                ?>
                <div class="col-md-4">
                    <div class="preset-card" style="border-color: <?= escape($preset['dosen_farbe']) ?>">
                        <div class="preset-header" style="background: <?= escape($preset['dosen_farbe']) ?>20">
                            <h5 class="mb-0"><?= escape($preset['name']) ?></h5>
                        </div>
                        <div class="preset-body">
                            <p class="text-muted small"><?= escape($preset['beschreibung']) ?></p>
                            <div class="preset-stats">
                                <span class="badge bg-warning text-dark">
                                    <i class="bi bi-lightning"></i> <?= $preset['menge_mg'] ?>mg
                                </span>
                                <span class="badge bg-secondary">
                                    <?= escape($preset['sweetener_name']) ?>
                                </span>
                            </div>
                        </div>
                        <div class="preset-footer">
                            <a href="konfigurator.php?preset=<?= $preset['id'] ?>" class="btn btn-sm btn-outline-neon w-100">
                                Anpassen
                            </a>
                        </div>
                    </div>
                </div>
                <?php 
                    endforeach;
                } catch (PDOException $e) {
                    // Datenbank noch nicht eingerichtet
                    echo '<div class="col-12 text-center">
                            <div class="alert alert-info">
                                <p>Die Datenbank wurde noch nicht eingerichtet.</p>
                                <a href="install.php" class="btn btn-neon">Jetzt installieren</a>
                            </div>
                          </div>';
                }
                ?>
            </div>
        </div>
    </section>

    <!-- Pricing Section -->
    <section class="py-5 bg-dark">
        <div class="container">
            <h2 class="text-center mb-5 text-white">Transparente Preise</h2>
            <div class="row justify-content-center g-4">
                <div class="col-lg-4 col-md-6">
                    <div class="pricing-card text-center p-4">
                        <h4 class="mb-2">300g Dose</h4>
                        <p class="text-muted small">~20 Portionen</p>
                        <h3 class="text-neon mb-3">14,95 €</h3>
                        <ul class="list-unstyled pricing-list text-start">
                            <li><i class="bi bi-check-circle text-success"></i> Basis-Koffein-Level</li>
                            <li><i class="bi bi-check-circle text-success"></i> Standard-Geschmack</li>
                            <li><i class="bi bi-check-circle text-success"></i> Personalisierung inklusive</li>
                        </ul>
                        <a href="konfigurator.php" class="btn btn-outline-neon mt-3 w-100">Jetzt starten</a>
                    </div>
                </div>
                <div class="col-lg-4 col-md-6">
                    <div class="pricing-card text-center p-4" style="border: 2px solid var(--neon-green); position: relative;">
                        <span class="badge bg-success position-absolute" style="top: -10px; left: 50%; transform: translateX(-50%);">BELIEBT</span>
                        <h4 class="mb-2">400g Dose</h4>
                        <p class="text-muted small">~27 Portionen</p>
                        <h3 class="text-neon mb-3">18,95 €</h3>
                        <ul class="list-unstyled pricing-list text-start">
                            <li><i class="bi bi-check-circle text-success"></i> Basis-Koffein-Level</li>
                            <li><i class="bi bi-check-circle text-success"></i> Standard-Geschmack</li>
                            <li><i class="bi bi-check-circle text-success"></i> Personalisierung inklusive</li>
                        </ul>
                        <a href="konfigurator.php" class="btn btn-neon mt-3 w-100">Jetzt starten <i class="bi bi-arrow-right"></i></a>
                    </div>
                </div>
                <div class="col-lg-4 col-md-6">
                    <div class="pricing-card text-center p-4">
                        <span class="badge bg-warning text-dark position-absolute" style="top: -10px; left: 50%; transform: translateX(-50%);">BEST VALUE</span>
                        <h4 class="mb-2">500g Dose</h4>
                        <p class="text-muted small">~33 Portionen</p>
                        <h3 class="text-neon mb-3">22,95 €</h3>
                        <ul class="list-unstyled pricing-list text-start">
                            <li><i class="bi bi-check-circle text-success"></i> Basis-Koffein-Level</li>
                            <li><i class="bi bi-check-circle text-success"></i> Standard-Geschmack</li>
                            <li><i class="bi bi-check-circle text-success"></i> Personalisierung inklusive</li>
                        </ul>
                        <a href="konfigurator.php" class="btn btn-outline-neon mt-3 w-100">Jetzt starten</a>
                    </div>
                </div>
            </div>
            <div class="text-center mt-4">
                <p class="text-muted">Premium-Flavors, Funktionale Zusätze und Extra Koffein kosten +0,20 – 0,50 € Aufpreis</p>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="py-4 bg-black">
        <div class="container">
            <div class="row">
                <div class="col-md-6">
                    <p class="mb-0 text-muted">
                        <i class="bi bi-lightning-charge-fill text-warning"></i> 
                        ENERGYMIX Pulver-Konfigurator &copy; 2026
                    </p>
                </div>
                <div class="col-md-6 text-md-end">
                    <a href="#" class="text-muted me-3">Impressum</a>
                    <a href="#" class="text-muted me-3">Datenschutz</a>
                    <a href="#" class="text-muted">AGB</a>
                </div>
            </div>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="js/konfigurator.js"></script>
</body>
</html>
