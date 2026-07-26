const { chromium } = require('playwright');
const outDir = process.argv[2];
(async () => {
  const browser = await chromium.launch();
  const page = await browser.newPage({ viewport: { width: 1440, height: 900 } });
  page.on('pageerror', e => console.log('PAGEERROR:', e.message));
  page.on('console', m => console.log('CONSOLE[' + m.type() + ']:', m.text()));
  page.on('response', r => { if (r.url().includes('/register')) console.log('RESPONSE:', r.status(), r.url()); });

  const email = 'regtest2.' + Date.now() + '@example.de';
  await page.goto('http://127.0.0.1:8000/register', { waitUntil: 'networkidle' });

  await page.locator('input[name="name"]').fill('Reg Test');
  await page.locator('input[type="email"]').fill(email);
  await page.locator('input[name="password"]').fill('weakpass');
  await page.locator('input[name="password_confirmation"]').fill('weakpass');
  await page.locator('button[type="submit"]').click();
  await page.waitForTimeout(2000);
  console.log('URL AFTER SUBMIT:', page.url());
  console.log('BUTTON DISABLED:', await page.locator('button[type="submit"]').isDisabled());
  const bodyText = await page.locator('body').innerText();
  console.log('BODY:', bodyText);
  await page.screenshot({ path: outDir + '/reg-debug.png' });

  await browser.close();
})();
