import { spawn } from 'child_process';
import { writeFileSync, mkdirSync, readFileSync } from 'fs';
import path from 'path';
import { fileURLToPath } from 'url';

const scriptDir = path.dirname(fileURLToPath(import.meta.url));
const lighthouseCli = path.join(scriptDir, 'node_modules', 'lighthouse', 'cli', 'index.js');
const chromePath = 'C:\\Program Files\\Google\\Chrome\\Application\\chrome.exe';

const base = 'https://distribuidoralunic.com.ar.dev';
const pages = [
  { key: 'inicio', url: `${base}/` },
  { key: 'tienda', url: `${base}/tienda/` },
  { key: 'ficha', url: `${base}/tienda/condimentos-y-especias/cardamomo-fruto-guatemala/` },
  { key: 'checkout', url: `${base}/finalizar-comprar/` },
];

const outDir = path.join(scriptDir, 'linea-base', 'lighthouse');
mkdirSync(outDir, { recursive: true });

function runLighthouse(url, formFactor, reportKey) {
  return new Promise((resolve, reject) => {
    const out = path.join(outDir, `${reportKey}.json`);
    const args = [
      lighthouseCli,
      url,
      '--quiet',
      `--chrome-path=${chromePath}`,
      '--chrome-flags=--headless --no-sandbox',
      '--only-categories=performance',
      `--form-factor=${formFactor}`,
      '--output=json',
      `--output-path=${out}`,
    ];
    if (formFactor === 'desktop') {
      args.push('--screenEmulation.disabled');
      args.push('--throttling.cpuSlowdownMultiplier=1');
    }
    const child = spawn(process.execPath, args, {
      stdio: ['ignore', 'pipe', 'pipe'],
    });
    let stderr = '';
    child.stderr.on('data', (d) => { stderr += d; });
    child.on('close', (code) => {
      try {
        if (readFileSync(out, 'utf8').includes('"lighthouseVersion"')) {
          resolve(out);
          return;
        }
      } catch {
        // no report yet
      }
      if (code !== 0) {
        reject(new Error(stderr || `exit ${code}`));
        return;
      }
      resolve(out);
    });
  });
}

function extract(reportPath) {
  const report = JSON.parse(readFileSync(reportPath, 'utf8'));
  const audits = report.audits;
  const perf = report.categories.performance?.score;
  const lcp = audits['largest-contentful-paint']?.numericValue;
  const inp = audits['interaction-to-next-paint']?.numericValue
    ?? audits['experimental-interaction-to-next-paint']?.numericValue;
  const cls = audits['cumulative-layout-shift']?.numericValue;
  let jsKb = 0;
  let cssKb = 0;
  const network = audits['network-requests']?.details?.items || [];
  for (const item of network) {
    const mime = item.mimeType || '';
    const size = item.transferSize || 0;
    if (mime.includes('javascript')) jsKb += size;
    if (mime.includes('css')) cssKb += size;
  }
  return {
    performance: perf != null ? Math.round(perf * 100) : null,
    lcp: lcp != null ? `${(lcp / 1000).toFixed(1)} s` : '—',
    inp: inp != null ? `${Math.round(inp)} ms` : '—',
    cls: cls != null ? Math.round(cls * 1000) / 1000 : null,
    jsTransferKb: Math.round(jsKb / 1024),
    cssTransferKb: Math.round(cssKb / 1024),
  };
}

const results = [];
for (const page of pages) {
  for (const formFactor of ['mobile', 'desktop']) {
    const tag = `${page.key}-${formFactor}`;
    process.stdout.write(`Running ${tag}...\n`);
    try {
      const reportPath = await runLighthouse(page.url, formFactor, tag);
      const metrics = extract(reportPath);
      results.push({ page: page.key, url: page.url, formFactor, ...metrics });
    } catch (e) {
      results.push({ page: page.key, url: page.url, formFactor, error: String(e.message || e) });
    }
  }
}

writeFileSync(path.join(outDir, 'summary.json'), JSON.stringify(results, null, 2));
console.log(JSON.stringify(results, null, 2));
