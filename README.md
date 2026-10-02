# 🔋 WattDrop

**More Code. Less Energy. Zero Useless Consumption.**

Every missing `favicon.ico` is a silent energy vampire. Your visitors' browsers auto-request it on every page load ([the HTML spec says so](https://html.spec.whatwg.org/multipage/links.html#rel-icon)). If it isn't there, your server still wakes up, runs your framework, generates a 404 page, and writes a log line — for **zero visual benefit**.

**WattDrop** is a ~1.2 KB drop-in that kills this consumption at the source. It intercepts the missing icon request directly in the browser, generates a lettered, gradient SVG in memory, and the HTTP request **never hits your server**.

- 🛑 **Kills Consumption:** The 404 request never happens. No server wake-up, no log spam, no disk writes.
- 🎨 **Generative UX:** Every site gets a unique letter icon from its own identity — `github.com` gets a gradient **GH**, a page titled `Dashboard` gets **DA**.
- ⚡ **Zero Config:** One line in your `<head>`. No dependencies, no build step, no icons to design.

## 🚀 The Drop-In

```html
<script src="https://cdn.jsdelivr.net/gh/johnbubak/wattdrop@main/wattdrop.min.js" async></script>
```

That's it. Browsers get a clean letter icon in the tab, and the `/favicon.ico` request is killed **before** it leaves the browser. Your `access.log` stays clean. Open DevTools (F12) — WattDrop leaves a colorful signature so you know it's working.

## 🔤 The letter always fits

The icon renders 1–2 letters, taken from (in order):

1. **The domain** — `my-api.com` → **MY**, `localhost` → **LO**
2. **The page `<title>`** — `Dashboard` → **DA**
3. A fixed **ZI** fallback

The font size adapts (2 letters → 46, 1 letter → 64) and the text is vertically centered with `dominant-baseline`, so the glyph is always perfectly framed — for any letter, any length, any domain.

## 🌍 The honest numbers

We promised "more code, less energy" — so here is the real calculation, with sources, not vibes.

**What a wasted favicon request actually costs:** the browser fires it on every visit to a site without an icon, gets a 404, and the server pays for a framework boot, a 404 page, and a log write. Measured per-request energy:

| Stack | Energy per request | Source |
|---|---|---|
| Static file server (nginx serving a 404) | ~0.01 mWh | packet + log line, near-zero compute |
| Lightweight API (Express / FastAPI boot) | ~0.02–0.1 mWh | Hoffmann & Majuntke, per-request energy models (0.006–4 mWh depending on endpoint) |
| WordPress / dynamic CMS (full boot + DB) | ~0.5 mWh | UCLouvain thesis: dynamic WP ≈ 27 W at 16 req/s; static/cached ≥ 60× less |

**Your site, your numbers:**

```
E = visits/day × 1 request per visit × mWh per request
```

| Example | Math | Saved |
|---|---|---|
| WordPress blog, 1,000 visits/day | 1,000 × 0.5 mWh | ≈ 0.5 Wh/day — **0.18 kWh/year**, plus ~365,000 log lines that never get written |
| Express API, 500 visits/day | 500 × 0.02 mWh | ≈ 0.01 Wh/day — small, but 100 % avoidable for one line of code |

**The global picture (order of magnitude, conservative):** roughly a billion websites exist; surveys find [~75 % of domains deliver no favicon file](https://tech.arantius.com/favicon-survey) (top-site 404 rates are ~16 %, [HTTP Archive](https://speakofthedevrel.cloud/2019/05/07/favicons-perhaps-the-least-understood-web-feature/)). Assuming only 100 million active sites without an icon, ~100 visits/day each:

- **~10 billion avoidable requests every single day**
- At 0.01–0.5 mWh each → **0.1–5 MWh/day** → **~40–1,800 MWh/year**
- Same order as the annual electricity of a few hundred households — spent on requests that produce *nothing*, every day, forever.

**Honesty clause:** these are order-of-magnitude estimates, not measurements — your stack, traffic and logging change the absolute numbers. What is not in question: the request is **100 % avoidable**, it happens **billions of times a day**, and the fix is **one line**. Measure your own: `grep 'favicon' access.log | wc -l`.

## 🔧 How it works

```javascript
(function () {
  // 1. If the site already has a favicon, do absolutely nothing.
  if (document.querySelector('link[rel*="icon"]')) return;

  // 2. Letter source: domain → <title> → fallback (always 1–2 chars)
  var d = (location.hostname.replace('www.', '').split('.')[0].replace(/[^a-zA-Z0-9]/g, '') ||
           (document.title || '').replace(/[^a-zA-Z]/g, '')) .toUpperCase();
  var initials = d.substring(0, 2) || 'ZI';
  var size = initials.length > 1 ? 46 : 64; // adaptive — always fits

  // 3. Generate the in-memory SVG (green→blue gradient)
  var svg =
    '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100">' +
    '<defs><linearGradient id="wd-grad" x1="0%" y1="0%" x2="100%" y2="100%">' +
    '<stop offset="0%" stop-color="#10b981"/><stop offset="100%" stop-color="#0ea5e9"/>' +
    '</linearGradient></defs>' +
    '<rect width="100" height="100" rx="22" fill="url(#wd-grad)"/>' +
    '<text x="50%" y="50%" dominant-baseline="central" font-size="' + size + '" ' +
    'text-anchor="middle" fill="#ffffff" font-family="system-ui, monospace" font-weight="bold">' +
    initials + '</text></svg>';

  // 4. Inject as a data URI — the server request never happens
  var link = document.createElement('link');
  link.rel = 'icon';
  link.href = 'data:image/svg+xml,' + encodeURIComponent(svg.replace(/\s+/g, ' '));
  document.head.appendChild(link);
})();
```

## 🧩 The Ecosystem — 3 Pillars

| Pillar | Where it lives | What it does |
|---|---|---|
| **JS Drop** | `wattdrop.min.js` (this repo, served via jsDelivr CDN) | Client-side favicon generation — kills the request before it happens |
| **Plugins** | WordPress & other CMS | Admin-friendly UX: pick an icon via the media library, or auto-generate an "Eco Logo" from your site initials |
| **Server-Side** | `server-configs/` (nginx, Apache, Cloudflare) | Edge & webserver fallback — answers icon/image 404s with a silent `204` so the app never wakes up |

### Advanced Server-Side Integrations

If you control the server (or want to catch bots that don't run JavaScript):

- `server-configs/nginx.conf` — icon + missing-image fallback, aggressive JS/CSS caching, gzip.
- `server-configs/.htaccess` — the Apache equivalent (204 for icons, image fallback, expires, gzip).
- `server-configs/cloudflare-worker.js` — kill icon requests at the Cloudflare edge; your origin stays at 0 W.
- `snippets/` — drop-in snippets for **FastAPI, Flask, Express (Node), Go, PHP, WordPress**.

### WordPress Plugin (Pillar 2)

WordPress is the biggest win: a missing icon boots the entire CMS — core, MySQL connection, theme — just to generate a 404 (~0.5 mWh per request). The `plugins/wordpress/` plugin hooks at `plugins_loaded` (priority 0), so the request never reaches the DB/theme layer:

- Pick your icon via the native **Media Library** (crop/convert in WordPress' own image editor — no custom canvas).
- No icon chosen? A generative **Eco Logo** from your site initials is served from memory.
- Real icon files on disk always win.

## 📦 Structure

```
wattdrop/
├── wattdrop.js              # Source (readable, commented)
├── wattdrop.min.js          # Minified drop-in (≈1.2 KB, what you load via CDN)
├── server-configs/
│   ├── nginx.conf           # Zero-Icon + eco-performance nginx drop-in
│   ├── .htaccess            # Apache equivalent (204, image fallback, gzip)
│   ├── cloudflare-worker.js # Edge: icon requests die at the Cloudflare node
│   └── wattdrop-missing.svg # 1×1 placeholder for the Apache image fallback
├── plugins/
│   └── wordpress/           # Pillar-2 plugin (intercept + Media Library + Eco Logo)
├── snippets/                # Copy-paste snippets for every major framework
├── .github/FUNDING.yml      # Donation buttons (Sponsor tab)
└── docs/origin.md           # How WattDrop came to be
```

## 🤝 Contribute

Have a cleaner snippet for **Rust/Axum? Ruby on Rails? Django?** Submit a Pull Request — we want a library of copy-paste snippets for every framework on earth.

If this saved your `access.log`, please leave a ⭐ and share it!

## ☕ Support this project

Built something with WattDrop? Coffee appreciated ☕

| Method | Link |
|---|---|
| ⭐ GitHub Sponsors | [github.com/sponsors/johnbubak](https://github.com/sponsors/johnbubak) |
| ☕ Ko-fi | [ko-fi.com/johnbubak](https://ko-fi.com/johnbubak) |
| 💳 PayPal | [paypal.me/janBobka](https://paypal.me/janBobka) |
| 🟡 Binance Pay | [JohnBubak](https://app.binance.com/qr/dplkj8) |

[![License: MIT](https://img.shields.io/badge/License-MIT-green.svg)](https://opensource.org/licenses/MIT)
[![PRs Welcome](https://img.shields.io/badge/PRs-welcome-brightgreen.svg)](#)

by John Bubak