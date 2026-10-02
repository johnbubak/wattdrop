// ==============================================================================
// WATTDROP — ECO-PERFORMANCE EDGE DROP-IN (Cloudflare Worker)
// ==============================================================================
// The Endgame: the request never even reaches your server. Cloudflare answers
// missing icon requests directly at the edge (physically ~10 km from the user)
// with 204 No Content. Your origin consumes exactly 0.0 Watt for these.
//
// Deploy: Cloudflare Dashboard -> Workers -> Create Worker -> paste -> Save
// Then route your domain (or a catch-all route) to this Worker.
// ==============================================================================

export default {
  async fetch(request) {
    const url = new URL(request.url);

    // All standard icon paths browsers and bots request blindly.
    // Order matters: exact matches first, then the wildcard pattern.
    const icons = [
      '/favicon.ico',
      '/favicon-16x16.png',
      '/favicon-32x32.png',
      '/apple-touch-icon.png',
      '/apple-touch-icon-precomposed.png',
    ];

    if (icons.includes(url.pathname) || /^\/apple-touch-icon.*\.png$/.test(url.pathname)) {
      // Kill the request at the edge: 204 No Content, no log spam, no origin wake-up.
      return new Response(null, { status: 204 });
    }

    // Everything else: pass through to your origin untouched.
    return fetch(request);
  },
};