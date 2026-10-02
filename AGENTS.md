# WattDrop — Agent Guide

**More Code. Less Energy. Zero Useless Consumption.**

Green-IT-Open-Source-Projekt (MIT, John Bubak): Ein ~1.2-KB-Drop-in, das sinnlose
404-Favicon-Requests (`/favicon.ico`, `apple-touch-icon*.png`) im Browser abfängt,
bevor sie den Server erreichen. Generatives SVG-Favicon aus Domain-/Title-Initialen
(adaptive Schriftgröße) als Data-URI — der HTTP-Request erreicht den Server nie.

## Dateien

| Datei | Zweck |
|---|---|
| `wattdrop.js` | Quelle (lesbar, kommentiert): Initialen (Domain → Title → Fallback) → Gradient-SVG als Data-URI, adaptive Schriftgröße + `dominant-baseline`-Zentrierung |
| `wattdrop.min.js` | Minifiziertes Drop-in (1183 Bytes), via CDN geladen |
| `README.md` | Public-Pitch (Englisch), realistische Einsparungskalkulation mit Quellen, Donations, 3-Säulen-Vision |
| `LICENSE` | MIT |
| `.github/FUNDING.yml` | Donation-Buttons (GitHub Sponsors, Ko-fi, PayPal, Binance Pay) — Sponsor-Tab |
| `.gitignore` | Ignoriert `data/` (lokale Artefakte) — nie committen |
| `server-configs/nginx.conf` | nginx: 204 für fehlende Icons, Image-Fallback, expires 1y, gzip |
| `server-configs/.htaccess` | Apache-Pendant (mod_rewrite, `!-f`-Check, Image-Fallback, gzip) |
| `server-configs/cloudflare-worker.js` | Edge: 204 für Icon-Pfade (Origin bleibt bei 0 W) |
| `server-configs/wattdrop-missing.svg` | 1×1-Platzhalter für Image-Fallback (Apache) |
| `plugins/wordpress/` | Säule-2-Plugin: `wattdrop.php` (Intercept, Media-Library, Eco-Logo) + `wattdrop-admin.js` |
| `snippets/` | Copy-paste-Snippets: fastapi, flask, express, go, php, wordpress |
| `docs/origin.md` | Entstehungsgeschichte (Auszug aus dem Gemini-Protokoll im `ai-proxy`-Ordner) — historisch, Entscheidungen stehen in README/AGENTS |

## Verwendung (CDN)

```html
<script src="https://cdn.jsdelivr.net/gh/johnbubak/wattdrop@main/wattdrop.min.js" async></script>
```

Sobald das Repo auf GitHub ist, liefert jsDelivr die Datei automatisch aus.
**Referenz-Deployment:** `aip.bobka.net` (ai-proxy) serviert `icon.svg` live für
8 Icon-Pfade inkl. `favicon-192/512` — Server-Side-Muster aus `snippets/fastapi.py`.

## Architektur & Constraints

- **Kern-Logik:** `if(document.querySelector('link[rel*="icon"]')) return;` +
  Data-URI-Injektion sind das Herzstück — der Request darf nie den Server erreichen.
- **Zero-Config / Zero-Dependency:** Keine externen Libs, kein Build-Step, kein
  Bild-Bloat — SVG wird als String aus dem RAM erzeugt (Initialen aus
  `location.hostname` oder `document.title`, Grün→Blau `#10b981`→`#0ea5e9`,
  Fallback "ZI", Schriftgröße 46/64 je nach Länge).
- **CSS/JS-Bündelung NICHT im Browser lösen:** HTTP/2-Multiplexing, gzip, hartes
  Caching gehören in `server-configs/`, nicht in JS.
- **Fehlende Bilder:** JS repariert nur das Layout — echten Strom spart erst die
  Server-Ebene (Säule 3). Kein falsches Versprechen in der Doku.
- **Ehrliche README:** Angaben (Dateigrößen, Spar-Rechnung) immer korrekt halten.

## Die 3-Säulen-Vision (alle umgesetzt)

| Säule | Beschreibung |
|---|---|
| **JS Drop** | `wattdrop.js`/`.min.js` — client-seitig, Request wird verhindert |
| **Plugins** | WordPress (`plugins/wordpress/`) — Icon via Media Library, natives Crop-Tool, Eco-Logo-Fallback; andere CMS offen |
| **Server-Side** | nginx + Apache `.htaccess` + Cloudflare Worker; Snippets für fastapi/flask/express/go/php/wordpress |

## Hinweise für Agenten

- **Sprache:** Public-facing Dateien (README, Snippets, Doku) auf Englisch.
- **Kein Commit ohne Auftrag:** Repo ist veröffentlicht auf GitHub
  (`github.com/johnbubak/wattdrop`, Branch `main`). Commits nur mit explizitem
  Auftrag; jsDelivr serviert den `main`-Branch automatisch als CDN.
- **Kein Build/Tests/CI:** Verifikation = Syntax-Checks (`node --check` für JS).
  `php` ist lokal nicht installiert — PHP nur statisch/manuell prüfen.
- **`wattdrop.min.js` manuell pflegen:** Es gibt keinen Minify-Build — Änderungen
  an `wattdrop.js` müssen per Hand in `wattdrop.min.js` gespiegelt werden.
- **`data/` ignorieren:** `data/tokens.db` ist ein lokales, untracked Artefakt —
  nicht Teil des Repos, nie committen.
- **Snippets konsistent halten:** Gleiche Icon-Pfade (`/favicon.ico`,
  `favicon-16x16/32x32/192x192/512x512.png`, `apple-touch-icon*.png`, `icon.svg`),
  reale Datei vor SVG-Fallback prüfen, sonst 204/SVG aus dem Speicher.
- **Server-Configs — reale Datei gewinnt immer:** `RedirectMatch 204` (Apache)
  und `return 204` (nginx) schatten echte Icons. Verifizierte Muster: Apache
  `RewriteCond %{REQUEST_FILENAME} !-f` + `[R=204,L]`; nginx
  `try_files $uri @missing_icon;` + Named-Location `return 204`.
- **WordPress-Plugin:** Intercept auf `plugins_loaded` (Priorität 0) — so früh wie
  möglich, DB/Theme-Layer darf nicht booten. Reale Datei in `ABSPATH` gewinnt
  immer. Kein eigener Canvas-Cropper — natives WP-Image-Editor nutzen.