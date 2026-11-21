#!/bin/bash
# Do khop alt tren HTML THAT cua tung loai trang (demo hoac local).
# Cach dung: bash deploy/alt-audit.sh http://sandienmay.test "/tang-url-1" "/tang-url-2"
#            bash deploy/alt-audit.sh https://demo.sandienmay.com "/" "/may-hut-bui-cong-nghiep"
#
# Phai doc ca the <img> nhe (perl -0777), vi Blade render the <img> cat ngang dong;
# ban grep -oiE '<img[^>]*>' theo dong bo sot hoan toan cac the do -> dem sai "0 img".
# Bẫy ngược lại: neu loc bang regex thi `alt="{{ $brand->name }}"` chua ky tu `>` nen
# cat som -> báo nhầm "thieu alt". O day do HTML sau render nen khong bi.
BASE="${1:-https://demo.sandienmay.com}"
shift
UA="Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/131 Safari/537.36"

PYPARSER=$(mktemp /tmp/altparser.pl.XXXXXX)
cat > "$PYPARSER" <<'PERL'
local $/; open F, '<', $ARGV[0] or die; my $h = <F>; close F;
my ($t, $n, $e) = (0, 0, 0);
my %bad;
sub lastname { my ($s) = @_; $s = '?' unless defined $s; my @p = split m{/}, $s; return $p[-1]; }
while ($h =~ /<img\b[^>]*>/gi) {
    my $tag = $&;
    $t++;
    my ($src) = $tag =~ /\bsrc="([^"]*)"/;
    my $nm    = lastname($src);
    if ($tag !~ /\balt=/i) { $n++; $bad{"THIEU ALT : $nm"}++; }
    elsif ($tag =~ /\balt="\s*"/) { $e++; $bad{"ALT RONG : $nm"}++; }
}
printf("   img=%d  thieu_alt=%d  alt_rong=%d\n", $t, $n, $e);
printf("   %s  x%d\n", $_, $bad{$_}) for sort keys %bad;
PERL

for p in "$@"; do
  f=$(mktemp /tmp/alt-page.XXXXXX)
  # -L: mot so trang (tim kiem) 302 ve chinh no, khong theo doi thi dem duoc img=0
  code=$(curl -sL -A "$UA" -o "$f" -w '%{http_code}' --max-time 60 "$BASE$p")
  echo "== $BASE$p [$code]"
  perl "$PYPARSER" "$f"
  rm -f "$f"
done
rm -f "$PYPARSER"
