#!/bin/bash
# Kiem tra asset (anh/css/js) bi thieu tren tung trang cua sandienmay.com.
# CACHE=/duong/dan/tmp dir, NSEC=so URL lay tu sitemap, EXTRA="url1 url2"
BASE="${BASE:-https://sandienmay.com}"
TMP=$(mktemp -d)

# gop URL can do: trang chu + sitemap + EXTRA
urls="$BASE/"
if [ -n "$SITEMAP" ]; then
  urls="$urls $(curl -s "$BASE/sitemap.xml" | grep -oE '<loc>[^<]+' | sed 's/<loc>//' | head -"$SITEMAP")"
fi
[ -n "$EXTRA" ] && urls="$urls $EXTRA"

check() {
  local a="$1"
  case "$a" in
    http*://*) ;;
    //*) a="https:${a%%\?*}" ;;
    /*) a="$BASE${a%%\?*}" ;;
    *) return 0 ;;
  esac
  a="${a%%\?*}"
  local c
  c=$(curl -s -o /dev/null -w "%{http_code}" "$a")
  [ "$c" != "200" ] && echo "   MISSING $c  $a"
}

for u in $urls; do
  f="$TMP/$(echo "$u" | md5sum | cut -c1-8).html"
  code=$(curl -sL -o "$f" -w "%{http_code}" "$u")
  echo "### $u -> $code ($(wc -c < "$f") bytes)"
  case "$code" in 200|301|302) ;; *) continue ;; esac
  grep -oE '(src|href|data-src|data-original|poster)="[^"]+"' "$f" \
    | sed -E 's/^[a-z-]+="//; s/"$//' \
    | grep -E '\.(png|jpe?g|webp|gif|svg|ico|css|js)([?#]|$)' \
    | sort -u > "$f.assets"
  echo "   assets: $(wc -l < "$f.assets")"
  while read -r a; do check "$a"; done < "$f.assets"
done
