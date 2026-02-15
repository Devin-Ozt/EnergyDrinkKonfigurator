<?php
/**
 * Registrierungsseite
 */
require_once 'config/database.php';
startSession();

// Wenn bereits eingeloggt, zur Startseite weiterleiten
if (isLoggedIn()) {
    header('Location: index.php');
    exit;
}

$fehler = '';
$erfolg = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $vorname = trim($_POST['vorname'] ?? '');
    $nachname = trim($_POST['nachname'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $passwort = $_POST['passwort'] ?? '';
    $passwort2 = $_POST['passwort2'] ?? '';
    $strasse = trim($_POST['strasse'] ?? '');
    $hausnummer = trim($_POST['hausnummer'] ?? '');
    $plz = trim($_POST['plz'] ?? '');
    $ort = trim($_POST['ort'] ?? '');
    
    // Validierung
    if (empty($vorname) || empty($nachname) || empty($email) || empty($passwort)) {
        $fehler = 'Bitte fülle alle Pflichtfelder aus.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $fehler = 'Bitte gib eine gültige E-Mail-Adresse ein.';
    } elseif (strlen($passwort) < 6) {
        $fehler = 'Das Passwort muss mindestens 6 Zeichen lang sein.';
    } elseif ($passwort !== $passwort2) {
        $fehler = 'Die Passwörter stimmen nicht überein.';
    } else {
        try {
            $pdo = getDbConnection();
            
            // Prüfen ob E-Mail bereits existiert
            $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
            $stmt->execute([$email]);
            
            if ($stmt->fetch()) {
                $fehler = 'Diese E-Mail-Adresse ist bereits registriert.';
            } else {
                // Benutzer anlegen
                $hashedPassword = password_hash($passwort, PASSWORD_DEFAULT);
                $stmt = $pdo->prepare("INSERT INTO users (vorname, nachname, email, passwort, strasse, hausnummer, plz, ort) 
                                       VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
                $stmt->execute([$vorname, $nachname, $email, $hashedPassword, $strasse, $hausnummer, $plz, $ort]);
                
                $erfolg = 'Registrierung erfolgreich! Du kannst dich jetzt einloggen.';
            }
        } catch (PDOException $e) {
            $fehler = 'Ein Datenbankfehler ist aufgetreten. Bitte versuche es später erneut.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registrieren - Energy Pulver Konfigurator</title>
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
            
            <h2>Registrieren</h2>
            
            <?php if ($fehler): ?>
                <div class="alert alert-danger">
                    <i class="bi bi-exclamation-circle"></i> <?= escape($fehler) ?>
                </div>
            <?php endif; ?>
            
            <?php if ($erfolg): ?>
                <div class="alert alert-success">
                    <i class="bi bi-check-circle"></i> <?= escape($erfolg) ?>
                    <br><a href="login.php" class="mt-2 d-inline-block">Jetzt einloggen</a>
                </div>
            <?php else: ?>
            
            <form method="POST" action="register.php">
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-floating">
                            <input type="text" class="form-control" id="vorname" name="vorname" 
                                   placeholder="Vorname" value="<?= escape($vorname ?? '') ?>" required>
                            <label for="vorname">Vorname *</label>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-floating">
                            <input type="text" class="form-control" id="nachname" name="nachname" 
                                   placeholder="Nachname" value="<?= escape($nachname ?? '') ?>" required>
                            <label for="nachname">Nachname *</label>
                        </div>
                    </div>
                </div>
                
                <div class="form-floating">
                    <input type="email" class="form-control" id="email" name="email" 
                           placeholder="E-Mail" value="<?= escape($email ?? '') ?>" required>
                    <label for="email">E-Mail-Adresse *</label>
                </div>
                
                <div class="form-floating">
                    <input type="password" class="form-control" id="passwort" name="passwort" 
                           placeholder="Passwort" required minlength="6">
                    <label for="passwort">Passwort * (min. 6 Zeichen)</label>
                </div>
                
                <div class="form-floating">
                    <input type="password" class="form-control" id="passwort2" name="passwort2" 
                           placeholder="Passwort wiederholen" required>
                    <label for="passwort2">Passwort wiederholen *</label>
                </div>
                
                <hr class="my-4">
                <p class="text-muted small">Optionale Angaben für spätere Bestellungen:</p>
                
                <div class="row">
                    <div class="col-md-8">
                        <div class="form-floating">
                            <input type="text" class="form-control" id="strasse" name="strasse" 
                                   placeholder="Straße" value="<?= escape($strasse ?? '') ?>">
                            <label for="strasse">Straße</label>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-floating">
                            <input type="text" class="form-control" id="hausnummer" name="hausnummer" 
                                   placeholder="Nr." value="<?= escape($hausnummer ?? '') ?>">
                            <label for="hausnummer">Nr.</label>
                        </div>
                    </div>
                </div>
                
                <div class="row">
                    <div class="col-md-4">
                        <div class="form-floating">
                            <input type="text" class="form-control" id="plz" name="plz" 
                                   placeholder="PLZ" value="<?= escape($plz ?? '') ?>">
                            <label for="plz">PLZ</label>
                        </div>
                    </div>
                    <div class="col-md-8">
                        <div class="form-floating">
                            <input type="text" class="form-control" id="ort" name="ort" 
                                   placeholder="Ort" value="<?= escape($ort ?? '') ?>">
                            <label for="ort">Ort</label>
                        </div>
                    </div>
                </div>
                
                <button type="submit" class="btn btn-neon w-100 mt-4">
                    <i class="bi bi-person-plus"></i> Registrieren
                </button>
            </form>
            
            <?php endif; ?>
            
            <div class="auth-links">
                Bereits registriert? <a href="login.php">Jetzt einloggen</a>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
