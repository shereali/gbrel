const http = require('http');

const publicRoutes = [
  '/',
  '/properties',
  '/properties/1',
  '/advisors',
  '/advisors/1',
  '/compare',
  '/dashboard',
  '/list-property',
  '/contact',
  '/login',
  '/signup'
];

const adminRoutes = [
  '/admin',
  '/admin/login',
  '/admin/properties',
  '/admin/approvals',
  '/admin/viewings',
  '/admin/advisors',
  '/admin/users',
  '/admin/leads',
  '/admin/financials',
  '/admin/settings'
];

async function checkRoute(path) {
  return new Promise((resolve) => {
    http.get(`http://localhost:3002${path}`, (res) => {
      let data = '';
      res.on('data', chunk => { data += chunk; });
      res.on('end', () => {
        console.log(`[HTTP ${res.statusCode}] Route: ${path.padEnd(25)} | Length: ${data.length} bytes`);
        resolve(res.statusCode === 200);
      });
    }).on('error', (err) => {
      console.error(`[ERROR] Route: ${path} - ${err.message}`);
      resolve(false);
    });
  });
}

async function run() {
  console.log('=== 1. Testing Public Website Routes (Default Layout) ===');
  let publicOk = true;
  for (const r of publicRoutes) {
    const ok = await checkRoute(r);
    if (!ok) publicOk = false;
  }

  console.log('\n=== 2. Testing Dedicated Admin Routes (Admin Layout) ===');
  let adminOk = true;
  for (const r of adminRoutes) {
    const ok = await checkRoute(r);
    if (!ok) adminOk = false;
  }

  console.log('\n======================================================');
  if (publicOk && adminOk) {
    console.log('✅ ALL 19 PUBLIC & ADMIN ROUTES RETURNED HTTP 200 OK!');
  } else {
    console.log('❌ Some routes encountered issues.');
  }
}

run();
