// WattDrop — Go drop-in (net/http standard library)

package main

import "net/http"

const miniSvg = `<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><rect width="100" height="100" rx="22" fill="#10b981"/><text x="50%" y="55%" font-size="46" text-anchor="middle" fill="#ffffff" font-family="system-ui, monospace" font-weight="bold">WD</text></svg>`

func faviconHandler(w http.ResponseWriter, r *http.Request) {
	w.Header().Set("Content-Type", "image/svg+xml")
	// In-memory SVG — or use Eco Mode: w.WriteHeader(http.StatusNoContent)
	w.Write([]byte(miniSvg))
}

func main() {
	// Register the routes in the standard mux
	http.HandleFunc("/favicon.ico", faviconHandler)
	http.HandleFunc("/favicon-16x16.png", faviconHandler)
	http.HandleFunc("/favicon-32x32.png", faviconHandler)
	http.HandleFunc("/apple-touch-icon.png", faviconHandler)
	http.HandleFunc("/apple-touch-icon-precomposed.png", faviconHandler)
	// ... rest of your server ...
}