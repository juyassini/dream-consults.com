"""
fetch_images.py

Simple script to download representative images from Unsplash Source API
into the project's `img/` folder. No API key required. Run:

    python fetch_images.py

Notes:
- Requires internet access and Python 3.
- If `python` is not installed, use the Unsplash URLs already in your HTML instead.
"""
import os
import urllib.request

IMAGES = {
    "welcome-office": "office team client,office,meeting",
    "service-supply": "warehouse logistics supply chain",
    "service-marketing": "farmers market agriculture",
    "service-profit": "business meeting team finance",
    "about-team": "team office smiling clients",
}

OUT_DIR = os.path.join(os.path.dirname(__file__), 'img')
os.makedirs(OUT_DIR, exist_ok=True)

print('Downloading images into:', OUT_DIR)
for name, query in IMAGES.items():
    url = f"https://source.unsplash.com/800x600/?{urllib.request.quote(query)}"
    out_path = os.path.join(OUT_DIR, f"{name}.jpg")
    try:
        print(f"Fetching {name} <- {url}")
        urllib.request.urlretrieve(url, out_path)
        print("Saved:", out_path)
    except Exception as e:
        print("Failed to download", name, "->", e)

print('Done.')
