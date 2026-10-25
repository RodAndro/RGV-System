"""
Generator script for RGV Multi-Tech Services - Whole Features Specification PDF.
Converts rgv_features_doc.html to a publication-grade PDF using headless Microsoft Edge.
"""

import os
import sys
import base64
import subprocess
import pypdf

BASE_DIR = os.path.dirname(os.path.abspath(__file__))
LOGO_PATH = os.path.join(BASE_DIR, "logo", "RGV-Logo.png")
INPUT_HTML = os.path.join(BASE_DIR, "rgv_features_doc.html")
TEMP_RENDER_HTML = os.path.join(BASE_DIR, "rgv_features_render.html")
OUTPUT_PDF = os.path.join(BASE_DIR, "RGV-System-Features-Specification.pdf")
EDGE_PATH = r"C:\Program Files (x86)\Microsoft\Edge\Application\msedge.exe"

if not os.path.exists(EDGE_PATH):
    EDGE_PATH = r"C:\Program Files\Microsoft\Edge\Application\msedge.exe"
if not os.path.exists(EDGE_PATH):
    EDGE_PATH = r"C:\Program Files\Google\Chrome\Application\chrome.exe"

# 1. Read Base HTML
with open(INPUT_HTML, "r", encoding="utf-8") as f:
    html_content = f.read()

# 2. Inject Base64 Logo
logo_tag = ""
if os.path.exists(LOGO_PATH):
    with open(LOGO_PATH, "rb") as f:
        b64_data = base64.b64encode(f.read()).decode("utf-8")
        logo_tag = f'<img src="data:image/png;base64,{b64_data}" class="cover-logo" alt="RGV Logo">'

html_content = html_content.replace("<!-- LOGO_IMAGE -->", logo_tag)

with open(TEMP_RENDER_HTML, "w", encoding="utf-8") as f:
    f.write(html_content)

print(f"Prepared render HTML ({len(html_content)} bytes)")

# 3. Compile via Edge Headless
header_html = (
    "<div style='font-size:7.5pt; width:100%; display:flex; justify-content:space-between; "
    "padding:0 15mm; color:#64748b; font-family:-apple-system,BlinkMacSystemFont,Segoe UI,Roboto,sans-serif;'>"
    "<span>RGV Multi-Tech Services &bull; Whole Features Specification</span>"
    "<span>Version 1.6</span></div>"
)

footer_html = (
    "<div style='font-size:7.5pt; width:100%; display:flex; justify-content:space-between; "
    "padding:0 15mm; color:#64748b; font-family:-apple-system,BlinkMacSystemFont,Segoe UI,Roboto,sans-serif;'>"
    "<span>Comprehensive Business Operations Manual</span>"
    "<span>Page <span class='pageNumber'></span> of <span class='totalPages'></span></span></div>"
)

cmd = [
    EDGE_PATH,
    "--headless",
    "--disable-gpu",
    "--run-all-compositor-stages-before-draw",
    f"--print-to-pdf={OUTPUT_PDF}",
    "--display-header-footer",
    f"--header-template={header_html}",
    f"--footer-template={footer_html}",
    TEMP_RENDER_HTML
]

print(f"Compiling PDF via {EDGE_PATH}...")
res = subprocess.run(cmd, capture_output=True, text=True)

if os.path.exists(TEMP_RENDER_HTML):
    os.remove(TEMP_RENDER_HTML)

if os.path.exists(OUTPUT_PDF):
    size = os.path.getsize(OUTPUT_PDF)
    reader = pypdf.PdfReader(OUTPUT_PDF)
    pages = len(reader.pages)
    print("\n" + "="*50)
    print(f"SUCCESS: Generated PDF at:")
    print(f"  {OUTPUT_PDF}")
    print(f"File Size: {size:,} bytes")
    print(f"Total Pages: {pages} pages")
    print("="*50)
else:
    print("ERROR: PDF was not generated.")
    print("Edge stderr:", res.stderr)
    sys.exit(1)

