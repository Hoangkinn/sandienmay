/*
 * Frontend asset builder.
 *
 * Sources you edit:
 *   public/css-new/src/*.css        -> public/css-new/dist/*.min.css
 *   public/js-new/fe/main.js        -> public/js-new/fe/main.min.js
 *   public/js-new/fe/pages/*.js     -> public/js-new/fe/pages/*.min.js
 *
 * Usage:
 *   node scripts/build-fe.js          build everything
 *   node scripts/build-fe.js css      build CSS only
 *   node scripts/build-fe.js js       build JS only
 */
const fs = require('fs');
const path = require('path');
const CleanCSS = require('clean-css');
const { minify } = require('terser');

const root = path.resolve(__dirname, '..');
const pub = path.join(root, 'public');
const cssSrc = path.join(pub, 'css-new', 'src');
const cssDist = path.join(pub, 'css-new', 'dist');
const jsFe = path.join(pub, 'js-new', 'fe');
const jsPages = path.join(jsFe, 'pages');

const target = (process.argv[2] || 'all').toLowerCase();

function ensureDir(dir) {
    if (!fs.existsSync(dir)) fs.mkdirSync(dir, { recursive: true });
}

async function buildCss() {
    ensureDir(cssDist);
    const clean = new CleanCSS({ level: 1, returnPromise: true });
    const files = fs.readdirSync(cssSrc).filter(f => f.endsWith('.css'));
    for (const file of files) {
        const src = fs.readFileSync(path.join(cssSrc, file), 'utf8');
        const out = await clean.minify(src);
        if (out.errors && out.errors.length) {
            throw new Error(`${file}: ${out.errors.join('; ')}`);
        }
        const name = file.replace(/\.css$/, '.min.css');
        fs.writeFileSync(path.join(cssDist, name), out.styles);
        console.log(`css  ${file} -> dist/${name}  (${Buffer.byteLength(src)} -> ${Buffer.byteLength(out.styles)} B)`);
    }
}

async function minifyJs(srcPath, outPath) {
    const code = fs.readFileSync(srcPath, 'utf8');
    const res = await minify(code, { compress: true, mangle: true });
    if (res.error) throw new Error(`${srcPath}: ${res.error.message}`);
    fs.writeFileSync(outPath, res.code);
    console.log(`js   ${path.relative(pub, srcPath)} -> ${path.basename(outPath)}  (${Buffer.byteLength(code)} -> ${Buffer.byteLength(res.code)} B)`);
}

async function buildJs() {
    await minifyJs(path.join(jsFe, 'main.js'), path.join(jsFe, 'main.min.js'));
    if (fs.existsSync(jsPages)) {
        for (const file of fs.readdirSync(jsPages).filter(f => f.endsWith('.js') && !f.endsWith('.min.js'))) {
            await minifyJs(path.join(jsPages, file), path.join(jsPages, file.replace(/\.js$/, '.min.js')));
        }
    }
}

(async () => {
    if (target === 'all' || target === 'css') await buildCss();
    if (target === 'all' || target === 'js') await buildJs();
})().catch(err => {
    console.error(err.message);
    process.exit(1);
});
