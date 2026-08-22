#!/usr/bin/env python3
"""Login to demo.sabaaq.com and capture dashboard HTML. Stdlib only."""
import re
import http.cookiejar
import urllib.request
import urllib.parse
import ssl

LOGIN_URL = "https://demo.sabaaq.com/login"
DASHBOARD_URL = "https://demo.sabaaq.com/dashboard"
USER = "admin@demo.bd"
PASS = "admin123"

OUT_DASHBOARD = "/c/Users/shere/Desktop/SabaaqNext/docs/DEMO_DASHBOARD.html"
OUT_LOGIN = "/c/Users/shere/Desktop/SabaaqNext/docs/DEMO_LOGIN.html"

HEADERS = {
    "User-Agent": "Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36",
    "Accept": "text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8",
    "Accept-Language": "en-US,en;q=0.5",
    "Accept-Encoding": "gzip, deflate, br",
}

cj = http.cookiejar.CookieJar()
opener = urllib.request.build_opener(
    urllib.request.HTTPSHandler(context=ssl.create_default_context()),
    urllib.request.HTTPCookieProcessor(cj),
)
opener.addheaders = [(k, v) for k, v in HEADERS.items()]


def get(url):
    req = urllib.request.Request(url, headers={"Accept": "text/html,*/*"})
    with opener.open(req, timeout=25) as resp:
        raw = resp.read()
        encoding = resp.headers.get_content_charset() or "utf-8"
        return raw.decode(encoding, errors="replace"), dict(resp.headers)


print("=== STEP 1: GET login page ===")
login_html, login_headers = get(LOGIN_URL)
with open(OUT_LOGIN, "w", encoding="utf-8") as f:
    f.write(login_html)
print("Saved login page:", OUT_LOGIN)
print("Cookies after GET:", [c.name for c in cj])

csrf_match = re.search(r'name="_token"[^>]*value="([^"]+)"', login_html)
csrf = csrf_match.group(1) if csrf_match else None
print("CSRF token:", csrf)
print("Login page length:", len(login_html))


def post_login():
    data = urllib.parse.urlencode({
        "_token": csrf or "",
        "email": USER,
        "password": PASS,
    }).encode("utf-8")
    req = urllib.request.Request(LOGIN_URL, data=data, method="POST",
                                 headers={"Content-Type": "application/x-www-form-urlencoded", "Accept": "text/html,*/*"})
    try:
        with opener.open(req, timeout=25) as resp:
            raw = resp.read()
            encoding = resp.headers.get_content_charset() or "utf-8"
            body = raw.decode(encoding, errors="replace")
            print("POST login status:", resp.status)
            print("POST login length:", len(body))
            print("POST Cookies after:", [c.name for c in cj])
            print("Set-Cookie:", [c for c in resp.headers.get_all("Set-Cookie") if c] if hasattr(resp.headers, 'get_all') else resp.headers.get("Set-Cookie", "none"))
            return body
    except urllib.error.HTTPError as e:
        print("HTTPError:", e.code, e.reason)
        print("Body:", e.read().decode("utf-8", errors="replace")[:500])
        return None
    except Exception as e:
        print("Exception:", type(e).__name__, e)
        return None


login_body = post_login()
if login_body:
    with open("/c/Users/shere/Desktop/SabaaqNext/docs/DEMO_LOGIN_POST.html", "w", encoding="utf-8") as f:
        f.write(login_body)
    print("Saved login POST response.")

print("\n=== STEP 2: GET dashboard ===")
try:
    dash_html, dash_headers = get(DASHBOARD_URL)
    with open(OUT_DASHBOARD, "w", encoding="utf-8") as f:
        f.write(dash_html)
    print("Saved dashboard:", OUT_DASHBOARD)
    print("Dashboard length:", len(dash_html))
    print("Cookies at dashboard:", [c.name for c in cj])
    # detect redirect
    if "login" in dash_html.lower() and "redirect" in dash_html.lower():
        print("RESULT: REDIRECTED back to login (auth failed or session missing)")
    else:
        print("RESULT: dashboard HTML captured")
except Exception as e:
    print("Dashboard fetch error:", type(e).__name__, e)

print("\n=== Done ===")
