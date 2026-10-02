/*!
 * WattDrop | MIT License | Stop Useless Consumption
 * More Code. Less Energy. Zero Useless Consumption.
 * by John Bubak
 *
 * Drop this into your <head> and instantly kill the favicon 404 request:
 *   <script src="https://cdn.jsdelivr.net/gh/johnbubak/wattdrop@main/wattdrop.min.js" async></script>
 */
(function () {
  // 1. If the site already has a favicon, do absolutely nothing.
  if (document.querySelector('link[rel*="icon"]')) return;

  // 2. The "Klicky Bunti" Console Easter Egg for Devs
  console.log(
    "%c🔋 WattDrop %c Consumption Dropped ",
    "color:#fff; font-weight:bold; background:#10b981; padding:3px 6px; border-radius:4px 0 0 4px;",
    "color:#fff; background:#374151; padding:3px 6px; border-radius:0 4px 4px 0;"
  );

  // 3. A letter (or two) that always fits — from the domain, falling back
  //    to the page <title>, then to a fixed fallback (zero-config).
  //    e.g. "my-api.com" -> "MY", "localhost" -> "LO", title "Dashboard" -> "DA"
  var d = (location.hostname.replace('www.', '').split('.')[0].replace(/[^a-zA-Z0-9]/g, '') ||
           (document.title || '').replace(/[^a-zA-Z]/g, '')) .toUpperCase();
  var initials = d.substring(0, 2) || 'ZI';
  var size = initials.length > 1 ? 46 : 64; // adaptive, so the glyph always fits

  // 4. Generate the in-memory SVG with a sleek green→blue gradient
  var svg =
    '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100">' +
    '<defs><linearGradient id="wd-grad" x1="0%" y1="0%" x2="100%" y2="100%">' +
    '<stop offset="0%" stop-color="#10b981"/>' +
    '<stop offset="100%" stop-color="#0ea5e9"/>' +
    '</linearGradient></defs>' +
    '<rect width="100" height="100" rx="22" fill="url(#wd-grad)"/>' +
    '<text x="50%" y="50%" dominant-baseline="central" font-size="' + size + '" text-anchor="middle" fill="#ffffff" ' +
    'font-family="system-ui, monospace" font-weight="bold">' + initials + '</text>' +
    '</svg>';

  // 5. Inject the SVG as a data URI — the server request never happens
  var link = document.createElement('link');
  link.rel = 'icon';
  link.href = 'data:image/svg+xml,' + encodeURIComponent(svg.replace(/\s+/g, ' '));
  document.head.appendChild(link);
})();