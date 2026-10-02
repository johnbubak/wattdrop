// WattDrop — Express drop-in (Node.js)

const path = require('path');
const fs = require('fs');

const MINI_SVG = `<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><rect width="100" height="100" rx="22" fill="#10b981"/><text x="50%" y="55%" font-size="46" text-anchor="middle" fill="#ffffff" font-family="system-ui, monospace" font-weight="bold">WD</text></svg>`;

// Catches all common icon paths in one route
app.get(['/favicon.ico', '/favicon-16x16.png', '/favicon-32x32.png',
         '/apple-touch-icon.png', '/apple-touch-icon-precomposed.png'], (req, res) => {
  // Check if a real file exists first (optional)
  const realFile = path.join(process.cwd(), 'icon.svg');
  if (fs.existsSync(realFile)) {
    return res.type('image/svg+xml').sendFile(realFile);
  }
  // In-memory SVG — or use Eco Mode: res.status(204).end();
  res.type('image/svg+xml').send(MINI_SVG);
});