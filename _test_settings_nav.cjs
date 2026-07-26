const { chromium } = require('playwright');
const outDir = process.argv[2];
(async () => {
  const browser = await chromium.launch();
  const page = await browser.newPage({ viewport: { width: 1440, height: 900 } });
  const errors = [];
  page.on('pageerror', e => errors.push(page.url() + ' :: ' + e.message));
  page.on('console', m => { if (m.type() === 'error') errors.push(page.url() + ' :: ' + m.text()); });

  await page.goto('http://127.0.0.1:8000/login', { waitUntil: 'networkidle' });
  await page.locator('input[type="email"]').first().fill('route.test.1784800087534@example.de');
  await page.locator('input[type="password"]').first().fill('TestPass2026!');
  await Promise.all([
    page.waitForNavigation({ waitUntil: 'networkidle', timeout: 10000 }).catch(() => {}),
    page.locator('button[type="submit"]').first().click(),
  ]);
  console.log('LOGIN URL:', page.url());
  await page.screenshot({ path: outDir + '/before-settings-click.png' });

  await Promise.all([
    page.waitForNavigation({ waitUntil: 'networkidle', timeout: 10000 }).catch(() => {}),
    page.locator('a:has-text("Einstellungen")').first().click(),
  ]);
  console.log('AFTER CLICK URL:', page.url());
  await page.screenshot({ path: outDir + '/after-settings-click.png' });

  console.log('ERRORS:', JSON.stringify(errors, null, 2));
  await browser.close();
})();
