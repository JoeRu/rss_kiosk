# RSS News Kiosk

Modern RSS News Viewer mit Svelte Frontend und PHP Backend - Ein vollständiges Kiosk-System für die Anzeige von Nachrichten aus RSS-Feeds.

## 📋 Inhaltsverzeichnis

- [RSS News Kiosk](#rss-news-kiosk)
  - [📋 Inhaltsverzeichnis](#-inhaltsverzeichnis)
  - [Überblick](#überblick)
    - [Features](#features)
  - [Systemanforderungen](#systemanforderungen)
    - [Software](#software)
    - [Optional](#optional)
  - [Initiale Inbetriebnahme](#initiale-inbetriebnahme)
    - [1. Repository klonen](#1-repository-klonen)
    - [2. PHP-Abhängigkeiten installieren](#2-php-abhängigkeiten-installieren)
    - [3. Frontend-Abhängigkeiten installieren](#3-frontend-abhängigkeiten-installieren)
    - [4. Konfiguration anpassen](#4-konfiguration-anpassen)
    - [5. Cache-Verzeichnis vorbereiten](#5-cache-verzeichnis-vorbereiten)
    - [6. Backend starten](#6-backend-starten)
      - [Option A: Apache Webserver (Empfohlen für Produktion)](#option-a-apache-webserver-empfohlen-für-produktion)
      - [Option B: PHP Built-in Server (Für Entwicklung)](#option-b-php-built-in-server-für-entwicklung)
    - [7. Frontend starten](#7-frontend-starten)
    - [8. Anwendung öffnen](#8-anwendung-öffnen)
  - [Produktiv-Deployment](#produktiv-deployment)
    - [1. Frontend bauen](#1-frontend-bauen)
    - [2. Apache konfigurieren](#2-apache-konfigurieren)
    - [3. Apache neu starten](#3-apache-neu-starten)
    - [4. Kiosk-Modus (Optional)](#4-kiosk-modus-optional)
  - [Konfiguration](#konfiguration)
    - [API-Konfiguration (`api/config.php`)](#api-konfiguration-apiconfigphp)
    - [Frontend-Konfiguration (`frontend/vite.config.js`)](#frontend-konfiguration-frontendviteconfigjs)
  - [Architektur](#architektur)
    - [Backend-Komponenten](#backend-komponenten)
    - [Frontend-Komponenten](#frontend-komponenten)
    - [Datenfluss](#datenfluss)
  - [Fehlerbehebung](#fehlerbehebung)
    - [Problem: "Composer not found"](#problem-composer-not-found)
    - [Problem: "npm not found"](#problem-npm-not-found)
    - [Problem: Keine Nachrichten werden angezeigt](#problem-keine-nachrichten-werden-angezeigt)
    - [Problem: CORS-Fehler im Browser](#problem-cors-fehler-im-browser)
    - [Problem: Cache wird nicht aktualisiert](#problem-cache-wird-nicht-aktualisiert)
    - [Problem: Port bereits belegt](#problem-port-bereits-belegt)
  - [Support \& Weiterentwicklung](#support--weiterentwicklung)

## Überblick

Das RSS News Kiosk System besteht aus zwei Hauptkomponenten:

- **Backend (PHP)**: REST API für Feed-Abruf, Caching und Datenbereitstellung
- **Frontend (Svelte)**: Moderne Single-Page-Application mit Auto-Carousel und manueller Steuerung

### Features

✅ Automatisches Carousel mit konfigurierbarer Geschwindigkeit  
✅ Mehrere RSS-Feeds gleichzeitig  
✅ Intelligentes Caching-System  
✅ QR-Codes für jeden Artikel  
✅ **Intelligente Bildextraktion** - Bilder werden aus dem Content extrahiert und prominent angezeigt  
✅ **Automatische Textkürzung** - Texte werden auf 2 Sätze oder 70 Zeichen begrenzt  
✅ Responsive Design  
✅ Dunkles Theme  
✅ Manuelle Navigation möglich  
✅ Auto-Refresh der Feeds  
✅ VS Code Integration mit Tasks für schnellen Start  

## Systemanforderungen

### Software

- **PHP**: Version 7.4 oder höher
  - **Erforderliche Extensions:**
    - `ext-openssl` (für Composer/SSL)
    - `ext-mbstring` (für QR-Code-Generator)
    - `ext-simplexml` (für RSS-Parsing)
- **Composer**: PHP Dependency Manager
- **Node.js**: Version 16 oder höher
- **npm**: Node Package Manager (kommt mit Node.js)
- **Webserver**: Apache oder PHP Built-in Server

### Optional

- **Git**: Für Repository-Verwaltung
- **Apache**: Für Produktiv-Deployment (empfohlen)

## Initiale Inbetriebnahme

Folgen Sie diesen Schritten nacheinander für eine erfolgreiche Installation:

### 1. Repository klonen

Öffnen Sie PowerShell oder die Eingabeaufforderung und navigieren Sie zum gewünschten Verzeichnis:

```powershell
# Navigieren Sie zum gewünschten Installationsort

# Klonen Sie das Repository (falls noch nicht vorhanden)
git clone <REPOSITORY_URL> rss_kiosk

# Wechseln Sie in das Projektverzeichnis
cd rss_kiosk
```

### 2. PHP-Extensions aktivieren

**⚠️ WICHTIG:** Vor der Installation müssen die erforderlichen PHP-Extensions aktiviert werden:

```powershell
# PHP-Konfigurationsdatei finden
php --ini

# Bearbeiten Sie die angezeigte php.ini Datei
# Entfernen Sie das Semikolon (;) vor diesen Zeilen:
#   extension=openssl
#   extension=mbstring
#   extension=simplexml

# Überprüfen Sie, dass die Extensions geladen sind
php -m | Select-String "openssl|mbstring|simplexml"
```

### 3. PHP-Abhängigkeiten installieren

Installieren Sie die benötigten PHP-Bibliotheken mit Composer:

```powershell
# Im Hauptverzeichnis des Projekts
composer install
```

**Was wird installiert:**
- `dg/rss-php`: RSS/Atom Feed Parser
- `chillerlan/php-qrcode`: QR-Code-Generator

**Erwartete Ausgabe:**
```
Loading composer repositories with package information
Installing dependencies from lock file
Package operations: X installs...
```

**Troubleshooting:**
- **"openssl extension is required"**: Aktivieren Sie `extension=openssl` in php.ini
- **"ext-mbstring * is missing"**: Aktivieren Sie `extension=mbstring` in php.ini
- Falls Composer nicht gefunden wird: [Composer installieren](https://getcomposer.org/download/)
- Bei Fehlern wegen PHP-Version: Stellen Sie sicher, dass PHP 7.4+ installiert ist

### 4. Frontend-Abhängigkeiten installieren

Wechseln Sie ins Frontend-Verzeichnis und installieren Sie die Node.js-Pakete:

```powershell
# Wechsel ins Frontend-Verzeichnis
cd frontend

# Node.js-Pakete installieren
npm install

# Zurück ins Hauptverzeichnis
cd ..
```

**Was wird installiert:**
- `svelte`: JavaScript-Framework
- `vite`: Build-Tool und Dev-Server
- `@sveltejs/vite-plugin-svelte`: Svelte-Integration für Vite

**Erwartete Ausgabe:**
```
added XXX packages in XXs
```

**Troubleshooting:**
- Falls npm nicht gefunden wird: [Node.js installieren](https://nodejs.org/)
- Bei Netzwerkproblemen: `npm install --verbose` für Details

### 5. Umgebungskonfiguration (Optional)

Für benutzerdefinierte Einstellungen:

```powershell
# Kopieren Sie die Beispielkonfiguration
cd frontend
copy .env.example .env.local

# Bearbeiten Sie .env.local nach Bedarf:
# VITE_API_TARGET=http://127.0.0.1:8000  (Standard für PHP Built-in Server)
# VITE_BASE_PATH=/rss_kiosk               (Deployment-Pfad)
cd ..
```

### 6. Konfiguration anpassen

Bearbeiten Sie die Datei `api/config.php` nach Ihren Bedürfnissen:

```powershell
# Öffnen Sie die Datei in Ihrem bevorzugten Editor
notepad api/config.php
```

**Wichtige Einstellungen:**

```php
return [
    // Aktualisierungsintervall in Sekunden
    'refresh_interval' => 300, // 5 Minuten
    
    // RSS-Feed-URLs
    'feeds' => [
        'https://www.tagesschau.de/xml/rss2',
        'https://rss.sueddeutsche.de/rss/Alles',
        'https://www.hr3.de/index.rss',
        // Fügen Sie hier weitere Feeds hinzu
    ],
    
    // Cache-Einstellungen
    'cache' => [
        'enabled' => true,
        'ttl' => 300, // 5 Minuten
    ],
    
    // Anzeige-Einstellungen
    'display' => [
        'max_items' => 50,
        'shuffle' => true,
        'exclude_keywords' => ['hr3 app', 'hr3 skill']
    ]
];
```

**Anpassungen:**
- Fügen Sie eigene RSS-Feeds hinzu oder entfernen Sie unerwünschte
- Passen Sie `refresh_interval` an (in Sekunden)
- Konfigurieren Sie `max_items` für die maximale Anzahl anzuzeigender Nachrichten
- Definieren Sie `exclude_keywords` zum Filtern unerwünschter Artikel

### 7. Cache-Verzeichnis vorbereiten

Stellen Sie sicher, dass das Cache-Verzeichnis existiert und beschreibbar ist:

```powershell
# Prüfen, ob das Cache-Verzeichnis existiert
Test-Path cache

# Falls es nicht existiert, wird es automatisch erstellt
# Stellen Sie sicher, dass die Berechtigungen korrekt sind
```

Das Verzeichnis sollte bereits vorhanden sein. Falls nicht:
```powershell
New-Item -ItemType Directory -Path cache
```

### 8. Entwicklungsumgebung starten

**Option 1: VS Code Tasks (Empfohlen)**

Wenn Sie VS Code verwenden:

1. Drücken Sie `Ctrl+Shift+P`
2. Wählen Sie "Tasks: Run Task"
3. Wählen Sie "Start Dev Environment"
4. Beide Server (PHP und Vite) starten automatisch
5. Öffnen Sie `http://localhost:5173`

**Option 2: Manuell - Backend und Frontend starten**

Sie haben zwei Optionen für das Backend:

#### Option A: Apache Webserver (Empfohlen für Produktion)

Falls Apache bereits auf Port 8087 läuft:

```powershell
# Stellen Sie sicher, dass Apache läuft und auf das Projektverzeichnis zeigt
# Keine weiteren Schritte erforderlich - Backend ist verfügbar unter:
# http://localhost:8087/api/
```

**Apache-Konfiguration prüfen:**
- DocumentRoot sollte auf `Z:\rss_kiosk` zeigen
- PHP-Modul sollte aktiviert sein

#### Option B: PHP Built-in Server (Für Entwicklung)

Öffnen Sie ein neues PowerShell-Fenster:

```powershell
# Im Hauptverzeichnis des Projekts
cd C:\Pfad\zum\rss_kiosk

# PHP Built-in Server starten (WICHTIG: 127.0.0.1 verwenden!)
php -S 127.0.0.1:8000 -t .
```

**⚠️ Wichtig:** Verwenden Sie `127.0.0.1` statt `localhost`, um IPv6-Bindungsprobleme zu vermeiden.

**Erwartete Ausgabe:**
```
[Sun Nov 23 10:00:00 2025] PHP 8.x Development Server started
```

**Hinweis:** Die Vite-Konfiguration ist bereits auf `http://127.0.0.1:8000` eingestellt.

**Lassen Sie dieses Fenster geöffnet!**

### 9. Frontend starten

Öffnen Sie ein **neues** PowerShell-Fenster:

```powershell
# Wechseln Sie ins Frontend-Verzeichnis
cd Z:\rss_kiosk\frontend

# Vite Development Server starten
npm run dev
```

**Erwartete Ausgabe:**
```
  VITE v5.x.x  ready in XXX ms

  ➜  Local:   http://localhost:5173/
  ➜  Network: http://192.168.x.x:5173/
  ➜  press h to show help
```

**Lassen Sie auch dieses Fenster geöffnet!**

### 10. Anwendung öffnen

Öffnen Sie Ihren Webbrowser und navigieren Sie zu:

```
http://localhost:5173
```

**Was Sie sehen sollten:**
- Eine dunkle Oberfläche mit News-Artikeln
- **Bilder links vom Text** (automatisch aus dem Content extrahiert)
- **Kurze, prägnante Texte** (automatisch auf 1-2 Sätze gekürzt)
- Automatisches Durchblättern (Carousel)
- QR-Codes rechts oben neben jedem Artikel
- Steuerungselemente am unteren Rand

**Falls keine Nachrichten angezeigt werden:**
1. Öffnen Sie die Browser-Konsole (F12)
2. Prüfen Sie auf Fehler
3. Überprüfen Sie, ob das Backend läuft (siehe Schritt 6)
4. Testen Sie den API-Endpoint direkt: `http://localhost:8087/api/index.php?action=news`

## Produktiv-Deployment

Für den Produktivbetrieb erstellen Sie ein optimiertes Build:

### 1. Frontend bauen

```powershell
cd frontend
npm run build
```

Dies erstellt einen `dist/`-Ordner mit optimierten Dateien.

### 2. Apache konfigurieren

**Beispiel VirtualHost (Apache):**

```apache
<VirtualHost *:80>
    ServerName rss-kiosk.local
    DocumentRoot "Z:/rss_kiosk/frontend/dist"
    
    <Directory "Z:/rss_kiosk/frontend/dist">
        Options -Indexes +FollowSymLinks
        AllowOverride All
        Require all granted
    </Directory>
    
    Alias /api "Z:/rss_kiosk/api"
    <Directory "Z:/rss_kiosk/api">
        Options -Indexes
        AllowOverride None
        Require all granted
    </Directory>
</VirtualHost>
```

### 3. Apache neu starten

```powershell
# Apache-Dienst neu starten
Restart-Service -Name "Apache*"
```

### 4. Kiosk-Modus (Optional)

Für einen Vollbild-Kiosk öffnen Sie Chrome/Edge im Kiosk-Modus:

```powershell
# Chrome Kiosk-Modus
Start-Process chrome.exe --kiosk "http://localhost/rss-kiosk" --start-fullscreen

# Edge Kiosk-Modus
Start-Process msedge.exe --kiosk "http://localhost/rss-kiosk" --start-fullscreen
```

## Konfiguration

### API-Konfiguration (`api/config.php`)

| Option | Beschreibung | Standardwert |
|--------|--------------|--------------|
| `refresh_interval` | Aktualisierungsintervall in Sekunden | 300 (5 Min) |
| `feeds` | Array mit RSS-Feed-URLs | 3 Beispiel-Feeds |
| `cache.enabled` | Cache aktivieren/deaktivieren | true |
| `cache.ttl` | Cache-Lebensdauer in Sekunden | 300 |
| `display.max_items` | Max. Anzahl News-Items | 50 |
| `display.shuffle` | Nachrichten mischen | true |
| `display.exclude_keywords` | Filterschlüsselwörter | ['hr3 app', 'hr3 skill'] |

### Frontend-Konfiguration (`frontend/vite.config.js`)

```javascript
export default defineConfig({
  plugins: [svelte()],
  server: {
    host: '0.0.0.0', // Für Netzwerkzugriff
    port: 5173,
    proxy: {
      '/api': {
        target: 'http://localhost:8087', // @todo: Backend-URL anpassen
        changeOrigin: true
      }
    }
  }
})
```

## Architektur

### Backend-Komponenten

```
api/
├── index.php           # REST API Endpoints
├── FeedService.php     # Feed-Abruf und Caching
└── config.php          # Zentrale Konfiguration
```

**API Endpoints:**

- `GET /api/?action=news` - Alle News-Items
- `GET /api/?action=config` - Aktuelle Konfiguration
- `GET /api/?action=status` - System-Status

### Frontend-Komponenten

```
frontend/src/
├── App.svelte              # Hauptkomponente
├── main.js                 # Entry Point
├── components/
│   ├── NewsCarousel.svelte # Carousel-Logik
│   ├── NewsItem.svelte     # Einzelner News-Artikel
│   └── Settings.svelte     # Einstellungs-Dialog
└── stores/
    └── newsStore.js        # Zustandsverwaltung
```

### Datenfluss

```
RSS-Feeds → FeedService (PHP) → Cache → REST API → Svelte Store → UI
                ↑                                         ↓
                └─────── Auto-Refresh (5 Min) ───────────┘
```

## Fehlerbehebung

### Problem: "Composer not found"

**Lösung:**
```powershell
# Composer global installieren
php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');"
php composer-setup.php --install-dir=C:\bin --filename=composer
```

### Problem: "npm not found"

**Lösung:** Installieren Sie [Node.js](https://nodejs.org/) (LTS-Version empfohlen)

### Problem: Keine Nachrichten werden angezeigt

**Checkliste:**
1. Backend läuft? → Testen Sie `http://localhost:8087/api/?action=news`
2. Feeds erreichbar? → Prüfen Sie die URLs in `config.php`
3. Cache-Verzeichnis beschreibbar? → Prüfen Sie Berechtigungen
4. Browser-Konsole prüfen (F12) → Suchen Sie nach Fehlermeldungen

### Problem: CORS-Fehler im Browser

**Lösung:** Stellen Sie sicher, dass Vite-Proxy korrekt konfiguriert ist:
```javascript
// frontend/vite.config.js
proxy: {
  '/api': {
    target: 'http://localhost:8087',
    changeOrigin: true
  }
}
```

### Problem: Cache wird nicht aktualisiert

**Lösung:**
```powershell
# Cache manuell löschen
Remove-Item cache\feed_cache.json

# Oder Cache-TTL in config.php reduzieren
'ttl' => 60 // 1 Minute für Tests
```

### Problem: Port bereits belegt

**Lösung:**
```powershell
# Port-Nutzung prüfen
netstat -ano | findstr :5173

# Prozess beenden (PID aus obigem Befehl)
taskkill /PID <PID> /F

# Oder anderen Port verwenden
npm run dev -- --port 5174
```

---

## Support & Weiterentwicklung

Bei Fragen oder Problemen:

1. Prüfen Sie diese README
2. Konsultieren Sie `QUICKSTART.md` für schnelle Referenz
3. Schauen Sie in die Browser-Konsole (F12)
4. Prüfen Sie die PHP-Logs

**Viel Erfolg mit Ihrem RSS News Kiosk!** 🎉
