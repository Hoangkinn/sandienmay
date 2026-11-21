#!/bin/bash
# Phan loai cac <img> alt="" thanh 2 nhom: chrome UI (decorative) vs anh NOI DUNG
BASE="${1:-https://demo.sandienmay.com}"
UA="Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/131 Safari/537.36"
CHROME='(hot\.svg|new\.png|icon[1-4]\.svg|search\.png|shopping-cart\.svg|phone-call[^"]*\.svg|ic-right\.svg|gif\.png)'
for p in "/" "/may-hut-bui-cong-nghiep" "/kumisai-kms-c1500" "/tin-tuc-may" "/demo-may-hut-bui-1" "/gio-hang"; do
  curl -s -A "$UA" -o /tmp/c.html "$BASE$p"
  tot=$(grep -oiE '<img[^>]*>' /tmp/c.html | wc -l)
  emp=$(grep -oiE '<img[^>]*>' /tmp/c.html | grep -cE 'alt=""')
  ui=$(grep -oiE '<img[^>]*>' /tmp/c.html | grep -E 'alt=""' | grep -cE "$CHROME")
  content=$((emp - ui))
  noalt=$(grep -oiE '<img[^>]*>' /tmp/c.html | grep -civ 'alt=')
  printf '%-30s img=%-4s thieu-alt=%-3s alt=\"\"=%-4s (chrome-UI=%-3s | ANH-NOI-DUNG=%s)\n' "$p" "$tot" "$noalt" "$emp" "$ui" "$content"
done
