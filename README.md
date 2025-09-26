# LSPD Management Anwendung (PHP-Version)

Dies ist eine voll funktionsfähige, serverseitige Webanwendung zur Verwaltung von LSPD-Operationen, geschrieben in PHP, HTML, CSS und JavaScript. Sie ist für den Betrieb auf einem Standard-Webspace mit PHP- und MySQL-Unterstützung (z.B. von all-inkl) ausgelegt.

## Funktionen

*   **Benutzer-Authentifizierung**: Sicheres Login-System für alle Beamten.
*   **Dynamisches Dispatch-Board**: Echtzeit-Ansicht der Einheiten im Dienst. Zuweisung von Beamten zu Fahrzeugen per Drag & Drop und dynamische Änderung von Status, Funkkanal und Callsign.
*   **Umfassende Personalverwaltung**:
    *   Anlegen, Bearbeiten und Anzeigen von Beamten.
    *   Verhängen und Anzeigen von Sanktionen.
    *   Verwaltung von Benutzer-Zugangsdaten (Passwort-Reset).
*   **"Mein Dienst"-Seite**: Persönliche Übersichtsseite für jeden Beamten mit Stempeluhr und Lizenz-Anzeige.
*   **FTO-Funktionen**:
    *   Verwaltung von Trainings-Modulen.
    *   Verwaltung und Nachverfolgung von Checklisten für Beamte.
*   **Fuhrpark-Verwaltung**: Stammdatenverwaltung für alle Fahrzeuge der Flotte.
*   **Dokumenten- und Mailsystem**: Internes System zur Verwaltung von Dokumenten und zum Austausch von Nachrichten.
*   **Protokollierung**: Alle wichtigen Aktionen im System werden in einer IT-Protokolldatei erfasst.

## Einrichtungsanleitung (für all-inkl oder ähnliche Hoster)

Folgen Sie diesen Schritten, um die Anwendung auf Ihrem Webspace zu installieren:

### 1. Datenbank einrichten

1.  **Datenbank erstellen**: Loggen Sie sich in Ihr KAS (Kundenadministrationssystem) bei all-inkl ein und erstellen Sie eine neue MySQL-Datenbank. Notieren Sie sich den Datenbanknamen, den Benutzernamen und das Passwort.
2.  **Datenbank-Tabellen importieren**:
    *   Öffnen Sie die `setup.sql`-Datei aus diesem Projekt.
    *   Gehen Sie im KAS zu Ihrer neuen Datenbank und öffnen Sie phpMyAdmin.
    *   Klicken Sie in phpMyAdmin auf den Reiter "Importieren".
    *   Wählen Sie die `setup.sql`-Datei aus und führen Sie den Import durch. Alle notwendigen Tabellen werden nun erstellt.

### 2. Konfigurationsdatei anpassen

1.  **`config.php` bearbeiten**: Öffnen Sie die Datei `config.php` in einem Texteditor.
2.  **Zugangsdaten eintragen**: Ersetzen Sie die Platzhalter `db_name_here`, `db_user_here` und `db_password_here` mit den echten Zugangsdaten Ihrer soeben erstellten Datenbank.
    ```php
    define('DB_HOST', 'localhost');      // Meistens 'localhost', kann bei all-inkl abweichen.
    define('DB_NAME', 'IHR_DB_NAME');   // z.B. 'd0123456'
    define('DB_USER', 'IHR_DB_USER');   // z.B. 'dbo123456'
    define('DB_PASS', 'IHR_DB_PASSWORT'); // Ihr Datenbankpasswort
    ```

### 3. Dateien hochladen

1.  **FTP-Zugang nutzen**: Verbinden Sie sich mit einem FTP-Programm (z.B. FileZilla) mit Ihrem Webspace.
2.  **Dateien kopieren**: Laden Sie alle Dateien und Ordner dieses Projekts in das gewünschte Verzeichnis auf Ihrem Webspace hoch (z.B. in einen Unterordner wie `/lspd-tool`).

### 4. Ersten Benutzer anlegen (WICHTIG)

Die Anwendung hat nach der Installation noch keine Benutzer. Sie müssen den ersten Administrator-Account manuell anlegen.

1.  **phpMyAdmin öffnen**: Gehen Sie erneut zu Ihrer Datenbank in phpMyAdmin.
2.  **`officers`-Tabelle**: Klicken Sie auf die Tabelle `officers` und dann auf den Reiter "Einfügen". Erstellen Sie einen neuen Beamten (z.B. mit Ihrem Namen) und merken Sie sich die `id` (sollte `1` sein).
3.  **`users`-Tabelle**: Klicken Sie auf die Tabelle `users` und dann auf "Einfügen".
    *   `officer_id`: Tragen Sie hier die `id` des soeben erstellten Beamten ein (z.B. `1`).
    *   `username`: Wählen Sie einen Benutzernamen (z.B. `admin`).
    *   `password_hash`: **Sehr wichtig!** Wählen Sie bei der Funktion `VARCHAR(255)` die Option `PASSWORD` aus dem Dropdown-Menü aus. Geben Sie dann im Feld daneben Ihr gewünschtes Passwort ein. phpMyAdmin wird es automatisch sicher hashen.
4.  **Admin-Rechte zuweisen (Optional)**: Um sich selbst zum Admin zu machen, gehen Sie zur Tabelle `officer_departments`, klicken auf "Einfügen" und tragen Ihre `officer_id` und die `department_id` für "Admin" ein (diese ist standardmäßig `9`).

### 5. Anwendung aufrufen

Sie können die Anwendung nun im Browser aufrufen, indem Sie die URL zu dem Verzeichnis aufrufen, in das Sie die Dateien hochgeladen haben (z.B. `ihre-domain.de/lspd-tool/`). Sie sollten die Login-Seite sehen und sich mit dem soeben erstellten Benutzer anmelden können.

---
Das Projekt ist nun bereit für die Bereitstellung.