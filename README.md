# Aufgabenmanagement Webapplikation

## Kurzbeschreibung

Diese Webapplikation dient zur Verwaltung von Aufgaben innerhalb einer Organisation. Benutzer können Aufgaben erstellen, bearbeiten und löschen. Jede Aufgabe kann einem spezifischen **Objekt** und einer **Einheit** zugeordnet werden, sowie einem **Monteur**. Zusätzlich ermöglicht die Applikation das Hochladen und Verwalten von Dateien, die mit den jeweiligen Aufgaben verknüpft sind.

### Wichtige Funktionen:
- **Anmeldung und Authentifizierung**: Benutzer müssen sich anmelden, um auf die Applikation zuzugreifen.
- **Aufgabenerstellung**: Benutzer können neue Aufgaben mit Titel, Beschreibung, Priorität, Status und Fälligkeitsdatum erstellen.
- **Aufgabenbearbeitung**: Bestehende Aufgaben können jederzeit bearbeitet und aktualisiert werden.
- **Datei-Upload**: Dateien können zu einer Aufgabe hochgeladen und verwaltet werden.
- **Aufgabenstatus**: Aufgaben können verschiedene Status wie "Ausstehend", "In Bearbeitung" oder "Erledigt" haben.
- **Filterung und Sortierung**: Aufgaben können nach verschiedenen Kriterien gefiltert und sortiert werden.

---

## ToDo-Liste

- [x] Benutzeranmeldung und Authentifizierung implementieren
- [x] Datenbankstruktur für Aufgaben, Monteure und Objekte erstellen
- [x] Aufgaben erstellen und bearbeiten
- [x] Priorität und Status für Aufgaben festlegen
- [x] Fälligkeitsdatum für Aufgaben hinzufügen
- [x] Verknüpfung von Aufgaben mit Monteuren und Objekten
- [x] Datei-Upload für Aufgaben implementieren
- [ ] Hochgeladene Dateien anzeigen und verwalten (z.B. Löschen)
- [ ] Aufgaben nach Priorität, Status und Fälligkeitsdatum filtern
- [ ] Paginierung für die Aufgabenübersicht hinzufügen
- [ ] Sicherheit: CSRF-Schutz und Validierung der Eingaben verbessern
- [ ] Benachrichtigungssystem für bevorstehende oder überfällige Aufgaben implementieren
- [ ] Responsives Design für mobile Geräte optimieren

---

## Installation

1. **Klonen des Repositories**:
   ```bash
   git clone https://github.com/yourusername/yourrepository.git