# WattDrop — Flask drop-in (Python)

from pathlib import Path
from flask import Flask, Response
from flask import send_file

app = Flask(__name__)

MINI_SVG = """<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><rect width="100" height="100" rx="22" fill="#10b981"/><text x="50%" y="55%" font-size="46" text-anchor="middle" fill="#ffffff" font-family="system-ui, monospace" font-weight="bold">WD</text></svg>"""


@app.route('/favicon.ico')
@app.route('/favicon-16x16.png')
@app.route('/favicon-32x32.png')
@app.route('/apple-touch-icon.png')
@app.route('/apple-touch-icon-precomposed.png')
def eco_icon():
    """Catches icon requests and prevents 404 log spam."""
    if Path("favicon.ico").exists():
        return send_file("favicon.ico", mimetype="image/x-icon")
    if Path("icon.svg").exists():
        return send_file("icon.svg", mimetype="image/svg+xml")
    return Response(MINI_SVG, mimetype='image/svg+xml')