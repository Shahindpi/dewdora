import { chromium } from 'playwright';
import fs from 'node:fs';
import path from 'node:path';
const output = path.resolve(process.env.E2E_OUTPUT_DIR || 'test-results');
fs.mkdirSync(output, { recursive: true });
const email = process.env.E2E_ADMIN_EMAIL;
const password = process.env.E2E_ADMIN_PASSWORD;
if (!email || !password) throw new Error('Set E2E_ADMIN_EMAIL and E2E_ADMIN_PASSWORD for an isolated test installation.');
(async()=>{
 const browser = await chromium.launch({
   executablePath: process.env.E2E_CHROMIUM_PATH || undefined,
   args: JSON.parse(process.env.E2E_CHROMIUM_ARGS || '[]'),
   headless: true,
 });
 const page=await browser.newPage({viewport:{width:1440,height:1000}});
 await page.route('https://www.googletagmanager.com/**', route => route.abort());
 await page.addInitScript(() => { window.__dewdoraEvents=[]; window.dataLayer=[]; const originalPush=window.dataLayer.push.bind(window.dataLayer);window.dataLayer.push=(...args)=>{for(const entry of args)window.__dewdoraEvents.push(Array.from(entry));return originalPush(...args);}; });
 const results=[],errors=[],requests=[];
 page.on('pageerror',e=>errors.push(e.message));
 page.on('response',r=>{if(r.url().includes('/api/v1/'))requests.push({url:r.url(),status:r.status()});});
 page.on('dialog',d=>d.accept());
 const base=process.env.E2E_BASE_URL || 'http://127.0.0.1:3000';
 const stamp=Date.now().toString();
 const check=async(name,fn)=>{await fn();results.push({name,status:'PASS'});console.log('PASS',name);};
 try {
 await check('Browser login with Origin and bearer token',async()=>{
  await page.goto(base+'/auth/login');await page.locator('input[name=email]').fill(email);await page.locator('input[name=password]').fill(password);await page.getByRole('button',{name:'Sign In',exact:true}).click();await page.waitForURL('**/admin');await page.getByRole('heading',{name:/Dashboard/}).waitFor();
 });
 for(const [resource,title] of [['categories','Categories'],['tags','Tags'],['brands','Brands'],['networks','Affiliate networks'],['roles','Roles']]){
  await check(resource+' UI create, search, edit, delete',async()=>{
   await page.goto(base+'/admin/'+resource);await page.getByRole('heading',{name:title,exact:true}).waitFor();await page.getByRole('button',{name:'Add new',exact:true}).click();
   const name='Browser '+resource+' '+stamp;await page.locator('input[name=name]').fill(name);await page.locator('input[name=slug]').fill('browser-'+resource+'-'+stamp);
   if(resource==='categories')await page.locator('input[name=sort_order]').fill('0');
   await page.getByRole('button',{name:'Save',exact:true}).click();await page.getByRole('cell',{name,exact:true}).waitFor();
   await page.getByPlaceholder('Search '+title.toLowerCase()).fill(name);
   const row=page.getByRole('row').filter({has:page.getByRole('cell',{name,exact:true})});await row.getByRole('button',{name:'Edit',exact:true}).click();await page.locator('input[name=name]').fill(name+' Updated');await page.getByRole('button',{name:'Save',exact:true}).click();await page.getByRole('cell',{name:name+' Updated',exact:true}).waitFor();
   if(['categories','tags','brands'].includes(resource)){
     const publicResponse=await page.goto(base+'/'+resource+'/browser-'+resource+'-'+stamp);
     if(publicResponse.status()!==200)throw Error('Public detail returned '+publicResponse.status());
     await page.getByRole('heading',{name:(resource==='tags'?'#':'')+name+' Updated',exact:true}).waitFor();
     await page.goto(base+'/admin/'+resource);
     await page.getByPlaceholder('Search '+title.toLowerCase()).fill(name+' Updated');
   }
   await page.getByRole('row').filter({hasText:name+' Updated'}).getByRole('button',{name:'Delete',exact:true}).click();await page.getByText('No records found.',{exact:true}).waitFor();
  });
 }
 await check('Users UI create, list, change role, reset password, delete',async()=>{
  await page.goto(base+'/admin/users/new');await page.locator('input[name=name]').fill('Browser User '+stamp);await page.locator('input[name=username]').fill('browser-'+stamp);await page.locator('input[name=email]').fill('browser'+stamp+'@example.test');await page.locator('select[name=role_id]').selectOption({label:'Editor'});await page.locator('input[name=password]').fill('BrowserTest123!');await page.locator('input[name=password_confirmation]').fill('BrowserTest123!');await page.getByRole('button',{name:'Save user',exact:true}).click();await page.waitForURL('**/admin/users');
  let row=page.getByRole('row').filter({hasText:'browser'+stamp+'@example.test'});await row.getByRole('link',{name:'Edit',exact:true}).click();await page.locator('select[name=role_id] option:checked').filter({hasText:'Editor'}).waitFor({state:'attached'});await page.locator('select[name=role_id]').selectOption({label:'Author'});await page.locator('input[name=name]').fill('Browser User Updated '+stamp);await page.getByRole('button',{name:'Save user',exact:true}).click();await page.waitForURL('**/admin/users');row=page.getByRole('row').filter({hasText:'browser'+stamp+'@example.test'});await row.getByRole('cell',{name:'Author',exact:true}).waitFor();await row.getByRole('link',{name:'Edit',exact:true}).click();await page.locator('select[name=role_id] option:checked').filter({hasText:'Author'}).waitFor({state:'attached'});const reset=page.locator('form').filter({has:page.getByRole('heading',{name:'Reset password'})});await reset.locator('input[name=password]').fill('ChangedBrowser123!');await reset.locator('input[name=password_confirmation]').fill('ChangedBrowser123!');const passwordResult=page.waitForResponse(r=>r.url().includes('/password') && r.request().method()==='PUT');await reset.getByRole('button',{name:'Change password'}).click();if((await passwordResult).status()!==200)throw Error('Password reset failed');await page.goto(base+'/admin/users');row=page.getByRole('row').filter({hasText:'browser'+stamp+'@example.test'});await row.getByRole('button',{name:'Delete',exact:true}).click();await row.waitFor({state:'detached'});
 });
 await check('Products UI create, edit and delete',async()=>{
  await page.goto(base+'/admin/products/new');await page.locator('input[name=name]').fill('Browser Product '+stamp);await page.locator('input[name=slug]').fill('browser-product-'+stamp);await page.locator('input[name=affiliate_url]').fill('https://example.com/offer');await page.locator('input[name=featured]').check();await page.getByRole('button',{name:'Save product',exact:true}).click();await page.waitForURL('**/admin/products');
  let row=page.getByRole('row').filter({hasText:'Browser Product '+stamp});await row.getByRole('link',{name:'Edit',exact:true}).click();await page.locator('input[name=name]').fill('Browser Product Updated '+stamp);await page.getByRole('button',{name:'Save product',exact:true}).click();await page.waitForURL('**/admin/products');
  await page.goto(base+'/products/browser-product-'+stamp);await page.getByRole('heading',{name:'Browser Product Updated '+stamp,exact:true}).waitFor();const offer=page.getByRole('link',{name:'View offer ↗'}).first();if(await offer.getAttribute('href')!=='https://example.com/offer')throw Error('Wrong offer');
  await page.goto(base+'/');await page.getByRole('heading',{name:'Explore products',exact:true}).waitFor();
  const homepageProduct=page.getByRole('region',{name:'Affiliate products'}).getByRole('article').filter({hasText:'Browser Product Updated '+stamp});await homepageProduct.waitFor();
  const homeOffer=homepageProduct.getByRole('link',{name:'View offer ↗'});
  if(await homeOffer.getAttribute('href')!=='https://example.com/offer')throw Error('Homepage offer destination is wrong');
  await page.screenshot({path:path.join(output,'desktop-with-product.png'),fullPage:true});
  await homepageProduct.getByRole('link',{name:'Browser Product Updated '+stamp,exact:true}).click();
  await page.getByRole('heading',{name:'Browser Product Updated '+stamp,exact:true}).waitFor();
  await page.goto(base+'/admin/products');row=page.getByRole('row').filter({hasText:'Browser Product Updated '+stamp});await row.getByRole('button',{name:'Delete',exact:true}).click();await row.waitFor({state:'detached'});
 });
 await check('Post UI create with empty relationships, publish, edit, delete',async()=>{
  await page.goto(base+'/admin/posts/new');await page.getByLabel('Title',{exact:true}).fill('Browser Review '+stamp);await page.locator('[contenteditable=true]').fill('A detailed review of a useful product with practical guidance.');await page.locator('select').filter({has:page.locator('option[value=published]')}).selectOption('published');await page.getByRole('button',{name:'Publish Post',exact:true}).click();await page.waitForURL('**/admin/posts');
  const row=page.getByRole('row').filter({hasText:'Browser Review '+stamp});await row.getByRole('link',{name:'Browser Review '+stamp,exact:true}).click();await page.getByLabel('Title',{exact:true}).fill('Updated Browser Review '+stamp);await page.getByRole('button',{name:'Save Changes',exact:true}).click();await page.waitForURL('**/admin/posts');await page.goto(base+'/posts/browser-review-'+stamp);await page.getByRole('heading',{name:'Updated Browser Review '+stamp,exact:true}).waitFor();await page.goto(base+'/admin/posts');await page.getByRole('button',{name:'Actions for Updated Browser Review '+stamp,exact:true}).click();await page.getByRole('menuitem',{name:'Delete',exact:true}).click();await page.getByRole('alertdialog').getByRole('button',{name:'Delete',exact:true}).click();await page.getByRole('link',{name:'Updated Browser Review '+stamp,exact:true}).waitFor({state:'detached'});
 });
 for(const path of ['/','/products','/categories','/tags','/brands','/posts','/search?q=Runtime','/contact'])await check('Public '+path,async()=>{const r=await page.goto(base+path);if(r.status()!==200)throw Error(path+' '+r.status());await page.locator('h1').first().waitFor();});
 await check('Carousel, SEO, GA events, hero and page sizes',async()=>{
  await page.setViewportSize({width:1440,height:1000});
  await page.goto(base+'/');
  const carousel=page.getByRole('region',{name:'Affiliate products'});
  const cards=carousel.getByRole('article');
  if(await cards.count()<8)throw Error('Expected eight real database products in carousel');
  const first=await cards.nth(0).boundingBox(), sixth=await cards.nth(5).boundingBox(), seventh=await cards.nth(6).boundingBox();
  if(!first||!sixth||!seventh||seventh.x<sixth.x+sixth.width)throw Error('Desktop carousel should show six cards');
  const image=cards.first().locator('img');await image.waitFor();if(!await image.evaluate(img=>img.complete&&img.naturalWidth>0))throw Error('Carousel product image did not load');
  const previous=carousel.getByRole('button',{name:'Previous affiliate products'}),next=carousel.getByRole('button',{name:'Next affiliate products'});
  if(!await previous.isDisabled()||await next.isDisabled())throw Error('Initial navigation state is wrong');
  await next.click(); await page.waitForTimeout(400);
  if(await previous.isDisabled())throw Error('Previous did not enable');
  for(let i=0;i<7;i++)if(!await next.isDisabled())await next.click();
  if(!await next.isDisabled())throw Error('Next did not stop at last card');
  for(let i=0;i<8;i++)if(!await previous.isDisabled())await previous.click();
  if(!await previous.isDisabled())throw Error('Previous did not stop at first card');
  await page.waitForFunction(()=>window.__dewdoraEvents?.some(e=>e[0]==='event'&&e[1]==='affiliate_product_impression'));
  const events=await page.evaluate(()=>window.__dewdoraEvents);
  const impression=events.find(e=>e[1]==='affiliate_product_impression');
  if(!impression[2].product_id||impression[2].brand_id!==1||impression[2].brand_name!=='Test Brand')throw Error('Brand impression parameters missing');
  await page.evaluate(()=>document.addEventListener('click',e=>{if(e.target.closest('a[href^="https://example.com/offer/"]'))e.preventDefault()},true));
  await cards.first().getByRole('link',{name:'View offer ↗'}).click();
  const clicks=await page.evaluate(()=>window.__dewdoraEvents.filter(e=>e[1]==='affiliate_click'));
  if(!clicks.length||clicks.at(-1)[2].brand_id!==1||clicks.at(-1)[2].product_id!==8)throw Error('Click analytics parameters missing');
  if(!await page.getByRole('heading',{name:'Test hero banner'}).isVisible())throw Error('Hero banner absent');
  const markup=await page.content();if(markup.indexOf('Affiliate products')>markup.indexOf('Test hero banner'))throw Error('Homepage sections out of order');
  for(const route of ['/products','/products/test-product-8','/posts/test-review']){
    const response=await page.goto(base+route);if(response.status()!==200)throw Error(route+' returned '+response.status());
    for(const selector of ['meta[property="og:title"]','meta[property="og:description"]','meta[property="og:image"]','meta[property="og:url"]','meta[property="og:type"]','meta[name="twitter:card"]','meta[name="twitter:title"]','meta[name="twitter:description"]','meta[name="twitter:image"]','link[rel="canonical"]'])if(!await page.locator(selector).count())throw Error(route+' missing '+selector);
  }
  await page.goto(base+'/admin/hero-banners');await page.getByRole('heading',{name:'Hero banners'}).waitFor();
  await page.getByRole('button',{name:'Add banner'}).click();await page.locator('input[name=heading]').fill('Browser banner '+stamp);await page.locator('input[name=sort_order]').fill('1');await page.getByRole('button',{name:'Save',exact:true}).click();await page.getByRole('heading',{name:'Browser banner '+stamp}).waitFor();await page.getByRole('button',{name:'Delete',exact:true}).last().click();
  await page.goto(base+'/admin/categories');const sizePending=page.waitForResponse(r=>r.url().includes('per_page=all'));await page.getByRole('combobox',{name:'Items per page'}).selectOption('all');const sizeResponse=await sizePending;if(sizeResponse.status()!==200)throw Error('All page-size request failed');
 });
 await check('Admin page sizes, persisted theme and sticky navigation',async()=>{
  for(const route of ['posts','categories','tags','brands','networks','products','users','comments','newsletter','contacts','media']){
    await page.goto(base+'/admin/'+route);
    const selector=page.getByRole('combobox',{name:'Items per page'});
    await selector.waitFor();
    const choices=await selector.locator('option').allTextContents();
    if(choices.join(',')!=='10,20,50,All')throw Error(route+' page sizes missing');
  }
  await page.goto(base+'/admin');
  const toggle=page.getByRole('combobox',{name:'Dashboard theme'});await toggle.selectOption('dark');
  await page.waitForFunction(()=>document.documentElement.classList.contains('dark'));
  await page.reload();await page.getByRole('combobox',{name:'Dashboard theme'}).waitFor();
  if(!await page.evaluate(()=>document.documentElement.classList.contains('dark')))throw Error('Dark mode was not persisted');
  const adminSticky=await page.locator('header').first().evaluate(node=>getComputedStyle(node).position);
  if(adminSticky!=='sticky')throw Error('Admin header is not sticky');
  await page.getByRole('combobox',{name:'Dashboard theme'}).selectOption('light');
  await page.goto(base+'/');const publicSticky=await page.locator('header').first().evaluate(node=>getComputedStyle(node).position);
  if(publicSticky!=='sticky')throw Error('Public navigation is not sticky');
 });
 await check('Media UI upload, list, delete',async()=>{
   await page.goto(base+'/admin/media');await page.getByRole('heading',{name:'Media library'}).waitFor();
   const png=Buffer.from('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVQIHWP4z8DwHwAFgAI/ScL/nwAAAABJRU5ErkJggg==','base64');
   await page.locator('input[type=file]').setInputFiles({name:'test.png',mimeType:'image/png',buffer:png});
   const uploadResponse=page.waitForResponse(r=>r.url().endsWith('/admin/media/upload') && r.request().method()==='POST');await page.getByRole('button',{name:'Upload image'}).click();
   const uploaded=await uploadResponse;if(uploaded.status()!==201)throw Error('Media upload returned '+uploaded.status());const uploadedName=(await uploaded.json()).data.name;
   const card=page.locator('div.overflow-hidden').filter({hasText:uploadedName});await card.waitFor();await card.getByRole('button',{name:'Delete'}).click();await card.waitFor({state:'detached'});
 });
 await check('Settings and profile API screens',async()=>{
   await page.goto(base+'/admin/settings');await page.getByRole('heading',{name:'Site settings'}).waitFor();const field=page.locator('input[name=site_name]');await field.waitFor();const previous=await field.inputValue();await field.fill('Dewdora Browser Test');const saveResult=page.waitForResponse(r=>r.url().endsWith('/admin/settings') && r.request().method()==='PUT');await page.getByRole('button',{name:'Save settings'}).click();if((await saveResult).status()!==200)throw Error('Settings update failed');await field.fill(previous || 'Dewdora');const restoreResult=page.waitForResponse(r=>r.url().endsWith('/admin/settings') && r.request().method()==='PUT');await page.getByRole('button',{name:'Save settings'}).click();if((await restoreResult).status()!==200)throw Error('Settings restore failed');await page.goto(base+'/admin/profile');await page.getByRole('heading',{name:'Profile',exact:true}).waitFor();await page.getByText(email).waitFor();
 });
 await page.goto(base+'/');await page.screenshot({path:path.join(output, 'desktop.png'),fullPage:true});
 await check('Mobile admin navigation and no horizontal overflow',async()=>{await page.setViewportSize({width:390,height:844});await page.goto(base+'/admin/users');await page.getByRole('heading',{name:'Users',exact:true}).waitFor();await page.getByRole('row').filter({hasText:email}).waitFor();if(await page.evaluate(()=>document.documentElement.scrollWidth>innerWidth))throw Error('Horizontal overflow');await page.getByRole('button',{name:'Open admin navigation'}).click();await page.getByRole('dialog').getByRole('link',{name:'Categories',exact:true}).waitFor();await page.screenshot({path:path.join(output, 'mobile-admin.png'),fullPage:true});});
 await check('Mobile public homepage',async()=>{await page.goto(base+'/');await page.locator('h1').waitFor();if(await page.evaluate(()=>document.documentElement.scrollWidth>innerWidth))throw Error('Horizontal overflow');await page.screenshot({path:path.join(output,'mobile-home.png'),fullPage:true});});
 if(errors.length)throw Error('Browser errors: '+errors.join('; '));
 if(requests.some(r=>r.status>=400))throw Error('Failed API requests: '+JSON.stringify(requests.filter(r=>r.status>=400)));
 }catch(e){results.push({name:'FAILURE',error:e.stack});console.error(e);await page.screenshot({path:path.join(output, 'failure.png'),fullPage:true});process.exitCode=1;}
 finally{fs.writeFileSync(path.join(output, 'browser-results.json'),JSON.stringify({results,errors,requests},null,2));await browser.close();}
})();
