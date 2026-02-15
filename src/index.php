<?php
/**
 * Landing Page - Energy Drink Konfigurator
 */
require_once 'config/database.php';
startSession();
?>
<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Energy Drink Konfigurator - Erstelle deinen eigenen Energy Drink</title>
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
                            <a class="nav-link" href="meine-drinks.php">Meine Drinks</a>
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
                        Dein <span class="text-neon">Energy Drink</span><br>
                        Deine Regeln.
                    </h1>
                    <p class="lead mb-4">
                        Kreiere deinen individuellen Energy Drink mit über 20 Geschmacksrichtungen, 
                        verschiedenen Koffein-Leveln und funktionalen Zusätzen. 
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
                            <svg viewBox="0 0 120 200" class="can-svg">
                                <!-- Dose SVG -->
                                <defs>
                                    <linearGradient id="canGradient" x1="0%" y1="0%" x2="100%" y2="0%">
                                        <stop offset="0%" style="stop-color:#00ff88;stop-opacity:1" />
                                        <stop offset="50%" style="stop-color:#00ccff;stop-opacity:1" />
                                        <stop offset="100%" style="stop-color:#00ff88;stop-opacity:1" />
                                    </linearGradient>
                                </defs>
                                <!-- Dose Körper -->
                                <rect x="15" y="25" width="90" height="160" rx="5" fill="url(#canGradient)" />
                                <!-- Dose Oberteil -->
                                <ellipse cx="60" cy="25" rx="45" ry="10" fill="#c0c0c0" />
                                <ellipse cx="60" cy="25" rx="40" ry="8" fill="#e0e0e0" />
                                <!-- Pull Tab -->
                                <ellipse cx="60" cy="25" rx="15" ry="5" fill="#a0a0a0" />
                                <rect x="55" y="15" width="10" height="15" rx="2" fill="#808080" />
                                <!-- Dose Unterteil -->
                                <ellipse cx="60" cy="185" rx="45" ry="10" fill="#00cc70" />
                                <!-- Label -->
                                <rect x="20" y="60" width="80" height="100" fill="rgba(0,0,0,0.3)" rx="3" />
                                <text x="60" y="100" text-anchor="middle" fill="#fff" font-size="12" font-weight="bold">ENERGY</text>
                                <text x="60" y="120" text-anchor="middle" fill="#fff" font-size="14" font-weight="bold">MIX</text>
                                <text x="60" y="145" text-anchor="middle" fill="#fff" font-size="8">DEIN DRINK</text>
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
                        <p class="text-muted">Dein Name auf der Dose, deine Farbe, dein Style.</p>
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
            <div class="row justify-content-center">
                <div class="col-lg-8">
                    <div class="pricing-card">
                        <div class="row align-items-center">
                            <div class="col-md-6">
                                <h3 class="text-neon mb-4">Basispreis: 3,95 €</h3>
                                <ul class="list-unstyled pricing-list">
                                    <li><i class="bi bi-check-circle text-success"></i> 330ml Dose</li>
                                    <li><i class="bi bi-check-circle text-success"></i> Basis-Koffein-Level</li>
                                    <li><i class="bi bi-check-circle text-success"></i> Standard-Geschmack</li>
                                    <li><i class="bi bi-check-circle text-success"></i> Personalisierung inklusive</li>
                                </ul>
                            </div>
                            <div class="col-md-6">
                                <h4 class="mb-3">Premium-Optionen</h4>
                                <ul class="list-unstyled pricing-list">
                                    <li><i class="bi bi-plus-circle text-warning"></i> Premium-Flavors: +0,30 €</li>
                                    <li><i class="bi bi-plus-circle text-warning"></i> Funktionale Zusätze: +0,30 €</li>
                                    <li><i class="bi bi-plus-circle text-warning"></i> Extra Koffein: +0,30-0,50 €</li>
                                </ul>
                                <a href="konfigurator.php" class="btn btn-neon mt-3">
                                    Jetzt starten <i class="bi bi-arrow-right"></i>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
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
                        ENERGYMIX Konfigurator &copy; 2026
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
