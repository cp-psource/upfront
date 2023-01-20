Change Log
============

1.9.5 - 2023-01-16
-------------------------------------------------------------------------------

- HINWEIS: Nun PSOURCE!

- Angepasst: Auf Deutsch übersetzt inkl. Quellcode

- Hinzugefügt: PSource Updater
- Hinzugefügt: Aktuelle Hilfequellen, Tutorials und Links auf Deutsch

- Fix: Veraltetes .click() jQuery
- Fix: Styling für das Kontrollkästchen im Popup "Das ist der Editor ..." in Firefox

1.9.4 - 2017-10-16
-------------------------------------------------------------------------------
- Hinzugefügt: Aufräumen von PHP-Funktionsaufrufen

- Fix: Akkordeon-Element-UI-Schaltflächen überlappen sich
- Fix: Styling für das Kontrollkästchen im Popup "Das ist der Editor ..." in Firefox
- Fix: Das Posts/Pages-Popup wird auf Subsites manchmal nicht richtig geladen
- Fix: Das Umbenennen der Seite in der Seitenleiste funktioniert nicht
- Fix: WooCommerce-Produktseite zeigt Beiträge statt Produkte
- Fix: Videoelement "Erstes Video zu Thumbnails hinzufügen" kann nicht deaktiviert werden
- Fix: Undo/Redo-Befehle sollten im Responsive-Modus nicht sichtbar sein
- Fix: Sichtbarkeitsoption "Sticky" in den Seiteneinstellungen kann nicht gespeichert werden
- Fix: Vollbild-Regionsbild verschwindet nach einiger Zeit


1.9.3 - 2017-08-23
-------------------------------------------------------------------------------
- Hinzugefügt: Video nur laden, wenn der Benutzer in YouTube Element auf Wiedergabe klickt
- Hinzugefügt: Beitragsinhalt richtig anzeigen, wenn der Inhalt "mehr"-Tags enthält
- Fix: Fehlerhafte Archiv-URL wird nicht richtig zu 404 aufgelöst
- Fix: Der Dialog Bilder importieren wird angezeigt, wenn der Editor gestartet wird
- Fix: Tabs-Elementtext wird jetzt korrekt gespeichert
- Fix: Das Festlegen des Anzeigestils von YouTube-Elementen auf Liste löst einen JavaScript-Fehler aus
- Fix: Codeelement sieht im Editor und im Frontend nicht gleich aus
- Fix: Kategorie- und Archiv-Widget-Optionen funktionieren jetzt richtig
- Fix: Die Größenänderung von Galerieelementen und das Beenden des Editors führen dazu, dass PHP-Hinweise angezeigt werden
- Fix: Menüelement-Posttyp-Dropdown hinter anderen Elementen versteckt
- Fix: Das Label "Name" des Kontaktelements kann nicht bearbeitet werden
- Fix: Die automatische Wiedergabe der YouTube-Elementeinstellung unterbricht die OK-Schaltfläche

1.9.2 - 2017-08-16
-------------------------------------------------------------------------------
- Fix: YouTube-Element fügt ein Video hinzu, wenn Sie außerhalb des Klicks ein Videosteuerelement hinzufügen
- Fix: Rahmenoptionen für Akkordeon-Elemente
- Fix: Post Part Settings - Teile fehlen
- Fix: Bilder von Galerieelementen können beim Bearbeiten der Bildunterschrift versehentlich ausgetauscht werden
- Fix: Beitragselement-Autorenteil schneidet Text ab
- Fix: Die Schaltfläche "Schriftarten verwalten" ist nicht immer aktiv
- Fix: Die Auswahl des Kalenders im Widget-Element verursacht einen Fehler
- Fix: Dem Beitrag hinzugefügtes Menüelement wird im Frontend nicht angezeigt
- Fix: Das Kontaktformular sendet E-Mails in einigen Fällen nicht richtig
- Fix: Kartenelementkarte bricht aus Elementgrenzen heraus
- Fix: Checkbox fehlt im "This is Editor Interface..."-Dialog
- Fix: Optionsfelder für Seiteneinstellungen sind abgeschnitten
- Fix: Schaltflächenelement-Einstellungen runden Ecken gesperrtes Symbol ist nicht ausgerichtet
- Fix: Schaltflächen für die Seiteneinstellungen sind falsch ausgerichtet
- Fix: Das Kontaktformular-Element zeigt die Absender-E-Mail nicht an
- Fix: Formularvalidierungsoptionen für Kontaktformularelemente sind nicht ausgerichtet
- Fix: Filteroptionen der Mediengalerie überlappen sich
- Fix: Die Optionen für die Show-Steuerelemente des Slider-Elements sind nicht ausgerichtet
- Fix: Optionsfelder für Post-Einstellungen sind nicht ausgerichtet
- Fix: Die Hintergrundfarbe des Akkordeon-Elements wurde nicht richtig angewendet
- Fix: Kontaktformular-Element, das SMTP deaktiviert, blendet zugehörige Einstellungen nicht aus
- Fix: Betreff und Captcha des Kontaktformular-Elements werden nicht angezeigt, bis die Einstellungen geschlossen sind
- Fix: Tabulator und Akkordeon-Element wenden Voreinstellungen erst an, wenn sie aktiviert werden
- Fix: Menüelement bricht aus schmalen Bereichen aus

1.9.1.1 - 2017-07-26
-------------------------------------------------------------------------------
- Fix: Das Festlegen der Elementauffüllung auf 0 Pixel im Editor ist standardmäßig auf 15 Pixel in Live eingestellt


1.9.1 - 2017-07-25
-------------------------------------------------------------------------------
- Hinzugefügt: Obergrenze für die Größe der Galerie-Thumbnails erhöhen
- Fix: Die Schriftfarbe der Schaltfläche wird beim Öffnen der Farbauswahl zurückgesetzt
- Fix: YouTube-Video wird weiter abgespielt, wenn Lightbox geschlossen ist
- Fix: Voreinstellungen für Beiträge werden im Frontend nicht geladen
- Fix: Die Position des Dropdown-Menüs für das Typelement ist in der Seitenleiste des Responsive-Modus nicht korrekt
- Fix: Im Medieninfo-Panel ist die Suchschaltfläche nicht anklickbar
- Fix: Beschriftungen und Felder werden in den globalen Hintergrundeinstellungen nicht richtig ausgerichtet
- Fix: OK-Schaltfläche in Regionshintergrundeinstellungen nicht richtig positioniert
- Fix: Im Kartensteuerfeld fehlen Optionen
- Fix: Schieberegler immer sichtbar, wenn Show on Hover ausgewählt ist
- Fix: Die Captcha-Steuerung ist in den Kontaktelementeinstellungen nicht richtig ausgerichtet
- Fix: Die Registerkarte "Katzen/Tags" ist in den Beitragseinstellungen nicht richtig gestaltet
- Fix: Beim Ändern der Bildgröße geht Text von der Folie im Slider-Element verloren
- Fix: Miniaturansichten der Folienreihenfolge werden in den Slider-Elementeinstellungen nicht angezeigt
- Fix: PHP-Hinweis wird protokolliert, wenn Post gelöscht wird
- Fix: Der Editor wird nicht geladen, wenn ein Drittanbieter-Skript eine asynchrone Moduldefinition verwendet

1.9 - 2017-07-12
-------------------------------------------------------------------------------
- Hinzugefügt: Neues Design für Seitenleisten-, Element- und Bereichseinstellungen.
- Hinzugefügt: Neues Design für Beiträge / Seiten / Kommentare.
- Hinzugefügt: Anderes Popup für Builder & Editor.
- Add: Performance - Reduzieren Sie die Anzahl der vom Editor ausgelösten Anfragen (JS/PHP)
- Hinzugefügt: Bessere Kompatibilität mit Hustle.
- Hinzugefügt: Bessere Kompatibilität mit Hummingbird.
- Leistungsverbesserung: Implementieren Sie eine umfassendere Nutzung des Objekt-Cachings.
- Leistungsverbesserung: Beseitigen Sie das aufgeblähte Markup, um die Leistung der Besucher im Einfriermodus zu beschleunigen.
- Leistungsverbesserung: Implementierung von Element-HTML-Caching zum Laden beim Booten.
- Leistungsverbesserung: Implementierung von Element-HTML-Caching zum Wechseln von Layouts im Editor.
- Leistungsverbesserung: Verhindert, dass das Navigationselement bei jeder Änderung und beim Laden des Layouts mehrere Anfragen stellt.

- Fix: Postet mehrere Begriffsfelder.
- Fix: Posts-Element pro Kategorie funktioniert nicht.
- Fix: Leuchtkästen bei kleiner Auflösung kaputt.
- Fix: Schwebende Regionen können nicht gelöscht werden.
- Fix: Builder speichert Änderungen nicht.
- Fix: Redactor fett & kursiv aktualisiert nur einen Teil der Auswahl.
- Fix: Einzelne Posts des Posts-Elements zeigen mehrere Posts an.

1.8.1 - 2017-05-11
-------------------------------------------------------------------------------
- Fix: Fehler, der das Löschen fester (schwebender) Regionen verhinderte.
- Fix: Link im Builder formatieren
- Fix: Registerkartenelement, das Einstellungen zurücksetzt, wenn es im Builder bearbeitet wird
- Fix: Problem mit Sticky-Unterregionen, die nicht für Vollbildregionen funktionieren
- Fix: Post-Featured-Image-Layout kann nicht bearbeitet werden

1.8 - 2017-03-29
-------------------------------------------------------------------------------
- Hinzugefügt: Neugestaltung des Medienmanagers
- Hinzugefügt: CoursePress-Kompatibilität
- Hinzugefügt: Optionen zur Unterstützung der Barrierefreiheit
- Hinzugefügt: Hierarchiedaten zum späteren Einrücken
- Hinzugefügt: Option zum Schieben mit den Pfeiltasten
- Hinzugefügt: Moduskontextdialog

- Fix: Picker-Alpha für Farbeinstellungen des Themas ausblenden
- Fix: Farbwähler ausblenden, wenn automatisches Ausblenden deaktiviert ist
- Fix: Die zugewiesene Farbe wird zurückgesetzt, wenn die Farbauswahl gestartet wird
- Fix: Lücke zwischen Responsive Body und Sidebar bei kleinen Bildschirmgrößen verhindern.
- Fix: verhindert, dass der Tooltip nach der Navigation sichtbar bleibt.
- Fix: Speichern nach Verlassen des Responsive-Modus
- Fix: Seitenbeschriftung des Schiebereglers unter dem Bild
- Fix: YT-Video hört nicht auf zu spielen, wenn Lightbox geschlossen wird
- Fix: Problem mit ausgewähltem Auswahl-Dropdown, das teilweise transparent ist.
- Fix: Problem mit Dropdown-Feldern, die beim Hover flackern.
- Fix: Startseitenelement zur Seitenliste hinzufügen.
- Fix: Fehlermeldung beim Hinzufügen eines bereits vorhandenen Labels anzeigen
- Fix: Das Suchergebnis des Media Managers zeigt die Nummer nicht richtig an
- Fix: Posts/Seiten-Popup beim Verlinken
- Fix: Melder-Z-Index hinter Medien-Popup
- Fix: Kontaktformular wird nicht auf PHP 7 gesendet
- Fix: Galeriebild alt wird nicht angezeigt
- Fix: Bildelement ALT im Frontend drucken
- Fix: Bildelement alt nicht gespeichert
- Fix: Verbesserung der Bodyclass für Plugin-Kompatibilität
- Fix: Burger-Voreinstellungsstile für vorhandene Voreinstellungen in DB
- Fix: Problem mit ausgewählten Medienelementen nach dem Hochladen
- Fix: Komprimierung von console.logs entfernen
- Fix: Bildauswahl für Hintergrundbild
- Fix: Etikettenliste nicht ausgeblendet, wenn keine Übereinstimmung vorhanden ist
- Fix: Das generische Limit für mehrere Posts funktioniert nicht
- Fix: fehlende l10n-Labels
- Fix: Verhindert, dass das Navigationsmenü kleiner als neun Spalten wird.
- Fix: Menüpunkt verliert HTML-Tags nach Änderung im Editor
- Fix: die Liste/einzelne numerische Steuereinstellungen.
- Fix: Lokale Lightboxen beim Bearbeiten globaler Regionen erneut anzeigen und löschen lassen.
- Fix: Hintergrundfehler im Vollbildbereich beim Scrollen
- Fix: Beim Bearbeiten werden benutzerdefinierte Klassen aus dem Menü entfernt
- Fix: Regionseinstellungen beim Ändern von Breakpoints ausblenden.
- Fix: Schließen Sie das Einstellungsfenster ordnungsgemäß, wenn eine Region gelöscht wird.
- Fix: Nur globale Lightboxen beim Modal "Globale Regionen bearbeiten" anzeigen.
- Fix: Löschen globaler Lightboxen zulassen.
- Fix: Update-Slider-Resize-Tipp bei Größenänderung.
- Fix: Hinweis zur Größenänderung der Gruppe beim Ändern der Größe aktualisieren.
- Fix: Falsche Bezeichnung für Post-Datenelemente in Gruppen und Breiteninkonsistenzen.
- Fix: Problem mit nicht richtig ausgerichteten Slider-Beschriftungen.
- Fix: Kästchen in Regionseinstellungen auswählen
- Fix: Problem mit überlappenden Elementen mit dem oberen Menü.

1.7 - 2017-01-10
-------------------------------------------------------------------------------
- Hinzugefügt: Neues Design für Interaktionspanels.
- Hinzugefügt: neues Design für die Elementstatus-Benutzeroberfläche.
- Hinzugefügt: Neues Design für die Gruppierungserfahrung.
- Hinzugefügt: Verbessertes Design für die Editor-Symbolleiste.
- Hinzugefügt: verbessertes Design für die Farbauswahl.
- Add: verbessertes und optimiertes Speicherverhalten.
- Hinzugefügt: Video in Hintergrundvideooptionen der Region hochladen.
- Hinzugefügt: Video-Einfügungen.
- Hinzugefügt: Die Regionseinstellung wurde in die Seitenleiste verschoben.
- Hinzugefügt: neue verbesserte Methode zur Behandlung von SMTP.

- Fix: Probleme bei der Lightbox-Erstellung.
- Fix: Aktualisieren Sie die Größe mehrerer Modulklassenelemente.
- Fix: Escape der Thumbnail-Überschreibungs-URL.
- Fix: Überprüfung des ausgewählten Bildquellentyps.
- Fix: Autorenarchive mit generischen Abfragen.
- Fix: doppelter Größenänderungshinweis für Codeelement.
- Fix: Button-Link-Panel-Icon im aktiven Zustand.
- Fix: Neue Lightbox-Feldposition für Gruppenelement.
- Fix: Tooltip wird beim Aufheben der Gruppierung nicht geschlossen.
- Fix: Problem mit der Beschriftung des Schiebereglers bei der Platzierung von Cover-Platzhaltern.
- Fix: Problem beim Bearbeiten von Menüpunkten.
- Fix: Stellen Sie sicher, dass die generierten virtuellen Links der SSL-Konvention entsprechen.
- Fix: voreingestellte Klassenausgabe auf Post-Datenelement.
- Fix: Verhindert, dass die Farbauswahl von der Bildlaufleiste der Seitenleiste überlappt wird.
- Fix: URL abgeschnitten, wenn mehr als 25 Zeichen, Anzeigeproblem im Beitrags-/Seitenbereich.
- Fix: Redactor Blockquote kehrt nicht in den Standardzustand zurück.
- Fix: Redactor-Zitat-Symbol wird nicht als aktiv markiert.
- Fix: Editor fügt Liste mit loser Auswahl hinzu.
- Fix: Gruppenbearbeitungsstile und andere kleinere Element-Hover-Stile.
- Fix: Leeres Bildelement wächst bei Größenänderung.
- Fix: Das Ändern der Produkt-Permalink-Option verursacht Probleme mit der Shop-Seite.
- Fix: globale CSS-Tippfehler.
- Fix: Media Manager gibt JS-Fehler aus.
- Fix: Konfliktlösung für Aktualisierungen überspringen, wenn Dashboard-Plugin vorhanden ist.
- Fix: Wenden Sie das von der Abfrage propagierte Beitragslimit an.
- Fix: Die Interaktion mit dem Datenelement konnte dazu führen, dass frisch geschriebene Inhalte gelöscht wurden.

1.6.1 - 2016-12-01
-------------------------------------------------------------------------------
- Fix: Fehlerkorrekturen und Verbesserungen in den Regionseinstellungen
- Fix: Abschaltung der Farbauswahl in den Seitenleisteneinstellungen
- Fix: Definieren Sie die Groß- und Kleinschreibung der Schalter und das HHVM-Problem
– Fix: Shortcode-Erweiterung auf WordPress-Codec-Implementierung umgestalten
- Fix: Drag-and-Drop-Fehler, wenn sich keine Elemente im Layout befinden
- Fix: PSeCommerce-Kompatibilitätsproblem mit Plugin von Drittanbietern

1.6 - 2016-11-23
-------------------------------------------------------------------------------
- Hinzugefügt: Wartungsmodus
- Hinzugefügt: PSeCommerce-Kompatibilität
- Hinzugefügt: Regionseinstellungen in Seitenleisten verschoben
- Hinzugefügt: bessere dynamische Bildlaufleisten

- Fix: Problem mit der Kopfzeile des Tab-Elements
- Fix: aktuelles Menüelementproblem im Editormodus
- Fix: Problem mit der Hintergrundparallaxe der Region
- Fix: Prüfen Sie, ob die Speicherplatzbegrenzung aktiviert ist, bevor Sie sie erzwingen
- Fix: Problem mit falschem Google Maps-API-Schlüssel
- Fix: Bild fügt Bildunterschriften ein
- Fix: Code-Element-Gültigkeitsprüfung
- Fix: Problem mit globalem Hintergrundbild mit fester Position

1.5 - 2016-10-24
-------------------------------------------------------------------------------
- Hinzugefügt: WooCommerce-Unterstützung
- Hinzugefügt: Polsterungseinstellung für einzelne Post-Teile auf Post-Datenelementen
- Hinzugefügt: Regionseinstellungen in die Seitenleiste verschoben
- Hinzugefügt: Umschalten zum Zurücksetzen der globalen Region, um den Optionsbereich im Admin zurückzusetzen

- Fix: Verhindern Sie das Doppelklicken auf die Polsterungssteuerung, um die Gruppenbearbeitung zu öffnen
- Fix: Region-BG-Problem, wenn die Gesamtgitterbreite größer als die Bildschirmbreite ist
- Fix: Verbessern Sie das Layout-Rendering, indem Sie endliche Timeouts auf Pubsub ändern
- Fix: Verbesserte Kompatibilität mit der Verankerung mit dem Domain Mapping-Plugin
- Fix: Tastaturgesteuerte Polsterung ist defekt
- Fix: Fehler bei der Beschriftung der Slider-Seite
- Fix: Entfernen Sie den Admin-Hinweis der Vorabvorlage zu Beitragstypen
- Fix: Element-Padding-Panel wird abgeschnitten, wenn es unten auf der Seite ist
- Fix: Upfront Notifier Z-Index
- Fix: Editor-Inline-Panel über Post-Select-Popup
- Fix: Problem beim Beenden des Akkordeon-Editors

1.4.3 - 2016-10-05
-------------------------------------------------------------------------------
- Hinzugefügt: Daten beim Speichern komprimieren
- Hinzugefügt: Schleifenoption für YouTube-Element und Videoregion
- Hinzugefügt: Optimierte Gruppeneinstellungen zur Seitenleiste
- Hinzugefügt: Verbesserte Steuerelemente zum Füllen von Elementen
- Hinzugefügt: Verbessertes Auslöseverhalten beim Einfügen von Bildern

- Fix: Standard-BG für ausgelöstes Menü nicht gerendert
- Fix: Menüelement-Inline-Link-Panel ausgeblendet, wenn unten auf der Seite
- Fix: Mehr Menü in der Seitenleiste überlappt mit Elementeinstellungsfenster
– Fix: Problem mit der Esc-Taste im Dialogfeld „Erste Schritte“ des Builders
- Fix: Überlappungsproblem mit der Inline-Bildschaltfläche
- Fix: Benutzer nur warnen, wenn mehrere Registerkarten für das aktuelle Layout geöffnet sind
- Fix: Problem mit Benachrichtigungen/Warnungen, die hinter dem Medien-Overlay verborgen sind
- Fix: Anfangszustand des Menüelements neu gestalten
- Fix: Fehler beim Löschen der Voreinstellung an einem anderen Haltepunkt
- Fix: Slider-Element mit Größenänderungsproblem der seitlichen Beschriftung
- Fix: Login-Element kann nicht gelöscht werden, nachdem das Standard-Erscheinungsbild bearbeitet wurde
- Fix: Beitrags-/Seiteneinstellung wird beim ersten Mal nicht angezeigt
- Fix: Hintergrundproblem mit Parallaxe in Firefox
- Fix: Layout-Benennungsproblem in der Admin-Liste
- Fix: Problem mit Post-Data-Meta-Elementen
- Fix: Probleme mit der RTL-Position
- Fix: Überlappendes Problem bei der Steuerung von Galeriebildern
- Fix: Neues Menü im Menüelement kann nicht erstellt werden
- Fix: verhindert, dass das Speichern als Entwurf Schattenbereiche enthält


1.4.2 - 2016-09-15
-------------------------------------------------------------------------------
- Hinzugefügt: Möglichkeit, Menüs für Haltepunkte zu wechseln.
- Hinzugefügt: Schaltfläche zum Löschen einer Gruppe von Elementen
- Hinzugefügt: Verbesserte Kontrolle über den Hintergrund verschiedener Regionen pro Haltepunkt

- Fix: Bildelementgröße beim Umschalten auf responsives Layout
- Fix: Die Größenänderung wurde beim ersten Mal nicht aktualisiert
- Fix: Probleme bei der Inhaltsaktualisierung
- Fix: Die Elemente können nicht angezeigt werden, wenn der verborgene Bereich in Responsive umgeschaltet wird
- Fix: Menüproblem nach dem Zurücksetzen des Themas
- Fix: Undefinierter Fehler beim Bearbeiten des Beitragsbildes
- Fix: Menüfehler mit wiederholtem Responsive Change


1.4.1 - 2016-09-13
-------------------------------------------------------------------------------
- Fix: Konfliktproblem bei geplanten Scans.


1.4.0 - 2016-09-07
-------------------------------------------------------------------------------
- Hinzugefügt: Unterstützung für das Meta-Beschreibungselement für Seiten.
- Hinzugefügt: Nachricht für leere globale Regionen/Leuchtkästen.
- Hinzugefügt: Changelog-Review-Bereich.
- Hinzugefügt: umgestaltetes Anmeldeelement.
- Hinzugefügt: Zulassen, dass auf jede Folie pro Haltepunkt ein anderer Stil angewendet wird.
- Hinzugefügt: anfängliche leere API-Nachricht und Überlagerung zum Kartenelement.
- Hinzugefügt: Kompatibilität mit Upfront Builder

- Fix: Parallaxe bei PNG-Dateien und transparenten Hintergründen.
- Fix: Bild fügt Verknüpfungsfeld ein.
- Fix: Problem beim Umschalten der Reihenfolge des Admin-Elements.
- Fix: Problem beim Zurücksetzen auf die ursprüngliche Farbe in der Farbauswahl.
- Fix: Galerie-Lightbox-Optionsanwendung im Frontend.
- Fix: Pfeilfüllung mit erweiterten Einstellungen synchronisieren.
- Fix: Kategoriendatenelement von Seiten entfernen.
- Fix: Rendern von Fehlermeldungen bei Widget-Elementen verbessert.
- Fix: Problem beim Einfügen von Schriftarten in der Bearbeitung im reaktionsschnellen Modus.
- Fix: Javascript-Fehler beim Senden des Kontaktformulars.
- Fix: Problem mit dem PHP-Format des Postdatums.
- Fix: Probleme bei der Auflösung von Hintergrundbildern.
- Fix: Verwendung von Schriftarten konsolidieren.
- Fix: Responsives Rendern von Bildvarianten.


1.3.3 - 2016-07-19
-------------------------------------------------------------------------------
- Hinzugefügt: Google Maps API-Schlüsselbereich im Adminbereich
- Fix: Problem mit globalen Regionen.
- Fix: Problem mit den globalen Themeneinstellungen.


1.3.2 - 2016-06-24
-------------------------------------------------------------------------------
- Fix: Ziehen und Ablegen bei reaktionsfähigem Problem.
- Fix: Burger-Menü funktioniert nicht im Desktop-Haltepunkt.


1.3.1 - 2016-06-20
-------------------------------------------------------------------------------
- Fix: Probleme beim Speichern von Layouts archivieren.
- Fix: Post-Element-spezifisches Post-Einstellungsproblem.
- Fix: Umgang mit Edge-Cases für gekennzeichnete Bilddatenelemente.


1.3 - 2016-06-17
-------------------------------------------------------------------------------
- Hinzugefügt: wiederverwendbare benutzerdefinierte Layoutvorlagen.
- Hinzugefügt: Erfahrung beim Bearbeiten neuer Posts/Seiten.
- Hinzugefügt: Dedizierter Einstellungsbereich für Posts und Seiten.

- Fix: Leistungsverbesserungen.
- Fix: Position der Regionsschaltfläche in Responsive anzeigen.
- Fix: Konflikt bei mehreren Lightboxen.
- Fix: Editor-Modus-Navigation im Posts-Element.
- Fix: Auffüllen von Tastatursteuerungen in Inline-Panels.
- Fix: Die Änderung der oberen Polsterung aktualisiert die Elementhöhe.
- Fix: Nachgestellter Schlusskommentar wird aus benutzerdefiniertem CSS entfernt.
- Fix: Problem mit Pro Sites-Upload-Kontingenten.


1.2.2 - 2016-04-29
-------------------------------------------------------------------------------
- Fix: Eigenschaft image element_size pro Haltepunkt speichern.

1.2.1 - 2016-04-28
-------------------------------------------------------------------------------
- Fix: Problem mit der angezeigten Schaltfläche zum Austauschen von Bildern.
- Fix: Gemeinsame Abstraktionsprüfungen im Admin-Bereich.
- Fix: Machen Sie den Posts- und Kommentardialog kontextsensitiv.
- Fix: Dieselbe ID wird mit mehreren Kalender-Widgets verwendet.
- Fix: neue Strings für l10n einfügen.
– Fix: Refactoring von veralteten jQuery-Aufrufen.
- Fix: Responsive Per-Preset-Post-Datenelement-Einrückungen zulassen.

1.2 - 2016-04-14
-------------------------------------------------------------------------------
- Hinzugefügt: Admin-Oberfläche.
- Hinzugefügt: Allgemeine und Debug-Einstellungen in der Admin-Oberfläche.
- Hinzugefügt: Benutzerrolleneinschränkungen in der Admin-Oberfläche.
- Hinzugefügt: experimentelle Leistungsoptimierung in der Admin-Oberfläche.
- Hinzugefügt: Neugestaltung von Bildelementen.
- Hinzugefügt: vorgefertigte CSS-Selektoren für Post-Datenelemente.
- Hinzugefügt: Tastaturkürzel zum Umschalten des Rasters (Alt+G)

- Fix: Textelement mit Bild in Inhaltsoptionen.
- Fix: reaktionsschneller Haltepunkt zum Umschalten der Bildgröße im Editor.
- Fix: Problem mit der Größen-/Höhenänderung von Codeelementen.
- Behoben: Bearbeitungsproblem bei Post-Part-Inputs.
- Fix: Textoptionen für die Beschriftung von Galerieelementen.
- Fix: Problem beim Löschen der reaktionsschnellen unteren Polsterung.
- Fix: Reaktionsleistungsproblem.
- Fix: Bearbeitungsproblem bei der Steuerung der Hintergrundkartenregion.
- Fix: Optimieren Sie die Aktualisierung bei der Neuanordnung von Menüelementen.

1.1.1 - 2016-03-31
-------------------------------------------------------------------------------
- Fix: Doppelte Eingabe bei der Listenbearbeitung sollte das Listenverhalten beenden.
- Fix: Stilkonflikt bei nativen WP-ausgerichteten Bildern.
- Fix: Codeelement mit bereits vorhandener Inhaltsbearbeitungsaktion.
- Fix: Haltepunktprüfung für IE8.
- Fix: Unterstützung für Medienabfragen für IE8.
- Fix: Featured Image Wrapper nimmt Platz mit Hide-Fallback.
- Fix: Element mit benutzerdefinierter Padding-Größenänderungsberechnung.

1.1 - 2016-03-23
-------------------------------------------------------------------------------
- Add: new single post layout editing experience.
- Add: right-to-left compatibility.
- Add: media manager page size.
- Add: new responsive region editing trigger location.
- Add: posts dialog sorting.
- Add: ability to select items across pages in media manager.
- Fix: gallery element linking issue.
- Fix: button element default preset.
- Fix: list creation alignment issue in content editing.
- Fix: slider preset captions issue.
- Fix: disallow spaces in uploaded images.
- Fix: slider initial image size issue.
- Fix: map element and region address refresh issue.
- Fix: content editing link insertion in certain scenarios issue.
- Fix: button element resizing and padding issue.
- Fix: IE11 page/post layout edit link issue.
- Fix: better asset optimization.
- Fix: global background with parallax issue.
- Fix: map element code editor resize issue.

1.0.5 - 2016-02-19
-------------------------------------------------------------------------------
- Fix: remove text element edit content overlay
- Fix: show background padding settings only for regions
- Fix: redactor inline mode edit issue
- Fix: new region controls issue
- Fix: tab label styles not applied in paragraphs
- Fix: prevent lightbox region columns from exceeding breakpoint columns
- Fix: prevent group resizing when entering edit element
- Fix: gallery images black areas on thumbnails resize edge cases
- Fix: gallery initial overlay styles, font styles and default preset value
- Fix: login element z-index issue in editor
- Fix: theme colors being inserted with expanded values
- Fix: preset CSS style cleanup
- Fix: re-render slider elements on Preset settings updated
- Fix: region editing corner trigger not accessible in responsive
- Fix: z-index issue with small-sized groups

1.0.4 - 2016-02-09
-------------------------------------------------------------------------------
- Fix: styling issue

1.0.3 - 2016-02-09
-------------------------------------------------------------------------------
- Drag and drop fixes and performance improvements
- Fix for an issue with pressing Tab key during menu item inline text editing
- Fix issue when using shift-enter double break at the end of an element
- Fix issues with inserts in tab and accordion elements
- Fix preset colors live update in editor
- Fix browser cache upgrade artifacts
- Fix image caption using hardcoded caption color
- Fix image border not properly applied
- Fix gallery caption module shows position options though unchecked
- Fix text element issues with preset creation on migration
- Fix issue with text element contextual menu editing

1.0.2 - 2016-02-04
-------------------------------------------------------------------------------
- Fix: rounded corner module improvements.
- Fix: preset name suggestion will offer an unique preset name.
- Fix: backup link in the upgrade popup recognizes the plugin.

1.0.1 - 2016-01-28
-------------------------------------------------------------------------------
- Fix: compatibility with old PHP versions.
- Fix: color picker appearance near right border.
- Fix: minor style issues.

1.0 - 2016-01-27
-------------------------------------------------------------------------------
- Entirely new way of working with elements size and position:
	+ New drag and drop will make the elements snap-align to other elements on your page.
	+ New concept of "spacer" elements that can be resized and snapped to.
	+ New, more obvious appearance of resizeable elements.
	+ New, more intuitive way in which the element resizing behaves.
- Entirely new element settings:
	+ Whole new design for settings and a brand new use for the sidebar area screen realestate.
	+ Better options organization.
	+ Zero-code advanced appearance editing (colors, borders, corners, typography...), with custom CSS still available for advanced users.
	+ Reusable element configurations:
		* The new presets (available in element settings) allow for having easily available reusable element styles.
		* Presets also store entire element configurations with all their settings included.
		* Live preview of all changes.
- Less error-prone element interaction:
	+ Instead of interacting with elements by hovering over them, you now click to select them. This makes for way less interference with other elements, or other things that can happen on mouse hovering (hover styles/events).
	+ Element settings are now much farther apart from the element removal button, and styled differently.
	+ Quick, no-code access to element padding.
- Improvements:
	+ Added the ability to change typography of text element without custom CSS.
	+ Various fixes in redactor, improved reliability in text editing
	+ Added the ability to create rounded corner for image element without custom CSS.
	+ Improved slider controls.
	+ Added the ability to style tab title.
	+ Improved compatibility with https.
	+ Added Upfront logo in top Upfront button.
	+ No more warning popup when Upfront button is clicked before the system is fully loaded.
- Bug fixes.

- Fixed the bug related to assigning hyperlink to text containing icons.
- Fixed caption bug in image element.
- Fixed caption bug in gallery element.
- Fixed refresh bug in gallery element.
- Fixed resize bug in slider element.
- Fixed resize bug in map element.
- Fixed responsive bug in menu element.
- Fixed initial state of tabs element.
- Fixed styling bug related to tab content.
- Fixed initial state of accordion element.
- Fixed styling bug related to accordion content.
- Fixed some occurrences of wrong z-index value.
- Fixed few typos in editor interface.
- Fixed menu item bug when using Upfront in IE 11.
- Fixed the appearance of featured images.
- Fixed some issues with post meta.
- Fixed display bug when Upfront is used with Spanish language.
- 100+ other minor fixes and improvements.

0.4.1.5 - 2016-01-15
-------------------------------------------------------------------------------
- Fix: PHP 5.2 compatibility issues.

0.4.1.4 - 2016-01-13
-------------------------------------------------------------------------------
- Fix: over-zealous filtering in text element.

0.4.1.3 - 2015-12-11
-------------------------------------------------------------------------------
- Fix: YouTube element protocol issue.
- Fix: WP 4.4 screen class issue with post element.

0.4.1.2 - 2015-11-16
-------------------------------------------------------------------------------
- Fix: like box  height snapping and centralized content.
- Fix: like box iframe going out of bounds width-wise.
- Fix: namespacing the cross-browser animation event.

0.4.1.1 - 2015-11-09
-------------------------------------------------------------------------------
- Fix: image links issues.
- Fix: button in group is opening url instead of edit text.
- Fix: syntax checks in code elements.
- Fix: parallax refresh error when rapidly change background style.
- Fix: parallax affects full width background.
- Fix: module group output z-index.
- Fix: enable region resizing after adding region.
- Add: content type macro to content expansion in posts element.

0.4.1 - 2015-10-26
-------------------------------------------------------------------------------
- Fix: redactor issue with icons in editor vs live.
- Fix: YouTube element issues.
- Fix: paralax mode issues with responsive and image selection.
- Fix: redactor text selection issues.
- Fix: like box trailing slash issue.
- Fix: responsive mode selection clearing and active break point issues.
- Fix: hadcoded gravatar protocol in sidebar.
- Fix: contact form name and l10n.
- Fix: text encoding issues in code and text element sanitization.
- Add: custom cursor for editing areas.
- Add: formatting via inline text expansion.
- Add: choice between theme layout and WP image inserts.
- Add: button element improvements.
- Add: new linking API.

0.4 - 2015-08-28
-------------------------------------------------------------------------------
- Fix: shortcodes in tabs/accordion elements.
- Fix: discussion settings update.
- Fix: responsive menu behavior.
- Fix: pagination issues.
- Fix: menu custom CSS saving in certain scenarios.
- Fix: YouTube element responsive behavior.
- Fix: image caption issues.
- Fix: lightbox creation issues.
- Fix: anchor link issues in menu element.
- Fix: text icons insertion issues.
- Fix: admin bar items issues.
- Add: styled map support for map elements and regions.
- Add: parallax type for image background regions.

0.3.2.1 - 2015-06-02
-------------------------------------------------------------------------------
- Fix: minor style fixes.
- Fix: legacy widget rendering.
- Fix: error in cache spawning.
- Fix: clean up multiplied listeners.

0.3.2 - 2015-05-29
-------------------------------------------------------------------------------
- Fix: images lightbox options.
- Fix: anchor links behavior.
- Fix: element groups and cloning.
- Fix: redactor formatting changes.
- Fix: global regions revert issues.
- Fix: backend content editing page templates issue.
- Fix: menu UI issues.
- Fix: "self" link selection options and rendering.
- Fix: anchors not taking into account sticky header height.
- Fix: text icons rendering.
- Fix: small height regions.
- Fix: prevent live preview when it's not supported.
- Fix: media paths SSL issues in certain setups.
- Fix: widget element changes.
- Fix: code element color picker.
- Fix: redactor and spectrum l10n strings.
- Add: augmented default search markup.
- Add: post date permalink in posts element.
- Add: posts element sticky posts handling options.

0.3.1 - 2015-05-05
-------------------------------------------------------------------------------
- Fix: responsive menu issues.
- Fix: background slider full-screen scroll issue.
- Fix: changing page templates with layouts in storage.
- Fix: changing menu links to pages.
- Fix: categories selection in new post creation.
- Fix: pagination in posts element.
- Fix: removing floating region restricted to header.
- Fix: listing all anchors in the menu.
- Fix: custom posts addition in posts element.
- Fix: gallery caption alignments.
- Fix: background video delayed loop.
- Fix: cloning within elements group.
- Fix: responsive elements positioning/ordering.
- Fix: accordion panel adding.
- Fix: discussion settings popup height.
- Fix: posts element taxonomy selection.
- Add: theme testing plugins widgets support.
- Add: posts element "Read more" tag support.
- Add: login element registration link support.
- Add: forms overrides support.

0.3 - 2015-04-02
-------------------------------------------------------------------------------
- Fix: menu rendering improvements.
- Fix: link behavior in grouped elements.
- Fix: slider element and region behavior.
- Fix: image inserts.
- Fix: first section element hide in responsive.
- Fix: linking panels update.
- Fix: accordion panel adding.
- Fix: gallery elements warnings and plugin conflicts.
- Fix: discussion settings update.
- Fix: adding playable video element
- Fix: keyframe animations and media queries allowed in global CSS.
- Add: multiple global regions.

0.2.7.1 - 2015-03-17
-------------------------------------------------------------------------------
- Fix: resizing handle hidden when editing elements in group.
- Fix: hide settings button and resizable handle on group when editing elements.

0.2.7 - 2015-03-17
-------------------------------------------------------------------------------
- Fix: custom 404 layout changes saving.
- Fix: image embed in text/accordion editing.
- Fix: remove gallery image rotate functionality.
- Fix: clean up the passed popup classname parameter on close.
- Fix: image warning popup styles.
- Fix: skip prefixing the global CSS.
- Fix: validate the selected image size argument for code element.
- Fix: posts/pages popup bugging out if no author specified.
- Fix: drag and drop issue on the last element

0.2.6 - 2015-03-10
-------------------------------------------------------------------------------
- Fix: Fix image blocks UI when S3 plugins move images.
- Fix: youtube element accept short ahare url format i.e. youtu.be
- Fix: keep ratio behavior for full screen region
- Fix: muted video background
- Fix: theme colors in code element
- Fix: post layout wont apply to all post types
- Fix: accordion panel add button not showing
- Fix: multiple spectrums open
- Fix: disable alpha slider when theme color is chosen

0.2.5 - 2015-03-05
-------------------------------------------------------------------------------
- Fix: gallery labels adding.
- Fix: compensate for dead element double-click event.
- Fix: like box fixed misalignment of thumbnails.
- Fix: like box mapped return key to send action.
- Fix: too many controlls after element group and chosen nag for sprites.

0.2.4 - 2015-03-04
-------------------------------------------------------------------------------
- Fix: YouTube and tabs elements.
- Fix: occasional spectrum-related nag.
- Fix: background image lazy loading not loaded in floating region.
- Fix: post status model inheritance.
- Added: close button for responsive menu navigation.
- Added: map markers toggling for map element and region.
- Added: autoplay option for video background.

0.2.3 - 2015-02-24
-------------------------------------------------------------------------------
- Fix: ensure slider dots are inside element.
- Fix: default styling for contact form.
- Fix: layout saves in new post writing experience and module selector.
- Fix: anonymous mode AJAX request layout resolution update.
- Fix: default date formats for posts element.

0.2.2 - 2015-02-23
-------------------------------------------------------------------------------
- Fix: responsive menu positioning.
- Fix: desktop breakpoint responsive typography.
- Fix: slider element slide removal.
- Fix: button element bugs.

0.2.1 - 2015-02-20
-------------------------------------------------------------------------------
- Fix: add typography defaults.
- Fix: background slider image removal.
- Fix: responsive typography.

0.2.0 - 2015-02-19
-------------------------------------------------------------------------------
- Fix: menu handling in responsive.
- Fix: link panels custom URL entry.
- Fix: handling empty gallery description.
- Fix: contact element behavior.
- Fix: redactor breaks and wpautop conflict in text editing.
- Fix: immediate image inserts publishing.
- Fix: default gravatar handling.
- Fix: media (de)selection for single items.
- Added: editable theme colors support.

0.1.3 - 2015-02-09
-------------------------------------------------------------------------------
- Fix: z-index overlap in case of multiple menus.
- Fix: navigation resizing bugs.
- Fix: input fields focus.
- Fix: drag and drop when there's only one element inside region.

0.1.2 - 2015-02-02
-------------------------------------------------------------------------------
- Fix: text copy/paste issues.
- Fix: contact form displaying (false) on mail sent
- Fix: use absolute URLs for theme images.
- Fix: editor copy/paste and color assignment issues.
- Added: posts with no featured image get special class in posts element.

0.1.1 - 2015-01-26
-------------------------------------------------------------------------------
- Second public Beta release.

0.1.0 - 2015-01-21
-------------------------------------------------------------------------------
- Initial public Beta release.
