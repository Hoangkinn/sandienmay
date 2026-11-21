const fs = require('fs');
for (const p of process.argv.slice(2)) {
  const j = JSON.parse(fs.readFileSync(p, 'utf8'));
  const m = j.audits['metrics'].details.items[0];
  const out = {
    file: p.split('/').pop(),
    url: j.finalDisplayedUrl,
    perf: Math.round(j.categories.performance.score * 100),
    a11y: Math.round(j.categories.accessibility.score * 100),
    bp: Math.round(j.categories['best-practices'].score * 100),
    seo: Math.round(j.categories.seo.score * 100),
    FCP: m.firstContentfulPaint, LCP: m.largestContentfulPaint, TBT: m.totalBlockingTime,
    SI: m.speedIndex, CLS: m.cumulativeLayoutShift,
    weightKiB: Math.round(j.audits['total-byte-weight'].numericValue / 1024),
    req: j.audits['network-requests'].numericValue,
  };
  const fails = Object.values(j.audits).filter(a => a.score !== null && a.score < 0.9 && a.scoreDisplayMode === 'binary')
    .map(a => a.id + ':' + Math.round(a.score * 100));
  const perfWarn = Object.values(j.audits).filter(a => a.scoreDisplayMode === 'numeric' && a.score !== null && a.score < 0.9)
    .map(a => a.id + '=' + Math.round(a.score * 100));
  console.log(JSON.stringify(out));
  console.log('  fail(binary):', fails.join(', ') || '-');
  console.log('  numeric<0.9 :', perfWarn.join(', ') || '-');
  const lcp = j.audits['largest-contentful-paint'];
  console.log('  LCP ngu:', (lcp.details && lcp.details.items && lcp.details.items[0] && lcp.details.items[0].node && lcp.details.items[0].node.nodeLabel) || '?');
}
