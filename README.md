# 🔋 WattDrop

**More Code. Less Energy. Zero Useless Consumption.**

Every missing `favicon.ico` is a silent energy vampire. It triggers a network request, wakes up your backend, generates a 404, spins up your hard drive to write the error log — and all of it for **zero visual benefit**.

**WattDrop** is a ~1 KB drop-in (under 300 bytes of logic, the rest is the SVG template) that kills this consumption at the source. It intercepts the missing icon request directly in the browser, generates a beautiful initials-based SVG in memory, and the HTTP request **never hits your server**.

- 🛑 **Kills Consumption:** The 404 request never happens. No server wake-up, no log spam, no SSD wear.
- 🎨 **Generative UX:** Automatically creates a sleek initials logo from your domain (e.g. `github.com` → a gradient **GH** icon).
- ⚡ **Zero Config:** Drop it in and forget it. One line, no dependencies, no build step.

## 🚀 The Drop-In

Paste this into your `<head>` and instantly drop your server consumption:

```html
<script src="https://cdn.jsdelivr.net/gh/johnbubak/wattdrop@main/wattdrop.min.js" async></script>
```

That's it.

- Browsers get a clean, gradient SVG icon in the tab.
- Missing icon requests are killed **before** they hit your server.
- Your `access.log` stays clean.
- Open DevTools (F12) — WattDrop leaves a colorful signature so you know it's working.

## 🌍 Why it matters (The "Napkin Math")

Every favicon request that misses wakes up your web framework, generates a 404 HTML page (1–5 KB), and writes to disk. With ~100,000 small APIs adopting this patch, saving ~500 unnecessary 404 log-writes per day:

- **50 Million** useless disk I/O operations prevented daily.
- **182 Billion** requests optimized over 10 years.
- **~18 Megawatt-hours (MWh)** of electricity saved globally — roughly the power draw of a two-person household for 6–7 years.

Fixing your logs is good. Saving the planet while doing it is better.

## 🔧 How it works

```javascript
/*! WattDrop | MIT License | Stop Useless Consumption */
(function(){
  // 1. If the site already has a favicon, do absolutely nothing.
  if(document.querySelector('link[rel*="icon"]')) return;

  // 2. The "Klicky Bunti" Console Easter Egg for Devs
  console.log(
    "%c🔋 WattDrop %c Consumption Dropped ",
    "color:#fff; font-weight:bold; background:#10b981; padding:3px 6px; border-radius:4px 0 0 4px;",
    "color:#fff; background:#374151; padding:3px 6px; border-radius:0 4px 4px 0;"
  );

  // 3. Generative initials from the domain ("my-api.com" -> "MY")
  let host = location.hostname.replace('www.', '').split('.')[0].replace(/[^a-zA-Z0-9]/g, '');
  let initials = (host.substring(0, 2) || "ZI").toUpperCase();

  // 4. Generate the in-memory SVG with a sleek CSS gradient
  const svg = `<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100">
<defs><linearGradient id="wd-grad" x1="0%" y1="0%" x2="100%" y2="100%">
<stop offset="0%" stop-color="#10b981"/><stop offset="100%" stop-color="#0ea5e9"/>
</linearGradient></defs>
<rect width="100" height="100" rx="22" fill="url(#wd-grad)"/>
<text x="50%" y="55%" font-size="46" text-anchor="middle" fill="#ffffff"
font-family="system-ui, monospace" font-weight="bold">${initials}</text>
</svg>`;

  // 5. Inject the SVG as a data URI — the server request never happens
  const link = document.createElement('link');
  link.rel = 'icon';
  link.href = 'data:image/svg+xml,' + encodeURIComponent(svg.replace(/\s+/g, ' '));
  document.head.appendChild(link);
})();
```

### Why this version is special

1. **Deeply personalized** — it reads `location.hostname`, so every developer who pastes it gets a custom icon for their specific project immediately. Instant "wow".
2. **Honors the paradox** — we added more code (initials logic + gradient) so the browser is so satisfied with the result that the backend consumes less energy.
3. **The Easter Egg** — the console log validates to the developer that they're doing something good for their infrastructure.

## 🧩 The Ecosystem — 3 Pillars

| Pillar | Where it lives | What it does |
|---|---|---|
| **JS Drop** | `wattdrop.min.js` (this repo, served via jsDelivr CDN) | Client-side favicon generation — kills the request before it happens |
| **Plugins** | WordPress & other CMS | Admin-friendly UX: pick an icon via the media library, or auto-generate an "Eco Logo" from your site initials |
| **Server-Side** | `server-configs/` (nginx, Apache) | Edge & webserver fallback — answers icon/image 404s with a silent `204` so the app never wakes up |

### Advanced Server-Side Integrations

If you control the server (or want to catch the bots that don't run JavaScript), see:

- `server-configs/nginx.conf` — icon + missing-image fallback, aggressive JS/CSS caching, gzip.
- `server-configs/.htaccess` — the Apache equivalent (204 for icons, image fallback, expires, gzip).
- `server-configs/cloudflare-worker.js` — kill icon requests at the Cloudflare edge; your origin stays at 0 W.
- `snippets/` — drop-in snippets for **FastAPI, Flask, Express (Node), Go, PHP, WordPress**.

### WordPress Plugin (Pillar 2)

WordPress is the biggest win of all: a missing icon currently boots the entire CMS — core, MySQL connection, theme — just to generate a 404. The `plugins/wordpress/` plugin hooks at `plugins_loaded` (priority 0), so the request never reaches the DB/theme layer:

- Pick your icon via the native **Media Library** (crop/convert in WordPress' own image editor — no custom canvas).
- No icon chosen? A generative **Eco Logo** from your site initials (green→blue gradient, same as the JS drop-in) is served from memory.
- Real icon files on disk always win.

## 📦 Structure

```
wattdrop/
├── wattdrop.js          # Source (readable, commented)
├── wattdrop.min.js      # Minified drop-in (what you load via CDN)
├── server-configs/
│   ├── nginx.conf       # Zero-Icon + eco-performance nginx drop-in
│   ├── .htaccess        # Apache equivalent (204, image fallback, gzip)
│   ├── cloudflare-worker.js  # Edge: icon requests die at the Cloudflare node
│   └── wattdrop-missing.svg  # 1×1 placeholder for the Apache image fallback
├── plugins/
│   └── wordpress/       # Pillar-2 plugin (intercept + Media Library + Eco Logo)
└── snippets/            # Copy-paste snippets for every major framework
```

## 🤝 Contribute

Have a cleaner snippet for **Rust/Axum? Ruby on Rails? Django?** Submit a Pull Request — we want a library of copy-paste snippets for every framework on earth.

If this saved your `access.log`, please leave a ⭐ and share it!

[![License: MIT](https://img.shields.io/badge/License-MIT-green.svg)](https://opensource.org/licenses/MIT)
[![PRs Welcome](https://img.shields.io/badge/PRs-welcome-brightgreen.svg)](#)

by John Bubak