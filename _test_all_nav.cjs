const { chromium } = require('playwright');
const outDir = process.argv[2];
const BASE = 'http://127.0.0.1:8000';

async function checkSidebar(page, label, issues) {
  const items = await page.locator('aside nav a, aside nav button').allTextContents();
  const hasUbersicht = items.some(t => /Übersicht|Dashboard/i.test(t));
  console.log(`  [${label}] sidebar items: ${items.join(' | ')}`);
  return items;
}

(async () => {
  const browser = await chromium.launch();
  const page = await browser.newPage({ viewport: { width: 1440, height: 900 } });
  const errors = [];
  page.on('pageerror', e => errors.push(page.url() + ' :: ' + e.message));
  page.on('console', m => { if (m.type() === 'error') errors.push(page.url() + ' :: ' + m.text()); });

  // ===== ADMIN =====
  console.log('=== ADMIN ===');
  await page.goto(BASE + '/admin/login', { waitUntil: 'networkidle' });
  await page.locator('input[type="email"]').first().fill('admin@angebotjetzt.de');
  await page.locator('input[type="password"]').first().fill('AdminSecure2026!');
  await Promise.all([
    page.waitForNavigation({ waitUntil: 'networkidle', timeout: 10000 }).catch(() => {}),
    page.locator('button[type="submit"]').first().click(),
  ]);
  const adminBaseline = await checkSidebar(page, 'admin baseline');
  const adminLinks = await page.locator('aside nav a').all();
  const adminHrefs = [];
  for (const l of adminLinks) adminHrefs.push(await l.getAttribute('href'));
  for (const href of adminHrefs) {
    await page.goto(BASE + href, { waitUntil: 'networkidle', timeout: 15000 }).catch(e => errors.push(href + ' NAV FAIL'));
    const items = await checkSidebar(page, 'admin @ ' + href);
    if (JSON.stringify(items) !== JSON.stringify(adminBaseline)) {
      console.log(`  !!! SIDEBAR CHANGED at ${href}`);
      await page.screenshot({ path: outDir + '/BUG-admin-' + href.replace(/\//g, '_') + '.png' });
    }
  }

  console.log(JSON.stringify(errors, null, 2));
  await browser.close();
})();
