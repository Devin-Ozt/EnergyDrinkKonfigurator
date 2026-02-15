<?php
/**
 * Login-Seite
 */
require_once 'config/database.php';
startSession();

// Wenn bereits eingeloggt, zur Startseite weiterleiten
if (isLoggedIn()) {
    header('Location: index.php');
    exit;
}

$fehler = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $passwort = $_POST['passwort'] ?? '';
    
    if (empty($email) || empty($passwort)) {
        $fehler = 'Bitte gib E-Mail und Passwort ein.';
    } else {
        try {
            $pdo = getDbConnection();
            $stmt = $pdo->prepare("SELECT id, vorname, nachname, passwort FROM users WHERE email = ?");
            $stmt->execute([$email]);
            $user = $stmt->fetch();
            
            if ($user && password_verify($passwort, $user['passwort'])) {
                // Login erfolgreich
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['user_name'] = $user['vorname'];
                $_SESSION['user_fullname'] = $user['vorname'] . ' ' . $user['nachname'];
                
                // Weiterleitung
                $redirect = $_GET['redirect'] ?? 'index.php';
                header('Location: ' . $redirect);
                exit;
            } else {
                $fehler = 'E-Mail oder Passwort ist falsch.';
            }
        } catch (PDOException $e) {
            $fehler = 'Ein Datenbankfehler ist aufgetreten.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Energy Pulver Konfigurator</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
    <link href="css/style.css" rel="stylesheet">
</head>
<body>
    <div class="auth-container">
        <div class="auth-card">
            <div class="text-center mb-4">
                <a href="index.php" class="text-decoration-none">
                    <i class="bi bi-lightning-charge-fill text-warning fs-1"></i>
                    <h1 class="h4 mt-2">
                        <span class="brand-text">ENERGY<span class="text-neon">MIX</span></span>
                    </h1>
                </a>
            </div>
            
            <h2>Login</h2>
            
            <?php if ($fehler): ?>
                <div class="alert alert-danger">
                    <i class="bi bi-exclamation-circle"></i> <?= escape($fehler) ?>
                </div>
            <?php endif; ?>
            
            <?php if (isset($_GET['registered'])): ?>
                <div class="alert alert-success">
                    <i class="bi bi-check-circle"></i> Registrierung erfolgreich! Du kannst dich jetzt einloggen.
                </div>
            <?php endif; ?>
            
            <form method="POST" action="login.php<?= isset($_GET['redirect']) ? '?redirect=' . urlencode($_GET['redirect']) : '' ?>">
                <div class="form-floating">
                    <input type="email" class="form-control" id="email" name="email" 
                           placeholder="E-Mail" value="<?= escape($_POST['email'] ?? '') ?>" required>
                    <label for="email">E-Mail-Adresse</label>
                </div>
                
                <div class="form-floating">
                    <input type="password" class="form-control" id="passwort" name="passwort" 
                           placeholder="Passwort" required>
                    <label for="passwort">Passwort</label>
                </div>
                
                <button type="submit" class="btn btn-neon w-100 mt-4">
                    <i class="bi bi-box-arrow-in-right"></i> Einloggen
                </button>
            </form>
            
            <div class="auth-links">
                Noch kein Konto? <a href="register.php">Jetzt registrieren</a>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
