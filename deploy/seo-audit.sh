#!/bin/bash
# Kiem tra the <title> / meta description / canonical tren tung loai trang (local)
urls="
/
/tin-tuc-may
/tin-tuc-may-hut-bui
/may-hut-bui-cong-nghiep
/hoang-test
/demo-tu-chong-am-4
/demo-hd-chon-may-hut-bui
/demo-may-hut-bui-kms601
/kumisai-kms-601-60-lit
/tim-kiem?q=may
/gio-hang
"
for u in $urls; do
  html=$(curl -s "http://sandienmay.test$u")
  code=$(curl -s -o /dev/null -w '%{http_code}' "http://sandienmay.test$u")
  t=$(printf '%s' "$html" | grep -oE '<title>[^<]*</title>' | head -1 | sed -e 's/<[^>]*>//g')
  d=$(printf '%s' "$html" | grep -oE '<meta name="description" content="[^"]*"' | head -1 | sed -e 's/.*content="//' -e 's/"$//')
  c=$(printf '%s' "$html" | grep -oE '<link rel="canonical" href="[^"]*"' | head -1 | sed -e 's/.*href="//' -e 's/"$//')
  echo "[$code] $u"
  echo "    title='${t:0:60}'"
  echo "    desc ='${d:0:40}'"
  echo "    canon='${c:0:50}'"
done
