const { chromium } = require('playwright');
const outDir = process.argv[2];
const BASE = 'http://127.0.0.1:8000';

async function checkSidebar(page, label) {
  const items = await page.locator('aside nav a, aside nav button').allTextContents();
  console.log(`  [${label}] sidebar items: ${items.join(' | ')}`);
  return items;
}

(async () => {
  const browser = await chromium.launch();
  const page = await browser.newPage({ viewport: { width: 1440, height: 900 } });
  const errors = [];
  page.on('pageerror', e => errors.push(page.url() + ' :: ' + e.message));
  page.on('console', m => { if (m.type() === 'error') errors.push(page.url() + ' :: ' + m.text()); });

  console.log('=== CUSTOMER ===');
  await page.goto(BASE + '/login', { waitUntil: 'networkidle' });
  await page.locator('input[type="email"]').first().fill('route.test.1784800087534@example.de');
  await page.locator('input[type="password"]').first().fill('TestPass2026!');
  await Promise.all([
    page.waitForNavigation({ waitUntil: 'networkidle', timeout: 10000 }).catch(() => {}),
    page.locator('button[type="submit"]').first().click(),
  ]);
  const custBaseline = await checkSidebar(page, 'customer baseline');
  const custLinks = await page.locator('aside nav a').all();
  const custHrefs = [];
  for (const l of custLinks) custHrefs.push(await l.getAttribute('href'));
  for (const href of custHrefs) {
    await page.goto(BASE + href, { waitUntil: 'networkidle', timeout: 15000 }).catch(e => errors.push(href + ' NAV FAIL'));
    const items = await checkSidebar(page, 'customer @ ' + href);
    if (JSON.stringify(items) !== JSON.stringify(custBaseline)) {
      console.log(`  !!! SIDEBAR CHANGED at ${href}`);
      await page.screenshot({ path: outDir + '/BUG-customer-' + href.replace(/\//g, '_') + '.png' });
    }
  }

  console.log('=== INSPECTOR ===');
  await page.goto(BASE + '/inspector/login', { waitUntil: 'networkidle' });
  await page.locator('input[type="email"]').first().fill('gutachter4@angebotjetzt.de');
  await page.locator('input[type="password"]').first().fill('Gutachter2026!');
  await Promise.all([
    page.waitForNavigation({ waitUntil: 'networkidle', timeout: 10000 }).catch(() => {}),
    page.locator('button[type="submit"]').first().click(),
  ]);
  const inspBaseline = await checkSidebar(page, 'inspector baseline');
  const inspLinks = await page.locator('aside nav a').all();
  const inspHrefs = [];
  for (const l of inspLinks) inspHrefs.push(await l.getAttribute('href'));
  for (const href of inspHrefs) {
    await page.goto(BASE + href, { waitUntil: 'networkidle', timeout: 15000 }).catch(e => errors.push(href + ' NAV FAIL'));
    const items = await checkSidebar(page, 'inspector @ ' + href);
    if (JSON.stringify(items) !== JSON.stringify(inspBaseline)) {
      console.log(`  !!! SIDEBAR CHANGED at ${href}`);
      await page.screenshot({ path: outDir + '/BUG-inspector-' + href.replace(/\//g, '_') + '.png' });
    }
  }

  console.log('ERRORS:', JSON.stringify(errors, null, 2));
  await browser.close();
})();
