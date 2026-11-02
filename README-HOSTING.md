# TopWebExpert — Hosting & Visibility Guide

Is file mein: server par host karne ke steps + live hone ke baad visibility (Google ranking) improve karne ki poori checklist.

---

## 1. Server Par Host Kaise Karein

### Folder Structure (naya — saaf/organized)
Web root (public_html) par ab sirf zaroori files hain, baaki sab `app/` folder ke andar — isse multiple subdomain folders ke beech confusion nahi hoga. **URLs bilkul same rehte hain.**

```
public_html/            <- (ya subdomain ka folder)
├── index.php           <- front controller (har request yahan aati hai)
├── sitemap.php         <- dynamic sitemap
├── robots.txt          <- SEO
├── llms.txt            <- AI/LLM visibility
├── .htaccess           <- routing + cache + security
├── assets/             <- CSS, images, JS (browser inhe seedhe load karta hai)
├── libs/               <- swiper, quill, qrcode, phpmailer
└── app/                <- SAARA application code (web se blocked, .htaccess se protected)
    ├── router.php      home.php  about-us.php  blog.php  ... (saare page files)
    ├── includes/       (db, header, footer, seo, cache, security...)
    ├── admin/          (poora admin panel)
    ├── content/        (page content JSON)
    ├── cache/          (auto-generated sitemap cache only — pages are never cached as files)
    └── scripts/        (migrate*.php — sirf fresh setup ke liye)
```

### Files Upload
- Poora `php-site` folder ka **content** hosting ke `public_html` (ya subdomain folder) mein upload karein — yani `index.php`, `sitemap.php`, `robots.txt`, `llms.txt`, `.htaccess`, aur `assets/`, `libs/`, `app/` folders.
- **Upload NA karein (sirf local build ke liye hain):** `node_modules/`, `src/`, `package.json`, `package-lock.json`, `*.backup` files.
- **`app/scripts/` folder:** fresh database setup ke liye ek baar chalao (`php app/scripts/migrate.php` … `migrate4.php`), phir **poora `scripts/` folder delete kar dein** — security ke liye zaroori.
- `app/cache/sitemaps/` folder khaali upload karein (permissions `775`) — sirf sitemap XML parts yahan bante hain. **Pages kabhi file mein cache nahi hote** (September 2026 update — pehle 2 lakh+ HTML files ban gayi thin jo hosting ka inode/disk quota kha rahi thin).

### Database
1. Hosting panel se MySQL database banayein.
2. Local database export karein: `C:\xampp\mysql\bin\mysqldump.exe -u root u224219320_react_topweb > backup.sql`
3. Server par import karein (phpMyAdmin ya CLI se).
4. `app/includes/db.php` mein naye server ke DB credentials daalein.

### Local Development (XAMPP)
- Tailwind CSS build: `npx tailwindcss -i src/input.css -o assets/css/style.css --minify` (web root se chalao)
- PHP dev server: `php -S 127.0.0.1:8090 -t . index.php` (web root se — `index.php` router hai)
- Production-jaisa Apache: XAMPP Apache (`.htaccess` support ke saath) — recommended for testing security rules.

### Zaroori Settings (live hote hi)
1. **`app/includes/db.php`** mein hosting ke naye MySQL credentials daalein.
2. **Admin → Site Settings → Site URL** ko apne real domain par set karein (e.g. `https://topwebexpert.com`) — canonical URLs, sitemap aur schema isi se bante hain.
3. `robots.txt` mein Sitemap line ka URL apne domain par update karein.
4. `llms.txt` mein links relative hain — koi change nahi chahiye.
5. **Admin password change karein** aur 2FA setup karein (pehli login par QR milega).
6. **`app/enquiry-submit.php`** mein SMTP password update karein (purana password public repo mein leak ho chuka hai — Hostinger par pehle naya password banayein).
6. SSL (HTTPS) enable karein — Hostinger par free Let's Encrypt milta hai. HTTPS ke bina ranking aur trust dono girta hai.
7. PHP version 8.0+ select karein aur **OPcache enable** karein (hosting panel → PHP configuration).

### Apache (.htaccess)
- `.htaccess` file included hai — pretty URLs, caching, compression aur security rules automatic kaam karenge (Apache/LiteSpeed hosting par).

---

## 2. Live Hone Ke Baad — Visibility Checklist

### Pehle Hafte Mein (zaroori)
1. **Google Search Console** (search.google.com/search-console)
   - Domain add karke verify karein.
   - Sitemap submit karein: `https://aapkadomain.com/sitemap.xml`
   - "URL Inspection" se homepage ka index request karein.
2. **Bing Webmaster Tools** (bing.com/webmasters) — same process. Bing se ChatGPT search results bhi aate hain.
3. **Google Business Profile** (business.google.com)
   - Business register karein — local searches ("digital marketing agency near me") ke liye sabse powerful free tool.
   - Address, phone, timing, photos, services sab complete karein.
4. **Google Analytics 4** install karein — traffic samajhne ke liye (Search Console se link karein).

### Pehle Mahine Mein
5. **Reviews collect karein** — har khush client se Google review maangein. Reviews = local ranking + trust.
6. **Social profiles active karein** — Facebook, Instagram, LinkedIn par website link daalein (footer ke links already hain).
7. **Business directories** mein list karein: JustDial, Sulekha, IndiaMART, Clutch, GoodFirms — har jagah SAME naam, address, phone (NAP consistency).
8. **Search Console monitor karein** — "Pages" section mein dekhein kitne pages index ho rahe hain. 40,000+ pages hain, indexing mein 2-3 mahine lag sakte hain — ye normal hai.

### Ongoing (har mahine)
9. **Blogs chalte rahenge** — scheduled posts roz publish ho rahe hain 2027 tak. Beech-beech mein 1-2 manual blog (real client stories/case studies) likhein — original content sabse strong signal hai.
10. **Backlinks banayein** — dusri websites se apni site ke links: guest posts, business associations, suppliers/partners ki websites, press releases.
11. **Page speed check karein** — pagespeed.web.dev par test karein (WebP, gzip, caching already lagi hai — score achha aayega).
12. **Search Console ke queries dekhein** — jo keywords impressions de rahe hain but clicks nahi, unke pages ke meta titles Admin → SEO Manager se improve karein.
13. **City pages ko dheere-dheere enrich karein** — jo cities enquiries de rahi hain, unke pages mein Admin se real content/photos add karein.

### AI / LLM Visibility (ChatGPT, Claude, etc. mein dikhne ke liye)
- `llms.txt` file lagi hui hai — AI assistants ko site ki structured summary milti hai.
- Bing indexing (step 2) important hai — kai AI search tools Bing se data lete hain.
- Note: robots.txt + security layer AI **training** crawlers ko block karti hai (content copy hone se bachav), lekin search-driven AI tools ke liye llms.txt aur schema data available hai. Agar kabhi AI training crawlers ko allow karna ho to `robots.txt` aur `includes/security.php` se unke naam hata dein.

### Kya NA karein
- Backlinks kharidna, fake reviews, keyword stuffing — Google penalty deta hai jo mahino tak nahi jaati.
- Sitemap baar-baar resubmit karna — ek baar kaafi hai.
- 2-3 hafte mein results ki umeed — SEO 3-6 mahine ka game hai; scheduled blogs + 40k city pages compound hote jayenge.

---

## 3. System Ki Khaas Baatein (reference)

| Cheez | Kahan |
|---|---|
| Admin panel | `/admin` (2FA protected) |
| Page cache | **Nahi hai.** Har page database se live render hota hai. `app/cache/sitemaps/` mein sirf sitemap parts (≈30 XML files) rehte hain; Admin save par ya Dashboard "Clear Page Cache" se clear hote hain |
| Sitemap | `/sitemap.xml` (index) → pages/blogs/cities parts, 6 ghante cache |
| Bot protection | `includes/security.php` — **koi automatic IP block / rate limiter nahi** (September 2026 se). Sirf Admin → Traffic & IPs se manually block kiye IP rukte hain. Scraper/AI user-agents aur hack-probe paths ko sirf us request par 403 milta hai; search engines (Googlebot/Bingbot) kabhi block nahi hote, purane WordPress URLs par unhe 410 milta hai |
| Form spam protection | 3 submissions/minute per IP |
| Scheduled blogs | Roz 1 post automatic 31 Dec 2027 tak; Admin → Blogs mein "Scheduled" badge |
| SEO manage | Admin → SEO Manager (static pages), blog/city editors (unke apne fields) |

---

## 4. September 2026 Update — Deploy Steps

Kya badla: page file-cache hataya gaya (`app/cache/pages/` ab nahi banta), automatic IP blocking/rate limiter hataya gaya (sirf manual block), purane WordPress URLs ke liye 301/410 rules, database down hone par 503 (pehle 200 "Database connection failed" aata tha jo Google ko soft-404 lagta tha), `robots.txt` ka Sitemap URL theek kiya.

1. Ye files/folders server par upload (overwrite) karein: `app/router.php`, `app/includes/cache.php`, `app/includes/security.php`, `app/includes/db.php`, `app/admin/index.php`, `app/admin/includes/admin-header.php`, `app/scripts/purge-cache.php`, `sitemap.php`, `robots.txt`.
2. Server par purani cache files (≈2.2 lakh HTML, ≈9 GB) hatayein — koi ek tarika:
   - **SSH / Hostinger cron (fastest):** `php public_html/app/scripts/purge-cache.php`
   - **hPanel File Manager:** `public_html/app/cache/pages` folder delete kar dein.
   - **Admin Dashboard:** "Clear Page Cache" button — 20 second ke batches mein delete karta hai, complete hone tak dobara click karein.
3. Server par `app/app/` aur `app/assets/` (purani duplicate copies) delete kar dein — code unhe use nahi karta.
4. Google Search Console → Sitemaps: `https://topwebexpert.com/sitemap.xml` ek baar dobara submit karein. Purane WordPress URLs (jo "Not found" dikha rahe hain) ab 301 ya 410 dete hain — Google unhe agle crawl mein khud hata dega.

---

## 5. Shopify Keyword Expansion (September 2026)

`Shopify_Agency_Keyword_Research.xlsx` ke keywords se 34 naye Shopify services, 9 city-guide articles aur 12 country-level locations (USA, UK, Canada, Australia, India, UAE, Germany, Netherlands, Ireland, New Zealand, South Africa, Saudi Arabia) add hue hain. Har service ka apna alag page copy `app/includes/service-templates.php` mein hai. Poori keyword → page mapping `KEYWORD-EXPANSION-REPORT.md` mein hai.

Deploy:
1. Upload karein: `app/includes/services-config.php`, `app/includes/service-templates.php`, `app/includes/keyword-migration.php`, `app/includes/functions.php`, `app/scripts/migrate-shopify-keywords.php`, `app/admin/city-pages.php`, `app/city-page.php`, `app/guide-page.php`.
2. Database mein pages banayein — koi ek tarika:
   - **SSH:** `php public_html/app/scripts/migrate-shopify-keywords.php` (5–10 minute; `--check` se pehle preview dekh sakte hain)
   - **Admin panel:** Admin → City Pages → "Generate" → **Run Shopify keyword expansion**. Ye 25-second batches mein khud chalta rehta hai (tab khula rakhein) jab tak "Complete" na aa jaye.
   Dono resumable hain — beech mein ruk jaye to dobara chalane par wahi se aage badhta hai, duplicate kabhi nahi banta.
3. Khatam hone par sitemap khud update ho jata hai (`/sitemap.xml` mein naye `sitemap-cities-N.xml` / `sitemap-guides-N.xml` parts). Search Console mein sitemap dobara submit kar dein.

---

## 6. Post-launch Audit Fixes (7 September 2026)

Kya badla: `www.topwebexpert.com` → `topwebexpert.com` 301 (duplicate host band), purane WordPress sitemap URLs (`/sitemap_index.xml`, `/post-sitemap.xml`, `/wp-sitemap*.xml`) → `/sitemap.xml` 301, naya hub page **`/shopify-services`** (35 Shopify services × 12 country pages ke links), Our Services / Home grid aur footer mein hub ka link, har Shopify city page ke sidebar mein "More Shopify services in {city}" block (34 sibling links).

Upload karein: `.htaccess`, `sitemap.php`, `app/router.php`, `app/city-page.php`, `app/shopify-services.php` (naya), `app/includes/services-config.php`, `app/includes/footer.php`, `app/includes/icons.php`, `app/content/our-services.json`.

Baad mein: Admin → Dashboard → Clear Page Cache (sitemap refresh), phir Search Console mein `https://topwebexpert.com/shopify-services` par "Request Indexing" karein, aur purane sitemaps (sitemap_index.xml etc.) ko Sitemaps report se remove kar dein.

---

## 7. Official Address — Gurugram (23 September 2026)

Naya official address: **Tower B1, Spaze I-Tech Park, 32B & 33A, Badshahpur Sohna Rd, Sector 49, Gurugram, Haryana 122018**. Site par jahan bhi "Noida" head office ke roop mein likha tha (home title/description/intro, About, SEO page, footer text, llms.txt) ab "Gurugram" hai. Noida ke city pages (`/…-noida`) waise hi hain — wo service-area pages hain.

Schema: Organization + naya `ProfessionalService` block, dono mein city / state / PIN aur saare social profiles (`sameAs`). Admin → Settings mein naye fields: Head Office City / State / PIN, Google Business Profile URL, X, YouTube, Medium.

Upload karein: `llms.txt`, `app/home.php`, `app/includes/header.php`, `app/includes/seo.php`, `app/includes/settings.php`, `app/includes/service-template.php`, `app/admin/settings.php`, `app/content/home.json`, `about-us.json`, `seo.json`, `contact-us.json`, `layout.json`, `our-services.json`, `app/scripts/migrate-official-address.php` (naya).

Database update — koi ek tarika:
- **SSH:** `php public_html/app/scripts/migrate-official-address.php --check` (preview), phir bina `--check` ke. Ye naya Gurugram head office primary banata hai, purane Noida aur Sapphire 83 branches band karta hai (Admin → Branches se wapas on ho sakte hain), Settings bharta hai aur SEO Manager ke 9 titles/descriptions mein Noida → Gurugram karta hai.
- **Admin panel (bina SSH):** Branches → naya branch add karke "Make primary", Noida / Sapphire 83 off; Settings → Address, City = Gurugram, State = Haryana, PIN = 122018, X URL; SEO Manager → `/`, `/about-us`, `/web-development-company`, `/search-engine-optimization` wagairah mein Noida → Gurugram.

Baad mein (site ke bahar, zaroori): Google Business Profile ka address + description ("Oracle Web Design" hatayein), Facebook (ek hi page rakhein), Instagram, X, LinkedIn, IndiaMART (category + city) — sab jagah bilkul yahi naam, address aur phone.

---

## 8. Tier Plan — Google mein kaunse pages dikhein (23 September 2026)

Kyun: 21 August 2026 ko Google ne site ke ~318k template pages (service × city + guide × city) ko doorway / scaled content maan kar dabaa diya — pages indexed hain par search mein nahi dikhte. Ab sirf achhe pages Google ko dikhaye jaate hain; **koi page delete nahi hota**, sab visitors ke liye live rehte hain.

| Tier | Kaunse pages | Google |
|---|---|---|
| 1 | 12 core cities (Gurgaon, Delhi, Noida, Faridabad, Manesar, Mumbai, Bangalore, Pune, Hyderabad, Karachi, Lahore, Melbourne) × saari services = 756 | index |
| 2 | Search Console mein proven pages (koi click, ya 20+ impressions position 20 ke andar) = 643 | index |
| 3 | Baaki ~91k city pages + saare ~225k guide × city pages | `noindex, follow`, sitemap se bahar |

Upload karein: `sitemap.php`, `app/city-page.php`, `app/guide-page.php`, `app/includes/seo.php`, `app/admin/city-page-edit.php`, `app/scripts/migrate-tiers.php`, `app/scripts/import-page-rewrites.php`, `app/scripts/data/` (folder).

Deploy order (zaroori — pehle column, phir baaki):
1. `php public_html/app/scripts/migrate-tiers.php --check` → phir bina `--check` (tier column banata hai aur Tier 1/2 set karta hai). Bina SSH: phpMyAdmin → Import → `db-export/07b-tiers.sql` (same result).
2. Rewritten pages (20 Gurugram pilot pages): `php public_html/app/scripts/import-page-rewrites.php` (files `app/scripts/data/rewrites/*.json`). Bina SSH: phpMyAdmin → Import → `db-export/08-gurugram-rewrites.sql` — sirf step 1 ke baad.
3. Admin → Dashboard → Clear Page Cache (sitemap cache). `/sitemap.xml` mein ab sirf pages, blogs aur ~1,400 city pages; `sitemap-guides-N.xml` ab 404.
4. Search Console → Sitemaps: `/sitemap.xml` dobara submit karein. Rewritten Gurugram pages par URL Inspection → Request Indexing.

Aage: naya Search Console Pages export `app/scripts/data/gsc-pages-YYYY-MM-DD.csv` naam se daal kar `migrate-tiers.php` dobara chalayein — naye proven pages Tier 2 ho jaate hain (script sirf upar le jaati hai, admin ka set kiya tier kabhi neeche nahi karti). Kisi ek page ka tier: Admin → City Pages → Edit → "Google visibility (tier)". Tier 3 page ko Tier 1/2 tabhi karein jab usme asli local content ho.
