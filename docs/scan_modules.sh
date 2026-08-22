#!/bin/bash
# Visit each Sabaq module page and capture title + URL
cd /c/Users/shere/Desktop/SabaaqNext

declare -a URLS=(
  "https://demo.sabaaq.com/dashboard"
  "https://demo.sabaaq.com/module-dashboard"
  "https://demo.sabaaq.com/reminder-tasks"
  "https://demo.sabaaq.com/leave-management"
  "https://demo.sabaaq.com/system-settings"
  "https://demo.sabaaq.com/digital-attendance"
  "https://demo.sabaaq.com/student-management"
  "https://demo.sabaaq.com/academic"
  "https://demo.sabaaq.com/promotion-graduation"
  "https://demo.sabaaq.com/teacher-management"
  "https://demo.sabaaq.com/administration"
  "https://demo.sabaaq.com/accounting"
  "https://demo.sabaaq.com/receipt-management"
  "https://demo.sabaaq.com/sponsor-donation"
  "https://demo.sabaaq.com/loan-due"
  "https://demo.sabaaq.com/orphan-sponsorship"
  "https://demo.sabaaq.com/boarding"
  "https://demo.sabaaq.com/notices"
  "https://demo.sabaaq.com/changelog"
)

USER_AGENT="Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36"

for url in "${URLS[@]}"; do
  echo "========================================="
  echo "URL: $url"
  echo "---"
  status=$(curl -s -o /tmp/page.html -w "%{http_code}" -L \
    -H "User-Agent: $USER_AGENT" \
    -H "Accept: text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8" \
    --max-time 10 "$url" 2>/dev/null)
  echo "HTTP: $status"
  title=$(python3 -c "
import re
try:
    html = open('/tmp/page.html', encoding='utf-8', errors='replace').read()
    m = re.search(r'<title[^>]*>([^<]+)</title>', html, re.I)
    print(m.group(1).strip() if m else 'NO TITLE')
    # also try to extract visible Bengali text snippet
    body = re.sub(r'<[^>]+>', ' ', html)
    body = re.sub(r'\s+', ' ', body).strip()
    bengali = re.findall(r'[\u0980-\u09FF]{3,}', body)
    if bengali:
        print('BANGLA:', ' '.join(bengali[:8]))
except Exception as e:
    print('ERROR:', e)
" 2>/dev/null)
  echo "$title"
  echo ""
done
