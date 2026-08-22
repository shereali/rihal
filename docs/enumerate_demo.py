#!/usr/bin/env python3
"""Standalone: load demo.sabaaq.com, extract CSRF token, login, enumerate sidebar."""
import http.cookiejar
import urllib.request
import urllib.parse
import ssl
import re
import sys
import gzip

LOGIN_URL = "https://demo.sabaaq.com/login"
DASH_URL = "https://demo.sabaaq.com/dashboard"
USER = "admin@demo.bd"
PASS = "admin123"
UA = ("Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 "
      "(KHTML, like Gecko) Chrome/124.0.0.0 Safari/537.36")

def make_opener():
    cj = http.cookiejar.CookieJar()
    hdrs = {
        "User-Agent": UA,
        "Accept": "text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8",
        "Accept-Language": "en-US,bn-BD;q=0.9,bn;q=0.8,en;q=0.7",
        "Accept-Encoding": "gzip, deflate",
        "Connection": "keep-alive",
        "Upgrade-Insecure-Requests": "1",
    }
    opener = urllib.request.build_opener(
        urllib.request.HTTPSHandler(context=ssl.create_default_context()),
        urllib.request.HTTPCookieProcessor(cj),
    )
    for k, v in hdrs.items():
        opener.addheader(k, v)
    return opener, cj

def fetch(url, opener, data=None, method="GET"):
    if data is not None:
        body = urllib.parse.urlencode(data).encode("utf-8")
        req = urllib.request.Request(url, data=body, method=method)
        req.add_header("Content-Type", "application/x-www-form-urlencoded")
    else:
        req = urllib.request.Request(url)
    with opener.open(req, timeout=30) as r:
        enc = r.headers.get("Content-Encoding", "")
        raw = r.read()
        if enc == "gzip":
            raw = gzip.decompress(raw)
        return raw.decode("utf-8", "replace"), r.status

def text_of(html):
    s = re.sub(r"<[^>]+>", " ", html)
    return re.sub(r"\s+", " ", s).strip()

def extract_csrf(html):
    m = re.search(r'name="_token"[^>]*value="([^"]+)"', html, re.I)
    if not m:
        # Laravel 7+ often uses this pattern:
        m = re.search(r'<input[^>]+_token[^>]+value="([^"]+)"', html, re.I)
    if not m:
        # Fallback: any input with name _token
        m = re.search(r'<input[^>]+name=["\']_token["\'][^>]+value=["\']([^"\']+)["\']', html, re.I)
    return m.group(1) if m else None

def sidebar_links(html):
    """Extract <a href=...>text</a> from sidebar area."""
    # Try common sidebar container first
    sidebar = re.search(r'(<nav[^>]*id=["\'][^"\']*sidebar[^"\']*>.*?</nav>)', html, re.S|re.I)
    region = sidebar.group(1) if sidebar else html
    links = re.findall(r'<a[^>]+href=["\']([^"\']+)["\'][^>]*>(.*?)</a>', region, re.S|re.I)
    out = []
    for href, inner in links:
        label = re.sub(r"<[^>]+>", "", inner)
        label = re.sub(r"\s+", " ", label).strip()
        if label and href and not href.startswith(("#", "javascript:")):
            out.append((href, label))
    return out

def menu_items(html):
    """Extract menu labels from <li> or nav items that look like sidebar entries."""
    # Look for repeated label patterns: Bengali text in <a> or <span> inside list items
    items = re.findall(r'<a[^>]+href=["\']([^"\']+)["\'][^>]*>\s*([^<]+?)\s*</a>', html, re.I)
    out = []
    for href, label in items:
        label = re.sub(r"\s+", " ", label).strip()
        if label and len(label) >= 2 and not href.startswith(("#", "javascript:")):
            out.append((href, label))
    return out

opener, cj = make_opener()

# ---- step 1: GET login page, capture token + cookies ----
print("[1] GET login page...")
lp_html, lp_status = fetch(LOGIN_URL, opener)
print(f"    status={lp_status} len={len(lp_html)}")
_token = extract_csrf(lp_html)
print(f"    CSRF _token: {_token}")
if not _token:
    print("    [!] Could not extract _token — trying regex variants...")
    # Show form area
    form = re.search(r'<form[^>]*>.*?</form>', lp_html, re.S|re.I)
    print("    form snippet:", (form.group(0)[:800] if form else "NONE"))

# ---- step 2: POST login with token ----
print("[2] POST login with _token...")
payload = {"_token": _token, "email": USER, "password": PASS}
resp_html, resp_status = fetch(LOGIN_URL, opener, data=payload, method="POST")
print(f"    status={resp_status} len={len(resp_html)}")
snippet = text_of(resp_html)[:300]
print(f"    snippet: {snippet}")
if "dashboard" in resp_html.lower() or "ড্যাশবোর্ড" in resp_html or "স্বাগতম" in resp_html or "authenticated" in resp_html.lower():
    print("    [✓] Authenticated (dashboard/welcome text present)")
elif resp_status == 419:
    print("    [!] 419 again — token may be wrong or expired; login page snippet:")
    print("    ", text_of(lp_html)[:400])
else:
    print("    [?] Auth unclear — inspecting redirect / body...")

# ---- step 3: fetch dashboard ----
print("[3] GET dashboard...")
dash_html, dash_status = fetch(DASH_URL, opener)
print(f"    status={dash_status} len={len(dash_html)}")
dash_text = text_of(dash_html)[:400]
print(f"    snippet: {dash_text}")

if dash_status == 200 and ("ড্যাশবোর্ড" in dash_html or "dashboard" in dash_html.lower()):
    print("    [✓] Dashboard accessible — extracting sidebar links...")
    links = sidebar_links(dash_html)
    print(f"    [✓] Found {len(links)} sidebar <a> links:")
    for href, label in links:
        print(f"      {label!r:40} -> {href}")
    # Also print raw menu items
    print("    [✓] All <a href> with label in full HTML (first 80):")
    all_links = menu_items(dash_html)
    for href, label in all_links[:80]:
        print(f"      {label!r:40} -> {href}")
else:
    print("    [?] Dashboard not clearly accessible. Full text:")
    print("    ", dash_text)

print("[done]")
