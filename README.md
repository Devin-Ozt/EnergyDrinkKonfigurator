# Energy Drink Konfigurator

Ein webbasierter Produkt-Konfigurator für individualisierte Energy Drinks – perfekt für die Gaming-Community!

## Features

- **Landing Page** mit Produktübersicht
- **User-Registrierung & Login** (E-Mail + Passwort)
- **5-Schritte-Konfigurator:**
  1. Koffein-Level wählen (50mg, 100mg, 150mg, 200mg)
  2. Geschmacksrichtungen kombinieren (20+ Optionen)
  3. Funktionale Zusätze (Vitamine, Taurin, Guarana, L-Theanin)
  4. Süßungsmittel wählen
  5. Personalisierung (Name + Dosenfarbe)
- **Visuelle Echtzeit-Vorschau** der Dose
- **Preisberechnung** in Echtzeit
- **Konfiguration speichern** (für eingeloggte User)
- **Zusammenfassung** mit Bestellbutton

### Zusatzfunktionen
1. **Gutschein-System** mit Rabattcodes
2. **Vorkonfigurierte Presets** zum Anpassen

## Installation

### Voraussetzungen
- Docker & Docker Compose installiert

### Starten
```bash
docker-compose up -d
```

### Zugriff
- **Webseite:** http://localhost
- **phpMyAdmin:** http://localhost:8081

### Datenbank-Zugangsdaten
- **Host:** db
- **Datenbank:** meine_db
- **Benutzer:** benutzer
- **Passwort:** benutzerpasswort
- **Root-Passwort:** rootpasswort

### Ersteinrichtung
1. Öffne http://localhost/install.php
2. Die Datenbank-Tabellen werden automatisch erstellt
3. Beispieldaten werden eingefügt

## Preismodell
- Basispreis: 3,95€
- Premium-Zutaten: +0,30€ pro Zutat
- Personalisierung inklusive

## Technologie-Stack
- **Frontend:** HTML5, CSS3, JavaScript (Vanilla), Bootstrap 5
- **Backend:** PHP 8.4 (ohne Framework)
- **Datenbank:** MariaDB
- **Container:** Docker

## Projektstruktur
```
├── docker-compose.yml
├── README.md
├── database_dump.sql
└── src/
    ├── index.php          # Landing Page
    ├── register.php       # Registrierung
    ├── login.php          # Login
    ├── logout.php         # Logout
    ├── konfigurator.php   # Hauptkonfigurator
    ├── summary.php        # Zusammenfassung
    ├── meine-drinks.php   # Gespeicherte Konfigurationen
    ├── install.php        # Datenbankinstallation
    ├── config/
    │   └── database.php   # DB-Verbindung
    ├── api/
    │   ├── save_config.php
    │   ├── load_config.php
    │   ├── check_coupon.php
    │   └── get_presets.php
    ├── css/
    │   └── style.css
    ├── js/
    │   └── konfigurator.js
    └── images/
        └── ...
```
