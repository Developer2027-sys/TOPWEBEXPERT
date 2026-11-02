# Shopify Keyword Expansion — Report (3 September 2026)

Source: `Shopify_Agency_Keyword_Research.xlsx` (5 tabs, 112 keywords).
Result on the database copy (`u224219320_topwebmain`, imported and migrated locally; run the same migration on Hostinger — see README-HOSTING.md section 5):

| | Before | Added | After |
|---|---|---|---|
| Services (service slugs) | 29 | **34** | 63 |
| Locations | 1,460 cities | **12** country-level | 1,472 |
| City pages (`city_pages` rows, one URL each) | 42,340 | **50,396** | 92,736 |
| Guide templates (`service_blogs`) | 145 (144 active) | **9** | 154 (153 active) |
| Guide page URLs (template × cities of its service) | 210,240 | **14,976** | 225,216 |
| **Indexable URLs in sitemap** (+566 blogs, +20 static) | 253,166 | **65,372** | **318,538** |

How the 50,396 city pages break down: 34 new services × 1,472 locations = 50,048, plus 29 existing services × 12 new country locations = 348.

Every page has unique per-service copy (intro, deliverables, process, reasons, FAQ, meta title/description/keywords) with the city and country filled in — see `app/includes/service-templates.php`. Guides are full articles in `app/includes/keyword-migration.php`.

---

## Tab 1 — Keyword Matrix (34 head keywords)

Transactional/commercial keywords became **services**, each with a page in all 1,472 locations (`/{service-slug}-{city-slug}`). Synonyms of the same buying intent share one page and are targeted in that page's title, H2s and meta keywords (listed under "also targets").

| # | Keyword | Page / service slug | Also targets |
|---|---|---|---|
| 1 | shopify developer | `shopify-developer` (new) | hire shopify developer, shopify freelancer, shopify developer near me |
| 2 | shopify development company | `shopify-development-company` (new) | shopify development agency, best/top shopify development company |
| 3 | shopify development agency | → `shopify-development-company` | |
| 4 | shopify agency | `shopify-agency` (new) | shopify design agency, shopify partner agency |
| 5 | hire shopify developer | → `shopify-developer` | |
| 6 | shopify expert | `shopify-expert` (new) | shopify consultant, shopify partner |
| 7 | shopify partner | → `shopify-expert` | |
| 8 | shopify consultant | → `shopify-expert` | |
| 9 | shopify freelancer | → `shopify-developer` | |
| 10 | shopify web design | `shopify-web-design` (new) | shopify website design, shopify store design, shopify designer |
| 11 | shopify website design | → `shopify-web-design` | |
| 12 | shopify store design | → `shopify-web-design` | |
| 13 | shopify designer | → `shopify-web-design` | |
| 14 | shopify redesign | `shopify-redesign` (new) | shopify website/store/theme redesign |
| 15 | shopify website development | → existing `shopify-development` | |
| 16 | custom shopify development | → existing `shopify-development` | |
| 17 | shopify theme development | `shopify-theme-development` (new) | theme customization (service), custom shopify theme |
| 18 | shopify theme customization | → `shopify-theme-development` | |
| 19 | shopify app development | `shopify-app-development` (new) | custom app, private app, subscription app development |
| 20 | shopify store setup | `shopify-store-setup` (new) | shopify setup expert, launch a shopify store |
| 21 | shopify plus agency | `shopify-plus-agency` (new) | shopify plus developer/development/partner |
| 22 | shopify plus developer | → `shopify-plus-agency` | |
| 23 | shopify b2b development | `shopify-b2b-development` (new) | shopify wholesale b2b setup, wholesale store |
| 24 | shopify headless development | `shopify-headless-development` (new) | headless shopify, hydrogen, storefront api |
| 25 | shopify migration | `shopify-migration` (new) | migrate to shopify, shopify migration service |
| 26 | woocommerce to shopify migration | `woocommerce-to-shopify-migration` (new) | |
| 27 | magento to shopify migration | `magento-to-shopify-migration` (new) | magento 2 / adobe commerce to shopify |
| 28 | shopify seo services | `shopify-seo-services` (new) | shopify seo agency/expert/company |
| 29 | shopify conversion rate optimization | `shopify-conversion-rate-optimization` (new) | shopify cro agency/services |
| 30 | shopify speed optimization | `shopify-speed-optimization` (new) | shopify page speed, slow loading fix |
| 31 | shopify maintenance services | `shopify-maintenance-services` (new) | shopify support services, store maintenance |
| 32 | shopify checkout customization | `shopify-checkout-customization` (new) | checkout extensibility, checkout upsell |
| 33 | ecommerce development company | → existing `ecommerce-website-development-company` | |
| 34 | ecommerce website development company | → existing `ecommerce-website-development-company` | |

## Tab 2 — Long Tail (34 keywords)

| # | Keyword | Page |
|---|---|---|
| 1 | shopify developer near me | Guide `shopify-developer-near-me-guide` (× 1,472 cities) + `shopify-developer` pages |
| 2 | best shopify development company | Guide `best-shopify-development-company-checklist` (× 1,472) |
| 3 | top shopify development companies | → same guide |
| 4 | shopify development company in india | `/shopify-development-company-india` (country page) + 514 Indian city pages |
| 5 | hire shopify developer india | `/shopify-developer-india` (title "Hire Shopify Developer" in meta) |
| 6 | shopify developer hourly rate | Guide `shopify-developer-hourly-rate-and-cost` (× 1,472) |
| 7 | shopify developer cost | → same guide |
| 8 | how much does it cost to build a shopify store | Guide `how-much-does-it-cost-to-build-a-shopify-store` (× 1,472) |
| 9 | shopify store development cost | → same guide |
| 10 | outsource shopify development | `outsource-shopify-development` (new service) |
| 11 | offshore shopify developer | → `outsource-shopify-development` |
| 12 | white label shopify development | `white-label-shopify-development` (new service) |
| 13 | white label shopify agency | → `white-label-shopify-development` |
| 14 | dedicated shopify developer | `dedicated-shopify-developer` (new service) |
| 15 | shopify developer for hire remote | → `outsource-shopify-development` |
| 16 | shopify liquid developer | `shopify-liquid-developer` (new service) |
| 17 | shopify api integration | `shopify-api-integration` (new service) |
| 18 | shopify erp integration | `shopify-erp-integration` (new service; NetSuite/SAP/Dynamics named) |
| 19 | shopify custom app development | → `shopify-app-development` |
| 20 | shopify subscription app development | Guide `shopify-subscription-app-development-guide` (× 1,472) + `shopify-app-development` |
| 21 | shopify multi currency setup | Guide `shopify-multi-currency-setup-guide` (× 1,472) |
| 22 | shopify multi store setup | `shopify-multi-store-setup` (new service) |
| 23 | shopify store audit | `shopify-store-audit` (new service, free-audit lead magnet) |
| 24 | shopify theme customization service | → `shopify-theme-development` |
| 25 | shopify one product store design | `shopify-one-product-store-design` (new service) |
| 26 | shopify dropshipping store design | `shopify-dropshipping-store-design` (new service) |
| 27 | shopify fashion store design | `shopify-fashion-store-design` (new service) |
| 28 | shopify wholesale b2b setup | → `shopify-b2b-development` |
| 29 | shopify to shopify plus migration | `shopify-to-shopify-plus-migration` (new service) |
| 30 | wordpress to shopify migration service | `wordpress-to-shopify-migration` (new service) |
| 31 | bigcommerce to shopify migration | `bigcommerce-to-shopify-migration` (new service) |
| 32 | shopify site not converting | Guide `shopify-site-not-converting-fixes` (× 1,472) |
| 33 | shopify store slow loading fix | Guide `shopify-store-slow-loading-fix` (× 1,472) |
| 34 | shopify vs woocommerce for business | Guide `shopify-vs-woocommerce-for-business` (× 1,472) |

## Tab 3 — Local & City (26 keywords)

All covered by service × location pages. City keywords map to existing cities; country keywords map to the 12 new country-level locations.

| Keyword | URL |
|---|---|
| shopify agency london / manchester / new york / austin / toronto / sydney / melbourne / abu dhabi / amsterdam / dublin | `/shopify-agency-london`, `/shopify-agency-manchester`, `/shopify-agency-new-york`, `/shopify-agency-austin`, `/shopify-agency-toronto`, `/shopify-agency-sydney`, `/shopify-agency-melbourne`, `/shopify-agency-abu-dhabi`, `/shopify-agency-amsterdam`, `/shopify-agency-dublin` |
| shopify developer london / los angeles / chicago / vancouver / dubai / singapore / auckland | `/shopify-developer-london`, `/shopify-developer-los-angeles`, `/shopify-developer-chicago`, `/shopify-developer-vancouver`, `/shopify-developer-dubai`, `/shopify-developer-singapore`, `/shopify-developer-auckland` |
| shopify development company uk / usa / dubai / delhi | `/shopify-development-company-uk`, `/shopify-development-company-usa`, `/shopify-development-company-dubai`, `/shopify-development-company-delhi` |
| shopify developer australia / saudi arabia / south africa / india | `/shopify-developer-australia`, `/shopify-developer-saudi-arabia`, `/shopify-developer-south-africa`, `/shopify-developer-india` |
| shopify agentur berlin | **Not built** — German-language keyword (see Tab 4) |

## Tab 4 — Non-English (18 keywords) — NOT built

`shopify agentur`, `shopify entwickler`, `agence shopify`, `agencia shopify`, `shopify bureau`, `shopify webshop laten maken`, `agenzia shopify`, … require native-language pages and a language structure (`/de/`, `/fr/`, `/nl/`) the site does not have. The sheet itself warns that machine-translated copy is obvious to those buyers. Recommended as a separate project with native copywriters; the English pages for Germany, Netherlands and the German/French/Dutch cities already exist (e.g. `/shopify-agency-berlin`, `/shopify-agency-amsterdam`).

## Tab 5 — Priority Plan

Every "page to build" in the plan now exists: offshore/white-label pages, pricing guide, audit page, four migration pages, technical hub pages (Liquid, API, custom app, ERP), underserved-geo city pages, Plus practice page, vertical pages (fashion, one-product, wholesale) and top-of-funnel guides. Only the Dutch/German native-language subfolders are outstanding.

---

## Caution

The site now carries about 318,000 templated URLs. Google's scaled-content and doorway-page policies target exactly this pattern. The new pages have genuinely different copy per service, which helps, but the risk is the site as a whole. Watch Search Console for a manual action or a sudden indexing drop over the next 8 weeks, and prioritise real case studies, pricing tables and reviews on the highest-value pages (developer, agency, development company, migrations) so they are not pure templates.
