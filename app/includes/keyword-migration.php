<?php
// Shopify keyword expansion (September 2026).
//
// Turns the "Shopify Agency Keyword Research" pack into site pages:
//   1. 12 country-level locations (for "shopify developer australia"-style keywords)
//   2. 9 city-guide templates (informational long-tail keywords, one article per city)
//   3. City pages for every new Shopify service × every location, and every service
//      × every new location (transactional keywords)
//
// keyword_migration_run() is resumable: every insert is INSERT IGNORE against a
// unique key, and it stops after $budgetSeconds so it can be driven either from the
// CLI script (app/scripts/migrate-shopify-keywords.php) or from the admin panel
// (City Pages > "Run Shopify keyword expansion"), clicked until it reports complete.
require_once __DIR__ . '/services-config.php';

// Country-level "locations": slug => [country, city label, lat, lng]
function keyword_migration_locations() {
    return [
        'usa' => ['United States', 'USA', 38.9072, -77.0369],
        'uk' => ['United Kingdom', 'UK', 51.5074, -0.1278],
        'canada' => ['Canada', 'Canada', 45.4215, -75.6972],
        'australia' => ['Australia', 'Australia', -35.2809, 149.1300],
        'india' => ['India', 'India', 28.6139, 77.2090],
        'uae' => ['United Arab Emirates', 'UAE', 24.4539, 54.3773],
        'germany' => ['Germany', 'Germany', 52.5200, 13.4050],
        'netherlands' => ['Netherlands', 'Netherlands', 52.3676, 4.9041],
        'ireland' => ['Ireland', 'Ireland', 53.3498, -6.2603],
        'new-zealand' => ['New Zealand', 'New Zealand', -41.2865, 174.7762],
        'south-africa' => ['South Africa', 'South Africa', -25.7479, 28.2293],
        'saudi-arabia' => ['Saudi Arabia', 'Saudi Arabia', 24.7136, 46.6753],
    ];
}

// Service slugs added by this expansion (must exist in services_list()).
function keyword_migration_services() {
    return array_keys(service_templates());
}

// Guide templates: rendered per city at /{slug}-{city-slug}. Placeholders {city} {country}.
function keyword_migration_guides() {
    $g = [];

    $g[] = ['service_slug' => 'shopify-developer', 'slug' => 'shopify-developer-hourly-rate-and-cost', 'sort_order' => 1,
        'title' => 'Shopify Developer Hourly Rate and Cost in {city}: 2026 Price Guide',
        'meta_title' => 'Shopify Developer Hourly Rate & Cost in {city} (2026)',
        'meta_description' => 'What a Shopify developer costs in {city}: hourly rates by experience level, fixed-price ranges for common jobs, and how to budget a store build in {country}.',
        'meta_keywords' => 'shopify developer hourly rate {city}, shopify developer cost {city}, shopify development cost {country}, hire shopify developer price {city}',
        'content' => <<<'HTML'
<p>If you are budgeting for Shopify work in {city}, the first question is always the same: what does a Shopify developer actually cost? The honest answer is "it depends on who you hire and how you hire them", so this guide breaks the numbers down the way agencies quote them in {country} - by hour, by job and by engagement model.</p>
<h2>Shopify developer hourly rates in {city}</h2>
<p>Hourly rates vary more by <strong>experience and location</strong> than by anything else. As a working guide for {city} businesses:</p>
<ul class="check-list">
<li><strong>Freelance beginner (under 2 years)</strong> - lowest rates, best for simple theme edits, highest risk of rework</li>
<li><strong>Experienced freelancer (3+ years, Liquid and JavaScript)</strong> - mid-range rates, good for well-defined tasks</li>
<li><strong>Local {city} agency developer</strong> - premium rates that include project management, QA and overhead</li>
<li><strong>Offshore agency team (India-based, serving {country})</strong> - senior developers plus process at roughly a third to a half of local agency rates</li>
</ul>
<p>The trap is comparing hourly rates alone. A senior developer at a higher rate frequently finishes a job in a third of the hours a beginner needs, and does not leave technical debt behind.</p>
<h2>Fixed prices for common Shopify jobs</h2>
<p>Most {city} merchants do not buy hours; they buy outcomes. Typical fixed-price categories:</p>
<ol class="steps">
<li><strong>Small fixes and theme tweaks</strong> - a section change, a bug, a new banner: priced as a small fixed job</li>
<li><strong>Custom sections and features</strong> - product configurators, bundle builders, custom filters: quoted per feature</li>
<li><strong>Theme customisation package</strong> - a premium theme adapted to your brand with several custom sections</li>
<li><strong>Full custom store build</strong> - design, theme development, setup and launch for a {country} store</li>
<li><strong>Shopify Plus builds and migrations</strong> - the top of the range, quoted after a scoping phase</li>
</ol>
<h2>What drives the cost up or down</h2>
<ul class="check-list">
<li><strong>Design source</strong> - a purchased theme costs far less to implement than a bespoke design</li>
<li><strong>Catalogue complexity</strong> - variants, bundles, subscriptions and B2B pricing add development time</li>
<li><strong>Integrations</strong> - every ERP, warehouse or marketplace connection is its own mini-project</li>
<li><strong>Content readiness</strong> - missing photos and copy delay projects and add cost</li>
<li><strong>Revisions</strong> - clear sign-off points keep {city} projects on budget</li>
</ol>
<h2>Retainers: the model most {city} stores end up with</h2>
<p>Once a store is live, a monthly retainer of 10 to 40 developer hours usually costs less than ad-hoc jobs and guarantees availability during campaigns and sales periods in {country}. It also means the same developer keeps working on your store, which compounds in speed and quality.</p>
<h2>How to get an accurate Shopify quote in {city}</h2>
<ol class="steps">
<li>Write a one-page brief: what the store must do, examples you like, deadline</li>
<li>Ask three providers for a fixed quote against the same brief</li>
<li>Ask who exactly will do the work and to see two live stores they built</li>
<li>Check what is excluded - apps, content, revisions, post-launch support</li>
<li>Prefer written scope over the lowest number</li>
</ol>
<p>TopWebExpert quotes every {city} project as a fixed price after a short scoping call, with hourly retainers for ongoing work. If you want a number for your specific store, <a href="/contact-us">send us the brief</a> and you will have it within a business day.</p>
HTML
    ];

    $g[] = ['service_slug' => 'shopify-store-setup', 'slug' => 'how-much-does-it-cost-to-build-a-shopify-store', 'sort_order' => 1,
        'title' => 'How Much Does It Cost to Build a Shopify Store in {city}?',
        'meta_title' => 'Cost to Build a Shopify Store in {city} - Full Breakdown',
        'meta_description' => 'The real cost of building a Shopify store in {city}: Shopify plans, theme, design, development, apps, content and launch - with budget ranges for {country} businesses.',
        'meta_keywords' => 'how much does it cost to build a shopify store {city}, shopify store development cost {city}, shopify website cost {country}, shopify store price {city}',
        'content' => <<<'HTML'
<p>"How much does it cost to build a Shopify store?" is the most searched Shopify question in {country}, and most answers are either a single misleading number or a sales pitch. This guide lists every cost line a {city} business will actually meet, so you can build a realistic budget before talking to anyone.</p>
<h2>1. Shopify subscription</h2>
<p>The platform fee is the easy part: Basic, Shopify and Advanced plans cover almost every {city} merchant, while Shopify Plus is for high-volume or B2B brands. Add payment processing fees, which depend on your {country} payment provider and plan.</p>
<h2>2. Theme: purchased or custom</h2>
<ul class="check-list">
<li><strong>Free theme (Dawn and family)</strong> - no cost, good foundation, needs customisation to look distinctive</li>
<li><strong>Premium theme</strong> - a one-off purchase from the Theme Store, then customisation</li>
<li><strong>Custom theme</strong> - designed and coded for your brand; the largest single cost but the biggest conversion lever</li>
</ul>
<h2>3. Design and development</h2>
<p>This is where budgets diverge. A {city} store built on a premium theme with a handful of custom sections sits at the lower end; a bespoke design with custom features, integrations and Shopify Plus checkout sits at the top. The main drivers are page count, custom features and integrations - not product count.</p>
<h2>4. Product data and content</h2>
<p>Photography, copywriting, size guides and translations are often forgotten in {country} budgets and are frequently the reason launches slip. Budget for them explicitly, whether you produce them in-house or outsource.</p>
<h2>5. Apps</h2>
<p>Reviews, email, subscriptions, bundles, search and loyalty are usually apps with monthly fees. A typical {city} store runs five to ten paid apps; part of a good build is choosing fewer, better ones - or replacing apps with theme code.</p>
<h2>6. Launch and post-launch</h2>
<ul class="check-list">
<li><strong>Setup and QA</strong> - payments, taxes, shipping, test orders, legal pages</li>
<li><strong>Migration</strong> - if moving from another platform, data migration and redirects</li>
<li><strong>Maintenance</strong> - a monthly retainer or ad-hoc developer hours</li>
<li><strong>Marketing</strong> - the store is a beginning; traffic needs its own budget</li>
</ul>
<h2>Budget ranges for {city} businesses</h2>
<ol class="steps">
<li><strong>Starter</strong> - free or premium theme, light customisation, small catalogue: the lowest total cost, fastest launch</li>
<li><strong>Growth</strong> - premium theme heavily customised or a semi-custom design, custom sections, a few integrations</li>
<li><strong>Brand</strong> - fully custom design and theme, advanced features, content production</li>
<li><strong>Enterprise</strong> - Shopify Plus, ERP integration, B2B, multi-store or headless</li>
</ol>
<h2>How to keep the cost down without a cheap store</h2>
<ul class="check-list">
<li>Start on a premium theme and invest in custom sections where they matter (product page, homepage)</li>
<li>Prepare content before development starts</li>
<li>Choose apps once, deliberately</li>
<li>Get a fixed-price quote from a team that serves {country} clients at offshore rates with agency process</li>
</ul>
<p>TopWebExpert sets up and builds Shopify stores for {city} businesses at fixed prices for each tier above. <a href="/contact-us">Describe your store</a> and we will send a line-by-line estimate.</p>
HTML
    ];

    $g[] = ['service_slug' => 'shopify-development-company', 'slug' => 'best-shopify-development-company-checklist', 'sort_order' => 1,
        'title' => 'Best Shopify Development Company in {city}: How to Compare the Top Agencies',
        'meta_title' => 'Best Shopify Development Company in {city} - Comparison Checklist',
        'meta_description' => 'How to find the best Shopify development company in {city}: a 12-point checklist for comparing top Shopify agencies on portfolio, process, pricing and support.',
        'meta_keywords' => 'best shopify development company {city}, top shopify development companies {city}, shopify development agency {country}, shopify company comparison {city}',
        'content' => <<<'HTML'
<p>Search "best Shopify development company {city}" and you get listicles written by the companies themselves. A better approach is to score the top candidates against the same checklist. Here is the one we would use if we were a {city} merchant hiring in {country}.</p>
<h2>Portfolio: proof, not promises</h2>
<ol class="steps">
<li><strong>Live stores</strong> - ask for three Shopify stores they built, then open them on your phone</li>
<li><strong>Relevance</strong> - at least one store in your category or with your complexity (B2B, subscriptions, large catalogue)</li>
<li><strong>Results</strong> - what changed after launch: conversion, speed, revenue - with permission to verify</li>
</ol>
<h2>Team: who actually does the work</h2>
<ol class="steps">
<li><strong>Named developers</strong> - not "our team"; the people who will touch your theme</li>
<li><strong>Shopify Partner status</strong> - a baseline, not a guarantee</li>
<li><strong>In-house design and development</strong> - subcontracted design is where quality slips</li>
</ol>
<h2>Process: how the project runs</h2>
<ol class="steps">
<li><strong>Written scope</strong> - deliverables, exclusions and sign-off points before any money changes hands</li>
<li><strong>Staging and version control</strong> - work on a duplicate theme, code in Git</li>
<li><strong>Demos</strong> - weekly or sprint-based, so nothing is a surprise at the end</li>
</ol>
<h2>Commercials: pricing and ownership</h2>
<ol class="steps">
<li><strong>Fixed price for the build</strong> - hourly is fine for maintenance, risky for a build</li>
<li><strong>Full ownership</strong> - theme code, apps and accounts belong to your {city} business</li>
<li><strong>Post-launch support</strong> - what is included, and the cost of a retainer afterwards</li>
</ol>
<h2>Local agency, freelancer or offshore team?</h2>
<p>A local {city} agency offers face-to-face meetings at premium rates. A freelancer is cheap and fast until they are unavailable. An offshore agency serving {country} clients - the model TopWebExpert runs - offers senior developers and agency process at a lower rate, provided communication and time-zone overlap are handled properly. Score all three on the checklist above rather than on category.</p>
<h2>Red flags</h2>
<ul class="check-list">
<li>No live stores, only mock-ups</li>
<li>A quote without a written scope</li>
<li>Ownership of the theme retained by the agency</li>
<li>"Unlimited revisions" - usually means no plan</li>
<li>Pressure to sign before you have spoken to a developer</li>
</ul>
<p>TopWebExpert is happy to be scored on every point above by {city} businesses. <a href="/contact-us">Send your brief</a> and we will answer the checklist in writing alongside a fixed quote.</p>
HTML
    ];

    $g[] = ['service_slug' => 'shopify-developer', 'slug' => 'shopify-developer-near-me-guide', 'sort_order' => 2,
        'title' => 'Shopify Developer Near Me: Finding the Right Shopify Help in {city}',
        'meta_title' => 'Shopify Developer Near Me in {city} - What to Look For',
        'meta_description' => 'Searching "Shopify developer near me" in {city}? Learn when a local developer matters, when remote is better, and how to vet Shopify developers serving {country}.',
        'meta_keywords' => 'shopify developer near me {city}, local shopify developer {city}, shopify expert near me {country}, shopify freelancer {city}',
        'content' => <<<'HTML'
<p>"Shopify developer near me" is one of the most common ways {city} business owners start looking for help. It is a sensible instinct - you want someone accountable - but for Shopify work, "near" matters less than most people assume. This guide explains when a local {city} developer is worth paying for and how to vet anyone, local or remote, before handing over store access.</p>
<h2>When a local Shopify developer in {city} genuinely helps</h2>
<ul class="check-list">
<li><strong>Retail with POS</strong> - a physical shop in {city} using Shopify POS benefits from someone who can visit</li>
<li><strong>Photography and content days</strong> - if the same partner produces content, proximity saves time</li>
<li><strong>Complex stakeholder projects</strong> - large teams sometimes prefer in-person workshops</li>
</ul>
<h2>When remote is the better choice</h2>
<ul class="check-list">
<li><strong>Theme, app and integration work</strong> - all done in the browser; location is irrelevant</li>
<li><strong>Cost</strong> - {city} agency rates include {city} rent; remote teams serving {country} do not</li>
<li><strong>Talent pool</strong> - the best Shopify specialist for your problem is rarely within ten miles</li>
<li><strong>Availability</strong> - a team with overlap hours answers faster than a solo local freelancer</li>
</ul>
<h2>How to vet any Shopify developer</h2>
<ol class="steps">
<li><strong>Ask for live stores</strong> and test them on mobile</li>
<li><strong>Give a small paid task first</strong> - a section or a fix - and judge communication as much as code</li>
<li><strong>Use collaborator access</strong>, never share your owner login</li>
<li><strong>Insist on a duplicate theme</strong> for all work</li>
<li><strong>Agree the price in writing</strong> before work starts</li>
</ol>
<h2>Questions that reveal real Shopify expertise</h2>
<ul class="check-list">
<li>How would you add this feature without another app?</li>
<li>What would you change on my product page first, and why?</li>
<li>How do you keep a store fast after adding apps?</li>
<li>What happens to your customisations when the theme updates?</li>
</ul>
<h2>The hybrid most {city} merchants settle on</h2>
<p>A remote Shopify team for development and a local {city} partner for anything physical. TopWebExpert works with {country} merchants on exactly that basis: overlap hours with {city}, written communication, fixed quotes and a team that already knows your store the next time you need something. <a href="/contact-us">Start with a small task</a> and see if it fits.</p>
HTML
    ];

    $g[] = ['service_slug' => 'shopify-store-setup', 'slug' => 'shopify-multi-currency-setup-guide', 'sort_order' => 2,
        'title' => 'Shopify Multi-Currency Setup for {city} Stores Selling Worldwide',
        'meta_title' => 'Shopify Multi Currency Setup Guide for {city} Merchants',
        'meta_description' => 'Step-by-step Shopify multi-currency setup for {city} stores: Shopify Markets, Shopify Payments, currency rounding, local pricing, duties and what to test before launch.',
        'meta_keywords' => 'shopify multi currency setup {city}, shopify markets setup {country}, sell internationally shopify {city}, shopify currency conversion {city}',
        'content' => <<<'HTML'
<p>A {city} store that shows prices only in its home currency loses international buyers at the product page. Shopify multi-currency setup is straightforward once you understand the pieces - Markets, Payments, pricing rules and checkout - and this guide walks a {country} merchant through them in order.</p>
<h2>Step 1: Decide your markets</h2>
<p>Shopify Markets groups countries into markets, each with its own currency, language, domain and pricing. Start with the two or three countries that already send traffic to your {city} store - Shopify analytics shows which - rather than "the world".</p>
<h2>Step 2: Enable Shopify Payments (or a multi-currency gateway)</h2>
<p>Local-currency checkout requires Shopify Payments where it is available in {country}, or a third-party gateway that supports multi-currency. Without it, prices can be displayed in other currencies but customers still pay in your base currency, which hurts conversion.</p>
<h2>Step 3: Set pricing rules per market</h2>
<ul class="check-list">
<li><strong>Automatic conversion</strong> with rounding rules so prices look deliberate (49.00, not 48.73)</li>
<li><strong>Price adjustments</strong> per market to cover shipping and fees</li>
<li><strong>Fixed prices</strong> for key markets where you want full control</li>
</ul>
<h2>Step 4: Domains and language</h2>
<p>Use subfolders (yourstore.com/en-us) or country domains per market, with translated content where the language differs. Shopify handles hreflang tags for you when Markets is configured correctly.</p>
<h2>Step 5: Duties, taxes and shipping</h2>
<ul class="check-list">
<li><strong>Duties and import taxes</strong> - collect at checkout with Markets Pro or configured duties, so international buyers are not surprised on delivery</li>
<li><strong>Tax settings</strong> per market, including registration thresholds</li>
<li><strong>Shipping profiles</strong> and carrier-calculated rates per market</li>
</ul>
<h2>Step 6: Theme support</h2>
<p>The theme must include a currency and country selector and format prices correctly. Most modern Online Store 2.0 themes do; older themes and some apps break here, which is where a developer is worth an hour of time.</p>
<h2>Step 7: Test like a customer</h2>
<ol class="steps">
<li>Browse from each market using a VPN and check prices, currency and language</li>
<li>Place a test order in each currency and verify the payout currency</li>
<li>Check notification emails show the right currency</li>
<li>Confirm duties are collected where configured</li>
</ol>
<p>TopWebExpert configures Shopify Markets, multi-currency and international checkout for {city} merchants as part of store setup or as a standalone project. <a href="/contact-us">Tell us which countries you want to sell to</a> and we will scope the setup.</p>
HTML
    ];

    $g[] = ['service_slug' => 'shopify-conversion-rate-optimization', 'slug' => 'shopify-site-not-converting-fixes', 'sort_order' => 1,
        'title' => 'Shopify Site Not Converting? 12 Fixes for {city} Stores',
        'meta_title' => 'Shopify Store Not Converting - 12 Fixes for {city} Merchants',
        'meta_description' => 'Traffic but no sales? The 12 most common reasons a Shopify store in {city} is not converting, how to diagnose each with analytics, and what to fix first.',
        'meta_keywords' => 'shopify site not converting {city}, shopify store no sales {city}, increase shopify conversion rate {country}, shopify cro {city}',
        'content' => <<<'HTML'
<p>Traffic is arriving, ads are running, and the orders are not coming. A Shopify store that is not converting is rarely broken in one place; it is usually leaking in five. These are the twelve causes we find most often when auditing {city} stores, with the check that confirms each one.</p>
<h2>Trust problems</h2>
<ol class="steps">
<li><strong>No social proof</strong> - no reviews, no press, no real photos. Check: does the product page show a review count above the fold?</li>
<li><strong>Template look</strong> - a store that looks like a thousand others reads as risky to {country} shoppers. Check: cover the logo and ask a stranger what the brand is.</li>
<li><strong>Missing policies</strong> - shipping times, returns and contact details hidden or absent. Check: can a visitor find delivery time to {city} in one click?</li>
</ol>
<h2>Traffic problems</h2>
<ol class="steps">
<li><strong>Wrong audience</strong> - ads bringing visitors who never intended to buy. Check: compare conversion by traffic source in Shopify analytics.</li>
<li><strong>Landing on the wrong page</strong> - ads landing on the homepage instead of the product. Check: landing page report.</li>
</ol>
<h2>Product page problems</h2>
<ol class="steps">
<li><strong>Weak imagery</strong> - one photo, no lifestyle shots, no video. Check: image count and zoom.</li>
<li><strong>Unclear price and offer</strong> - shipping cost or discount unknown until checkout. Check: is total cost predictable from the product page?</li>
<li><strong>Slow page</strong> - three seconds on mobile is a lost sale. Check: run a speed test on the product template.</li>
<li><strong>Buried add-to-cart</strong> - button below the fold on mobile. Check: on a phone, without scrolling.</li>
</ol>
<h2>Cart and checkout problems</h2>
<ol class="steps">
<li><strong>Cart friction</strong> - forced account creation, surprise fees, no express pay. Check: checkout abandonment rate.</li>
<li><strong>Payment methods</strong> - missing the wallets and local options {country} buyers expect. Check: payment method availability by market.</li>
<li><strong>No recovery</strong> - abandoned checkouts not followed up. Check: abandoned checkout emails and SMS enabled?</li>
</ol>
<h2>What to fix first</h2>
<p>Rank by traffic touched: product page and checkout issues affect every visitor, trust issues affect first-time visitors, traffic issues affect paid channels. A {city} store fixing speed, above-the-fold product page and checkout friction typically sees the biggest early lift.</p>
<p>If you would rather have the diagnosis done for you, TopWebExpert runs CRO audits for {city} stores that score all twelve points against your real data. <a href="/contact-us">Request a conversion audit</a>.</p>
HTML
    ];

    $g[] = ['service_slug' => 'shopify-speed-optimization', 'slug' => 'shopify-store-slow-loading-fix', 'sort_order' => 1,
        'title' => 'Shopify Store Slow Loading? How {city} Merchants Fix It',
        'meta_title' => 'Shopify Store Slow Loading Fix - Guide for {city} Stores',
        'meta_description' => 'Why Shopify stores load slowly and how to fix it: apps, images, theme code, fonts and third-party scripts. A practical checklist for {city} merchants in {country}.',
        'meta_keywords' => 'shopify store slow loading fix {city}, shopify speed optimization {city}, shopify page speed {country}, slow shopify store {city}',
        'content' => <<<'HTML'
<p>Shopify itself is fast - its servers and CDN are excellent. When a {city} store loads slowly, the cause is almost always what has been added on top: apps, images, theme edits and tracking scripts. Here is how to find which, and fix it, in the order that gives the biggest gain.</p>
<h2>First, measure properly</h2>
<p>Run Google PageSpeed Insights on your homepage, a collection page and a product page, on mobile. Note Largest Contentful Paint, Total Blocking Time and Cumulative Layout Shift. Then check Shopify's own speed report in the admin. You now have a baseline for your {city} store.</p>
<h2>Fix 1: App bloat</h2>
<ul class="check-list">
<li>List every installed app and ask what it does for revenue</li>
<li>Uninstall what you do not use - then check the theme for leftover code apps leave behind</li>
<li>Replace heavy apps with lighter ones or with theme code</li>
</ul>
<h2>Fix 2: Images</h2>
<ul class="check-list">
<li>Use Shopify's image tags with correct sizes so phones are not downloading desktop images</li>
<li>Lazy-load everything below the fold; preload the hero image</li>
<li>Keep decorative images small; convert heavy PNGs to WebP</li>
</ul>
<h2>Fix 3: Theme code</h2>
<ul class="check-list">
<li>Remove render-blocking scripts and stylesheets from the head</li>
<li>Load JavaScript deferred; only load slider and video libraries on pages that use them</li>
<li>Clean out years of accumulated edits - old sections, unused snippets, duplicated CSS</li>
</ul>
<h2>Fix 4: Fonts</h2>
<p>Every custom font is a download. Two families, preloaded, with system fallbacks, is enough for almost any {country} brand.</p>
<h2>Fix 5: Third-party scripts</h2>
<p>Chat widgets, heat-maps, pixels and review widgets are often the largest cost. Load them after the page is interactive, and remove the ones nobody looks at.</p>
<h2>Fix 6: Layout shift</h2>
<p>Reserve space for images, banners and app widgets so content does not jump as it loads. This affects Core Web Vitals and, therefore, Google rankings for your {city} store.</p>
<h2>What speed is "good enough"?</h2>
<p>For a {country} store, aim for Largest Contentful Paint under 2.5 seconds on mobile and no visible layout shift. Beyond that, returns diminish - spend the effort on conversion instead.</p>
<h2>When to get a developer</h2>
<p>Fixes 1 and 5 are admin work; fixes 2, 3, 4 and 6 need theme code changes and should be done on a duplicate theme. TopWebExpert offers a free speed check for {city} stores and a fixed-price speed optimisation that typically halves load times. <a href="/contact-us">Send your store URL</a> to start.</p>
HTML
    ];

    $g[] = ['service_slug' => 'woocommerce-to-shopify-migration', 'slug' => 'shopify-vs-woocommerce-for-business', 'sort_order' => 1,
        'title' => 'Shopify vs WooCommerce for {city} Businesses: Which Should You Choose?',
        'meta_title' => 'Shopify vs WooCommerce for Business in {city} - Honest Comparison',
        'meta_description' => 'Shopify vs WooCommerce for a {city} business: cost, maintenance, speed, SEO, scalability and when to switch. A practical comparison for {country} merchants.',
        'meta_keywords' => 'shopify vs woocommerce for business {city}, shopify or woocommerce {country}, woocommerce vs shopify {city}, best ecommerce platform {city}',
        'content' => <<<'HTML'
<p>Both platforms can run a successful store, so the useful question for a {city} business is not "which is better" but "which is better for us, now". Here is the comparison we give {country} merchants who ask - including when the honest answer is to stay on WooCommerce.</p>
<h2>Total cost of ownership</h2>
<p><strong>WooCommerce</strong> is free software with paid hosting, paid plugins, a developer for updates and security, and your time. <strong>Shopify</strong> is a monthly subscription that includes hosting, security, updates and support, plus app fees. For most {city} stores under a large catalogue, the total is similar; Shopify's is more predictable.</p>
<h2>Maintenance and risk</h2>
<ul class="check-list">
<li><strong>WooCommerce</strong> - you own the updates: WordPress core, WooCommerce, theme and every plugin, with the conflicts that brings</li>
<li><strong>Shopify</strong> - the platform is maintained for you; your theme and apps are all you look after</li>
</ul>
<h2>Speed and reliability</h2>
<p>Shopify's global infrastructure handles traffic spikes without configuration. WooCommerce performance depends entirely on hosting, caching and plugin discipline - achievable, but it is work a {city} business has to keep doing.</p>
<h2>Flexibility</h2>
<p>This is where WooCommerce wins: anything is possible with custom code. Shopify is more structured, and some unusual checkout or catalogue requirements need Shopify Plus or workarounds. For the majority of {country} retail stores, Shopify's structure is a benefit, not a limit.</p>
<h2>SEO and content</h2>
<p>WordPress is the stronger content platform; Shopify has closed most of the gap and its URL structure is stricter. A content-heavy {city} publisher that also sells may prefer WordPress; a store that also blogs will be well served by Shopify.</p>
<h2>Scalability</h2>
<p>Shopify scales from a first sale to enterprise volume on the same platform, with Shopify Plus at the top. WooCommerce can scale, but every step up requires infrastructure decisions.</p>
<h2>Stay on WooCommerce if</h2>
<ul class="check-list">
<li>Your store depends on deeply custom WordPress functionality</li>
<li>You have reliable technical staff who enjoy maintaining it</li>
<li>Content is your primary business and commerce is secondary</li>
</ul>
<h2>Switch to Shopify if</h2>
<ul class="check-list">
<li>Maintenance, security or downtime is costing your {city} team time</li>
<li>You want predictable costs and platform support</li>
<li>You plan to grow, sell internationally or add B2B</li>
<li>Your WooCommerce site has become slow and nobody is sure why</li>
</ul>
<p>If you decide to switch, migration is a rehearsed process: products, customers, orders, content and redirects. TopWebExpert migrates WooCommerce stores for {city} merchants with a full trial migration before the real one. <a href="/contact-us">Ask for a migration audit</a> to see what your move would involve.</p>
HTML
    ];

    $g[] = ['service_slug' => 'shopify-app-development', 'slug' => 'shopify-subscription-app-development-guide', 'sort_order' => 1,
        'title' => 'Shopify Subscription App Development for {city} Brands: Build or Buy?',
        'meta_title' => 'Shopify Subscription App Development Guide for {city} Brands',
        'meta_description' => 'Subscriptions on Shopify for {city} brands: how the Subscriptions API works, when an off-the-shelf app is enough, when custom subscription app development pays off, and what it involves.',
        'meta_keywords' => 'shopify subscription app development {city}, shopify subscriptions {country}, custom subscription app shopify {city}, recurring billing shopify {city}',
        'content' => <<<'HTML'
<p>Recurring revenue changes a business: predictable cash flow, higher lifetime value and a customer base you can plan around. Shopify supports subscriptions natively through its Subscriptions API, and a {city} brand has two routes - install a subscription app, or have one developed. This guide explains how to choose, and what custom subscription app development involves.</p>
<h2>How subscriptions work on Shopify</h2>
<p>Since the Subscriptions API launched, subscription apps no longer run their own checkout. Selling plans are attached to products, Shopify checkout handles the first purchase, and the app manages contracts, billing attempts, customer portals and dunning. This makes both off-the-shelf and custom apps far more reliable than the old redirect-based tools {country} merchants may remember.</p>
<h2>When an existing app is enough</h2>
<ul class="check-list">
<li>Standard "subscribe and save" or replenishment offers</li>
<li>Simple frequencies and discounts</li>
<li>A customer portal with pause, skip and swap</li>
<li>A {city} team happy to work within the app's rules</li>
</ul>
<h2>When custom subscription app development pays off</h2>
<ul class="check-list">
<li><strong>Unusual billing logic</strong> - usage-based, tiered, prepaid with instalments, member pricing across the store</li>
<li><strong>Custom customer portal</strong> - a branded experience or one embedded in your own app</li>
<li><strong>Integration needs</strong> - subscriptions synced with an ERP, fulfilment partner or loyalty system</li>
<li><strong>Cost at scale</strong> - app fees taken as a percentage of subscription revenue exceed the cost of building</li>
<li><strong>Product differentiation</strong> - the subscription experience is the product</li>
</ul>
<h2>What a custom subscription app involves</h2>
<ol class="steps">
<li><strong>Selling plans</strong> - defining frequencies, pricing policies and delivery policies through the API</li>
<li><strong>Subscription contracts</strong> - creating and managing contracts after the first checkout</li>
<li><strong>Billing attempts and dunning</strong> - scheduling charges, retrying failures, notifying customers</li>
<li><strong>Customer portal</strong> - manage, pause, skip, swap and update payment methods</li>
<li><strong>Admin tooling</strong> - reporting, churn analysis and manual adjustments for your {city} team</li>
<li><strong>Webhooks and compliance</strong> - GDPR endpoints, API versioning and monitoring</li>
</ol>
<h2>Timeline and budget</h2>
<p>A focused custom subscription app for a {country} brand is a multi-week project with a defined scope; a full platform with portal and integrations takes longer. Compare the build cost against three years of app fees at your projected subscription revenue - the answer is usually clear.</p>
<p>TopWebExpert builds subscription and membership apps on the Shopify Subscriptions API for {city} brands, and will tell you honestly when an existing app is the better choice. <a href="/contact-us">Describe your subscription model</a> to get a recommendation.</p>
HTML
    ];

    return $g;
}

// Run the expansion for up to $budgetSeconds. Returns progress counts and whether it finished.
function keyword_migration_run($conn, $budgetSeconds = 25, $log = null) {
    $deadline = microtime(true) + $budgetSeconds;
    $out = ['locations_added' => 0, 'guides_added' => 0, 'pages_added' => 0, 'pages_existing' => 0, 'complete' => false, 'errors' => []];
    $say = function ($m) use ($log) { if ($log) $log($m); };

    // 1) Country-level locations
    $stmt = $conn->prepare("INSERT IGNORE INTO locations (country, city, slug, is_active, lat, lng) VALUES (?, ?, ?, 1, ?, ?)");
    if (!$stmt) { $out['errors'][] = 'locations: ' . $conn->error; return $out; }
    foreach (keyword_migration_locations() as $slug => [$country, $city, $lat, $lng]) {
        $stmt->bind_param('sssdd', $country, $city, $slug, $lat, $lng);
        $stmt->execute();
        if ($stmt->affected_rows > 0) $out['locations_added']++;
    }
    $say("locations added: {$out['locations_added']}");

    // 2) Guide templates
    $stmt = $conn->prepare("INSERT IGNORE INTO service_blogs (service_slug, slug, title, content, meta_title, meta_description, meta_keywords, sort_order, is_active) VALUES (?,?,?,?,?,?,?,?,1)");
    if (!$stmt) { $out['errors'][] = 'service_blogs: ' . $conn->error; return $out; }
    foreach (keyword_migration_guides() as $g) {
        $stmt->bind_param('sssssssi', $g['service_slug'], $g['slug'], $g['title'], $g['content'], $g['meta_title'], $g['meta_description'], $g['meta_keywords'], $g['sort_order']);
        $stmt->execute();
        if ($stmt->affected_rows > 0) $out['guides_added']++;
    }
    $say("guides added: {$out['guides_added']}");

    // 3) City pages. Work list = (new services × all locations) + (all services × new locations).
    $services = services_list();
    $locations = $conn->query("SELECT id, city, country, slug FROM locations WHERE is_active = 1 ORDER BY id")->fetch_all(MYSQLI_ASSOC);
    $newLocSlugs = array_keys(keyword_migration_locations());
    $newServices = keyword_migration_services();
    $work = []; // service_slug => list of location rows
    foreach ($newServices as $svc) $work[$svc] = $locations;
    foreach ($services as $svc => $_) {
        if (in_array($svc, $newServices, true)) continue;
        $work[$svc] = array_values(array_filter($locations, fn($l) => in_array($l['slug'], $newLocSlugs, true)));
    }

    $ins = $conn->prepare("INSERT IGNORE INTO city_pages (service_slug, service_name, location_id, title, content, image, meta_title, meta_description, meta_keywords) VALUES (?,?,?,?,?,?,?,?,?)");
    if (!$ins) { $out['errors'][] = 'city_pages: ' . $conn->error; return $out; }

    foreach ($work as $svc => $locs) {
        if (!$locs) continue;
        [$svcName, $img] = $services[$svc];
        // Existing pages for this service, so resumed runs skip straight to what is missing
        $have = [];
        $r = $conn->query("SELECT location_id FROM city_pages WHERE service_slug = '" . $conn->real_escape_string($svc) . "'");
        while ($row = $r->fetch_row()) $have[(int)$row[0]] = true;
        $added = 0;
        foreach ($locs as $loc) {
            $locId = (int)$loc['id'];
            if (isset($have[$locId])) { $out['pages_existing']++; continue; }
            if (microtime(true) > $deadline) {
                $say("budget reached during $svc (added $added this run)");
                return $out; // complete stays false
            }
            $title = "$svcName in " . location_label($loc['city'], $loc['country']);
            $content = generate_city_page_content($svcName, $loc['city'], $loc['country'], $svc);
            [$mt, $md, $mk] = generate_city_page_meta($svc, $svcName, $loc['city'], $loc['country']);
            $ins->bind_param('ssissssss', $svc, $svcName, $locId, $title, $content, $img, $mt, $md, $mk);
            if (!$ins->execute()) { $out['errors'][] = "$svc/{$loc['slug']}: " . $ins->error; continue; }
            if ($ins->affected_rows > 0) { $out['pages_added']++; $added++; } else { $out['pages_existing']++; }
        }
        if ($added) $say("$svc: $added pages added");
    }

    $out['complete'] = true;
    // New pages change the sitemap: drop the cached parts so the next fetch regenerates
    require_once __DIR__ . '/cache.php';
    cache_clear_status(5);
    return $out;
}

// Sanity check used by the CLI script: no guide slug may be a prefix of a service slug
// (the router matches guides first, so such a guide would hijack every page of that service).
function keyword_migration_check_slugs($conn) {
    $problems = [];
    $services = array_keys(services_list());
    $guides = array_column($conn->query("SELECT slug FROM service_blogs")->fetch_all(MYSQLI_ASSOC), 'slug');
    foreach (keyword_migration_guides() as $g) $guides[] = $g['slug'];
    foreach ($guides as $g) {
        foreach ($services as $s) {
            if (strpos($s . '-', $g . '-') === 0) $problems[] = "guide '$g' would capture service '$s' URLs";
        }
    }
    return $problems;
}
