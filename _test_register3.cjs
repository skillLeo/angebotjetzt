const { chromium } = require('playwright');
(async () => {
  const browser = await chromium.launch();
  const page = await browser.newPage({ viewport: { width: 1440, height: 900 } });
  page.on('pageerror', e => console.log('PAGEERROR:', e.message));

  page.on('response', async (r) => {
    if (r.url().includes('/register')) {
      const headers = r.headers();
      console.log('---RESPONSE---', r.request().method(), r.status(), r.url());
      console.log('  x-inertia:', headers['x-inertia']);
      console.log('  content-type:', headers['content-type']);
      console.log('  location:', headers['location']);
    }
  });

  const email = 'regtest3.' + Date.now() + '@example.de';
  await page.goto('http://127.0.0.1:8000/register', { waitUntil: 'networkidle' });

  await page.locator('input[name="name"]').fill('Reg Test');
  await page.locator('input[type="email"]').fill(email);
  await page.locator('input[name="password"]').fill('weakpass');
  await page.locator('input[name="password_confirmation"]').fill('weakpass');
  await page.locator('button[type="submit"]').click();
  await page.waitForTimeout(2500);

  console.log('FINAL URL:', page.url());
  console.log('BUTTON DISABLED:', await page.locator('button[type="submit"]').isDisabled());

  await browser.close();
})();
