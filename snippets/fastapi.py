# WattDrop — FastAPI drop-in (Python)
# Serves the physical icon.svg if present, otherwise an in-memory SVG.
# Prevents 404 log spam and keeps the app from waking up for icon requests.

from pathlib import Path
from fastapi import APIRouter, Response
from fastapi.responses import FileResponse

wattdrop_router = APIRouter()

# Tiny default SVG (green→blue gradient, "WD" initials)
DEFAULT_SVG = """<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><rect width="100" height="100" rx="22" fill="#10b981"/><text x="50%" y="55%" font-size="46" text-anchor="middle" fill="#ffffff" font-family="system-ui, monospace" font-weight="bold">WD</text></svg>"""


@wattdrop_router.get("/favicon.ico", include_in_schema=False)
@wattdrop_router.get("/favicon-16x16.png", include_in_schema=False)
@wattdrop_router.get("/favicon-32x32.png", include_in_schema=False)
@wattdrop_router.get("/apple-touch-icon.png", include_in_schema=False)
@wattdrop_router.get("/apple-touch-icon-precomposed.png", include_in_schema=False)
async def eco_icon_handler():
    # 1. Prioritize real files if they exist
    if Path("favicon.ico").exists():
        return FileResponse("favicon.ico")
    if Path("icon.svg").exists():
        return FileResponse("icon.svg", media_type="image/svg+xml")
    # 2. In-memory SVG mode (or Response(status_code=204) for maximum eco)
    return Response(content=DEFAULT_SVG, media_type="image/svg+xml")


# Usage in your app:
#   from wattdrop import wattdrop_router
#   app.include_router(wattdrop_router)