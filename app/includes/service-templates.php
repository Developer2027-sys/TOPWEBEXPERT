<?php
// Per-service page copy for the Shopify keyword services (added September 2026 from
// the "Shopify Agency Keyword Research" pack). Each service has its own intro,
// deliverables, process, reasons, FAQ and meta description, so the 1,470+ city
// pages of one service differ in substance from every other service — not just in
// the service name swapped into one shared template.
//
// Placeholders: {city} {country} {place} {name} {kw}
//   {place} = "City, Country" (or just the name for country-level pages)
//   {kw}    = the primary keyword phrase in lower case, e.g. "shopify developer"
//
// service_page_html() renders one page; generate_city_page_content() in
// services-config.php calls it when a template exists for the slug.

function service_templates() {
    static $t = null;
    if ($t !== null) return $t;
    $t = [

    'shopify-developer' => [
        'kw' => 'shopify developer',
        'variants' => ['hire shopify developer', 'shopify freelancer', 'shopify developer near me', 'shopify developer for hire'],
        'meta' => 'Hire an experienced Shopify developer in {place}. Custom themes, apps, integrations and store builds delivered by certified TopWebExpert developers. Get a free quote.',
        'intro' => 'Looking to <strong class="kw">hire a Shopify developer in {city}</strong> who writes clean Liquid, understands conversion and delivers on time? TopWebExpert gives {city} merchants direct access to senior Shopify developers - not a sales layer - with clear hourly or fixed-price engagement, {country}-friendly working hours and a portfolio of stores that sell.',
        'offer' => [
            '<strong>Custom theme builds and Liquid work</strong> - sections, blocks, metafields and Online Store 2.0 features coded to your design',
            '<strong>Bug fixes and small jobs</strong> - most {city} clients start with a single fix and stay for years',
            '<strong>App and API integrations</strong> - shipping, payments, ERP, CRM and marketing tools connected properly',
            '<strong>Performance and Core Web Vitals</strong> - lean code that keeps your store fast on mobile',
            '<strong>Ongoing developer retainer</strong> - reserved hours every month for a store that never stops changing',
        ],
        'process' => [
            '<strong>Scope call</strong> - 30 minutes to understand what you need and give a fixed estimate',
            '<strong>Staging build</strong> - all work happens on a duplicate theme, your live store is never touched',
            '<strong>Review and revisions</strong> - you test on a preview link and request changes',
            '<strong>Go-live and handover</strong> - we publish, document what changed and stay on for support',
        ],
        'why' => 'Most "Shopify developers" a {city} business finds online are either a marketplace freelancer who disappears mid-project or an agency that charges agency overhead for a two-hour task. TopWebExpert sits in between: a real team with a project manager, source control and QA, at rates a growing {country} store can sustain.',
        'faq' => [
            ['How much does a Shopify developer in {city} cost?', 'Small fixes start at a fixed price; larger builds are quoted after a scope call. Retainer clients in {country} typically reserve 10 to 40 hours a month at a discounted rate.'],
            ['Can I hire a Shopify developer for just one task?', 'Yes. Single tasks - a section, a bug, an integration - are the most common way {city} merchants start working with us.'],
            ['Do you work with Shopify Plus stores?', 'Yes. Our developers work on Shopify Plus features including checkout extensibility, Shopify Functions and B2B.'],
        ],
        'cta' => 'Tell us what your store needs and get a fixed quote from a Shopify developer who serves {place} - usually within one business day.',
    ],

    'shopify-agency' => [
        'kw' => 'shopify agency',
        'variants' => ['shopify design agency', 'shopify partner agency', 'shopify ecommerce agency'],
        'meta' => 'TopWebExpert is a full-service Shopify agency for {place}: strategy, design, development, migration and growth marketing for Shopify and Shopify Plus brands.',
        'intro' => 'TopWebExpert is a <strong class="kw">Shopify agency serving {city}</strong> brands that need more than a theme install. Strategy, UX design, development, migration and post-launch growth sit under one roof, so your store in {country} is planned, built and scaled by the same team that will be accountable for the results.',
        'offer' => [
            '<strong>Store strategy and UX</strong> - customer research, information architecture and wireframes before a pixel is designed',
            '<strong>Design and development</strong> - bespoke themes built for conversion, speed and easy content editing',
            '<strong>Migrations and re-platforming</strong> - moving {city} brands to Shopify from WooCommerce, Magento, BigCommerce or custom stacks',
            '<strong>Growth services</strong> - CRO, SEO, email and paid media run against the same revenue targets',
            '<strong>Retained partnership</strong> - a dedicated account lead and development capacity every month',
        ],
        'process' => [
            '<strong>Discovery workshop</strong> - goals, products, margins and what the current store gets wrong',
            '<strong>Roadmap and proposal</strong> - phased plan with a budget for each phase, nothing open-ended',
            '<strong>Design, build, launch</strong> - weekly demos so nothing surprises you at the end',
            '<strong>Grow</strong> - monthly optimisation sprints driven by analytics, not opinions',
        ],
        'why' => 'A local {city} agency charges local overhead; a marketplace freelancer cannot take ownership of your growth. As a Shopify agency built for international clients, TopWebExpert gives {country} merchants senior talent, process and accountability at a cost that leaves budget for marketing.',
        'faq' => [
            ['What makes a good Shopify agency for a {city} brand?', 'Look for real Shopify stores in their portfolio, a named team, a documented process and pricing that separates build from growth work. TopWebExpert provides all four on the first call.'],
            ['Do you only work with large brands?', 'No. We work with {city} start-ups launching their first store and with established retailers on Shopify Plus.'],
            ['Can you take over an existing store?', 'Yes. Agency clients in {country} often come to us with a store built elsewhere; we audit it first and propose the shortest path to improvement.'],
        ],
        'cta' => 'Book a discovery call with a Shopify agency that treats {city} clients as long-term partners, not tickets.',
    ],

    'shopify-development-company' => [
        'kw' => 'shopify development company',
        'variants' => ['shopify development agency', 'best shopify development company', 'top shopify development companies'],
        'meta' => 'Rated among the best Shopify development companies for {place}. End-to-end Shopify store development, custom features and Plus builds by TopWebExpert.',
        'intro' => 'Choosing the <strong class="kw">best Shopify development company in {city}</strong> comes down to three things: proof, process and price. TopWebExpert shows {city} clients live stores we have built, a written development process, and transparent fixed-price quotes - which is why brands across {country} rank us among the top Shopify development companies they have worked with.',
        'offer' => [
            '<strong>End-to-end store development</strong> - from product data and theme to payments, taxes and shipping for {country}',
            '<strong>Custom functionality</strong> - configurators, bundles, quotes, loyalty and anything a stock app cannot do',
            '<strong>Shopify Plus development</strong> - checkout customisation, Functions, B2B and multi-store',
            '<strong>Third-party integrations</strong> - ERP, accounting, warehouse, marketplaces and marketing stacks',
            '<strong>Post-launch development</strong> - a monthly plan so the store keeps evolving with your business',
        ],
        'process' => [
            '<strong>Requirements and estimate</strong> - a written scope you can compare with any other company',
            '<strong>Design approval</strong> - clickable mock-ups signed off before development starts',
            '<strong>Development sprints</strong> - two-week sprints with demos, on a staging store',
            '<strong>QA, launch and training</strong> - device testing, go-live checklist and a training session for your {city} team',
        ],
        'why' => 'Development companies fail {city} clients in two ways: they over-promise on timelines or they lock you in with undocumented code. Every TopWebExpert build is delivered with clean, commented code, a handover document and full ownership of your theme and apps.',
        'faq' => [
            ['How do I compare Shopify development companies in {city}?', 'Ask each company for three live stores, a written process, who exactly will do the work, and a fixed quote against the same brief. Then compare like for like.'],
            ['How long does a Shopify store build take?', 'A standard store for a {country} merchant takes four to six weeks; complex Plus builds run eight to fourteen weeks.'],
            ['Will I own the code?', 'Yes. Everything is built in your own Shopify account and you receive the full theme source.'],
        ],
        'cta' => 'Get a fixed-price proposal from a Shopify development company that {city} brands recommend.',
    ],

    'shopify-expert' => [
        'kw' => 'shopify expert',
        'variants' => ['shopify consultant', 'shopify partner', 'shopify consulting services'],
        'meta' => 'Work with a certified Shopify expert and consultant for {place}. Store audits, growth strategy, technical consulting and hands-on fixes from TopWebExpert.',
        'intro' => 'A <strong class="kw">Shopify expert in {city}</strong> should be able to look at your store for an hour and tell you exactly where revenue is leaking. TopWebExpert consultants have audited and improved hundreds of Shopify stores, and we bring that pattern-matching to {city} merchants who need answers, not another long project.',
        'offer' => [
            '<strong>Store audits</strong> - conversion, speed, SEO, app bloat and theme quality scored with a prioritised fix list',
            '<strong>Strategy consulting</strong> - platform decisions, Plus upgrades, international expansion from {country}',
            '<strong>Technical consulting</strong> - integration architecture, app selection and custom development scoping',
            '<strong>Hands-on implementation</strong> - we do not just advise; our developers apply the fixes',
            '<strong>Fractional ecommerce lead</strong> - a Shopify expert on call for your {city} team every month',
        ],
        'process' => [
            '<strong>Diagnostic session</strong> - a recorded walkthrough of your store with quick wins identified live',
            '<strong>Written recommendations</strong> - ranked by impact and effort, with cost estimates',
            '<strong>Implementation</strong> - by our team or yours, with our consultant reviewing the work',
            '<strong>Measurement</strong> - a follow-up review against the baseline numbers',
        ],
        'why' => '{city} merchants rarely need more apps; they need someone who has seen the same problem fifty times. Our consultants are working developers and marketers, so the advice you get in {country} is grounded in what actually ships.',
        'faq' => [
            ['What does a Shopify consultant cost?', 'A store audit is a fixed price; ongoing consulting is billed hourly or as a monthly retainer for {city} businesses.'],
            ['Are you an official Shopify Partner?', 'Yes. TopWebExpert is a Shopify Partner and builds within the Shopify Partner programme guidelines.'],
            ['Can you review a store built by another agency?', 'Yes - an independent audit is the most common request we get from {country} merchants.'],
        ],
        'cta' => 'Book a diagnostic session with a Shopify expert who works with {place} merchants every week.',
    ],

    'shopify-web-design' => [
        'kw' => 'shopify web design',
        'variants' => ['shopify website design', 'shopify store design', 'shopify designer'],
        'meta' => 'Custom Shopify web design for {place} brands. Conversion-focused store design, bespoke themes and mobile-first layouts by TopWebExpert Shopify designers.',
        'intro' => 'Great <strong class="kw">Shopify web design in {city}</strong> is not about winning design awards; it is about making a first-time visitor trust your brand and find the product they want in three taps. TopWebExpert designs Shopify stores for {city} brands with that discipline: mobile-first, fast, on-brand and built to convert {country} shoppers.',
        'offer' => [
            '<strong>Bespoke store design</strong> - homepage, collection, product, cart and checkout designed as one journey',
            '<strong>Brand-aligned theming</strong> - typography, colour and imagery systems that match your identity',
            '<strong>Conversion-led product pages</strong> - hierarchy, social proof, sizing and delivery information where {country} shoppers expect them',
            '<strong>Mobile-first layouts</strong> - designed on the phone screen first, since most {city} traffic is mobile',
            '<strong>Design-to-development</strong> - our own developers build the design, so nothing is lost in translation',
        ],
        'process' => [
            '<strong>Brand and audience review</strong> - who buys, on what device, and what makes them hesitate',
            '<strong>Wireframes</strong> - structure agreed before visual design',
            '<strong>Visual design</strong> - high-fidelity screens for desktop and mobile, two revision rounds included',
            '<strong>Build and launch</strong> - pixel-accurate theme development and QA',
        ],
        'why' => 'Template stores all look alike, and {city} shoppers notice. A designed Shopify store signals a real brand, lifts average order value and reduces returns through clearer product presentation - a return that pays for the design many times over in {country}.',
        'faq' => [
            ['How much does Shopify website design cost in {city}?', 'Design-only projects are priced by page count; design plus build is quoted as a package. Every {country} quote is fixed before work starts.'],
            ['Can you redesign my existing Shopify store?', 'Yes. Most {city} design projects are redesigns of a store that has outgrown its first theme.'],
            ['Do you design for Shopify Plus?', 'Yes, including customised checkout designs available on Plus.'],
        ],
        'cta' => 'See what a purpose-designed Shopify store could look like for your {city} brand - request a free design consultation.',
    ],

    'shopify-redesign' => [
        'kw' => 'shopify redesign',
        'variants' => ['shopify website redesign', 'shopify store redesign', 'shopify theme redesign'],
        'meta' => 'Shopify store redesign for {place} merchants: modern design, faster theme and higher conversion without losing SEO rankings or sales during the switch.',
        'intro' => 'A <strong class="kw">Shopify redesign for your {city} store</strong> should raise conversion and speed without dropping the Google rankings you have earned. TopWebExpert plans every redesign for {city} merchants around that rule: we keep URLs, structured data and content intact while replacing the design, theme and layout underneath.',
        'offer' => [
            '<strong>Redesign audit</strong> - what to keep, what to fix and what to remove, backed by your own analytics',
            '<strong>New design on Online Store 2.0</strong> - a modern, section-based theme your {city} team can edit',
            '<strong>SEO-safe migration</strong> - URL mapping, redirects and metadata preserved',
            '<strong>Speed uplift</strong> - app clean-up and lean code, usually halving load time',
            '<strong>Parallel launch</strong> - the new theme is built and tested alongside the live store, then switched in minutes',
        ],
        'process' => [
            '<strong>Benchmark</strong> - conversion, speed and ranking baselines recorded before any change',
            '<strong>Design</strong> - new layouts for every template, approved by you',
            '<strong>Build on a duplicate theme</strong> - zero risk to current sales in {country}',
            '<strong>Switch and monitor</strong> - go-live during a quiet hour with two weeks of monitoring',
        ],
        'why' => 'Redesigns go wrong when they are treated as a fresh build. We treat your {city} store as a live business: every change is measured against the benchmark, and anything that dips is fixed before we call the project done.',
        'faq' => [
            ['Will a redesign hurt my SEO?', 'Not when it is planned properly. We keep URLs, redirect anything that changes and preserve titles, descriptions and schema for your {country} pages.'],
            ['How long does a Shopify redesign take?', 'Typically three to six weeks for a {city} store, depending on page count and custom features.'],
            ['Can I keep my products and customers?', 'Yes. A redesign changes the theme only; products, orders and customers are untouched.'],
        ],
        'cta' => 'Request a free redesign audit for your {city} Shopify store and see exactly what would change.',
    ],

    'shopify-theme-development' => [
        'kw' => 'shopify theme development',
        'variants' => ['shopify theme customization', 'shopify theme customization service', 'custom shopify theme'],
        'meta' => 'Custom Shopify theme development and theme customization for {place}: Online Store 2.0 themes, custom sections, Liquid and speed-optimised code by TopWebExpert.',
        'intro' => '<strong class="kw">Shopify theme development in {city}</strong> ranges from customising a purchased theme to building a fully custom Online Store 2.0 theme from scratch. TopWebExpert does both for {city} merchants, writing Liquid, JSON templates and JavaScript that stay fast, pass Shopify theme checks and remain easy for your {country} team to edit.',
        'offer' => [
            '<strong>Custom theme from scratch</strong> - your design, coded as a lean OS 2.0 theme with no unused code',
            '<strong>Theme customization</strong> - new sections, blocks and product-page features added to Dawn or any premium theme',
            '<strong>Metafield-driven content</strong> - size charts, specs, ingredients and FAQs managed without a developer',
            '<strong>Theme upgrades</strong> - moving legacy themes to Online Store 2.0 without losing customisations',
            '<strong>Theme performance</strong> - critical CSS, lazy loading and script control for {country} mobile networks',
        ],
        'process' => [
            '<strong>Theme review</strong> - we inspect your current theme and list what can be reused',
            '<strong>Development on a copy</strong> - customisations built on a duplicate theme with version control',
            '<strong>Theme Check and QA</strong> - Shopify theme validation, cross-device testing and accessibility checks',
            '<strong>Publish and document</strong> - a short guide for editing your new sections',
        ],
        'why' => 'Theme customisation done badly is why so many {city} stores are slow: layers of edits, forgotten apps and duplicated scripts. Our theme work is structured, documented and reversible, so your store stays fast as it grows in {country}.',
        'faq' => [
            ['Should I buy a theme or build a custom one?', 'Under a few hundred products with standard needs, a customised premium theme is the better value for a {city} store. Beyond that, custom theme development pays back in speed and flexibility.'],
            ['Can you customise Dawn?', 'Yes - Dawn is the base for most of our theme customization projects in {country}.'],
            ['Will theme updates overwrite your work?', 'Custom sections are built to survive updates, and we document the upgrade path for each project.'],
        ],
        'cta' => 'Send us your theme and your wish-list; {city} merchants get a fixed quote for theme development within a day.',
    ],

    'shopify-app-development' => [
        'kw' => 'shopify app development',
        'variants' => ['shopify custom app development', 'shopify private app development', 'shopify subscription app development'],
        'meta' => 'Shopify app development for {place}: custom apps, private apps, subscription features and integrations built with the Shopify API by TopWebExpert.',
        'intro' => 'When no app in the Shopify App Store does what your business needs, <strong class="kw">Shopify app development in {city}</strong> is the answer. TopWebExpert builds custom and private apps for {city} merchants - from a small automation to full subscription and B2B systems - using the Shopify Admin API, Storefront API, App Bridge and Shopify Functions.',
        'offer' => [
            '<strong>Custom (private) apps</strong> - built for one store, solving one specific workflow in {country}',
            '<strong>Public app development</strong> - App Store-ready apps with billing, onboarding and support tooling',
            '<strong>Shopify Functions</strong> - custom discounts, shipping and payment logic on Plus',
            '<strong>Subscription and membership features</strong> - recurring billing, customer portals and dunning',
            '<strong>Integration apps</strong> - bridges to ERP, PIM, warehouses and marketplaces',
        ],
        'process' => [
            '<strong>Technical scoping</strong> - data model, API scopes, webhooks and a fixed estimate',
            '<strong>Build</strong> - Node/React or PHP stack, hosted on infrastructure that scales with your {city} store',
            '<strong>Testing on a development store</strong> - before anything touches live orders',
            '<strong>Deployment and monitoring</strong> - error tracking and a support plan',
        ],
        'why' => 'Custom apps are a long-term asset, so they must be built to Shopify standards: GDPR webhooks, API versioning, rate-limit handling. {city} merchants who have had an app built cheaply and watched it break on the next API version know why that matters. We build for the version after next.',
        'faq' => [
            ['How much does Shopify app development cost in {city}?', 'A single-purpose private app starts at a modest fixed price; subscription or B2B systems for {country} merchants are quoted after scoping.'],
            ['Do I need a Shopify Plus plan for a custom app?', 'No. Custom apps work on all plans; only checkout-level customisation requires Plus.'],
            ['Who owns the app?', 'You do. The source code and the app listing in your Partner account belong to your {city} business.'],
        ],
        'cta' => 'Describe the feature your store is missing and get a scoped estimate for Shopify app development in {place}.',
    ],

    'shopify-store-setup' => [
        'kw' => 'shopify store setup',
        'variants' => ['shopify store setup service', 'shopify setup expert', 'launch a shopify store'],
        'meta' => 'Professional Shopify store setup for {place}: theme, products, payments, shipping, taxes, domains and launch checklist handled end to end by TopWebExpert.',
        'intro' => 'A proper <strong class="kw">Shopify store setup for {city}</strong> businesses covers far more than picking a theme. Payments and taxes for {country}, shipping zones, product data, legal pages, analytics and a launch checklist all need doing right the first time. TopWebExpert sets up Shopify stores for {city} merchants so they open ready to sell - and ready to scale.',
        'offer' => [
            '<strong>Store configuration</strong> - domains, payments, taxes, shipping rates and checkout settings for {country}',
            '<strong>Product and collection setup</strong> - clean data, variants, images, tags and navigation',
            '<strong>Theme setup and branding</strong> - homepage, collection and product templates built out',
            '<strong>Essential apps and analytics</strong> - reviews, email, GA4 and pixels installed without bloat',
            '<strong>Launch checklist and training</strong> - test orders, legal pages and a hand-over session for your {city} team',
        ],
        'process' => [
            '<strong>Setup call</strong> - products, markets, payment providers and timeline',
            '<strong>Build</strong> - usually seven to fourteen days for a standard {city} store',
            '<strong>Test orders</strong> - payments, shipping and notifications verified end to end',
            '<strong>Launch and 30-day support</strong> - we stay on for the first month of trading',
        ],
        'why' => 'Stores that are set up in a hurry pay for it later in tax mistakes, shipping errors and messy data. Our setup process for {country} merchants is a checklist refined over hundreds of launches, so your {city} store starts on a clean foundation.',
        'faq' => [
            ['How long does Shopify store setup take?', 'One to two weeks for most {city} stores; larger catalogues take longer for product data preparation.'],
            ['Can you set up multiple currencies and markets?', 'Yes. We configure Shopify Markets so a {country} store can sell internationally from day one.'],
            ['Do you migrate products from another platform?', 'Yes - see our migration services, or include it in the setup package.'],
        ],
        'cta' => 'Get your {city} Shopify store set up properly - request a fixed-price setup package today.',
    ],

    'shopify-plus-agency' => [
        'kw' => 'shopify plus agency',
        'variants' => ['shopify plus developer', 'shopify plus development', 'shopify plus partner'],
        'meta' => 'Shopify Plus agency for {place}: enterprise store builds, checkout extensibility, Shopify Functions, B2B, multi-store and Plus migrations by TopWebExpert.',
        'intro' => 'High-volume brands in {city} need a <strong class="kw">Shopify Plus agency</strong> that understands what the Plus plan actually unlocks - checkout extensibility, Shopify Functions, B2B, Launchpad, expansion stores - and how to use them to grow. TopWebExpert delivers Shopify Plus development for {city} and {country} merchants doing serious volume.',
        'offer' => [
            '<strong>Shopify Plus store builds</strong> - enterprise-grade themes, custom checkout and complex catalogues',
            '<strong>Checkout customisation</strong> - checkout UI extensions, upsells, custom fields and validation',
            '<strong>Shopify Functions</strong> - bespoke discount, delivery and payment rules',
            '<strong>Multi-store and international</strong> - expansion stores for new {country} regions or brands',
            '<strong>Plus migrations</strong> - moving from Advanced Shopify or another enterprise platform without downtime',
        ],
        'process' => [
            '<strong>Plus readiness review</strong> - is Plus the right plan, and what will it unlock for your {city} business',
            '<strong>Architecture</strong> - stores, markets, integrations and data flows designed before building',
            '<strong>Sprint delivery</strong> - enterprise projects delivered in two-week increments with demos',
            '<strong>Launch and optimisation</strong> - peak-season readiness and ongoing feature work',
        ],
        'why' => 'Plus projects fail when built by teams that have only shipped standard Shopify stores. Our Plus work for {country} brands includes checkout extensibility, Functions and B2B rollouts - the features a {city} enterprise is paying for.',
        'faq' => [
            ['When should a {city} brand move to Shopify Plus?', 'Usually when checkout customisation, B2B, multiple stores or high order volume make the standard plan limiting - we advise honestly either way.'],
            ['Do you handle the Shopify Plus migration itself?', 'Yes, including data, theme, apps, checkout and redirects.'],
            ['Can you support us after launch?', 'Yes - most Plus clients in {country} retain us for monthly development and optimisation.'],
        ],
        'cta' => 'Talk to a Shopify Plus agency that has delivered enterprise builds for {place} brands.',
    ],

    'shopify-b2b-development' => [
        'kw' => 'shopify b2b development',
        'variants' => ['shopify wholesale b2b setup', 'shopify wholesale store', 'b2b ecommerce on shopify'],
        'meta' => 'Shopify B2B and wholesale development for {place}: company accounts, price lists, net terms, quick order forms and B2B checkout built by TopWebExpert.',
        'intro' => 'Wholesale buyers expect ordering to be as easy as retail. <strong class="kw">Shopify B2B development in {city}</strong> makes that possible: company accounts, customer-specific price lists, payment terms, quick-order forms and approval flows built on native Shopify B2B or a wholesale-app stack. TopWebExpert builds wholesale stores for {city} manufacturers, distributors and brands selling across {country}.',
        'offer' => [
            '<strong>Native B2B on Shopify Plus</strong> - companies, locations, catalogs, price lists and net terms',
            '<strong>Wholesale on standard plans</strong> - app-based wholesale channels where Plus is not yet justified',
            '<strong>Quick order and reorder tools</strong> - CSV upload, order pads and saved lists for {country} trade buyers',
            '<strong>Account approval and pricing tiers</strong> - registration, credit limits and tier logic',
            '<strong>ERP and accounting sync</strong> - invoices, stock and customer records kept in step',
        ],
        'process' => [
            '<strong>Wholesale requirements</strong> - customer types, price structures, terms and order rules',
            '<strong>Stack decision</strong> - native B2B, blended store or separate wholesale store',
            '<strong>Build and data load</strong> - companies, catalogs and price lists imported and verified',
            '<strong>Buyer onboarding</strong> - launch with guides for your {city} trade customers',
        ],
        'why' => 'B2B is where Shopify expertise shows. Getting price lists, terms and tax handling right for {country} requires people who have done it; TopWebExpert has built wholesale stores that process thousands of trade orders a month.',
        'faq' => [
            ['Do I need Shopify Plus for B2B?', 'Native B2B features require Plus. For smaller {city} wholesalers we build app-based wholesale stores on standard plans.'],
            ['Can retail and wholesale run on one store?', 'Yes - a blended store serves both, with wholesale pricing shown only to logged-in trade accounts.'],
            ['Can buyers pay on account?', 'Yes. Net terms, deposits and invoice payment are supported for {country} customers.'],
        ],
        'cta' => 'Bring your price lists and terms - we will design the right Shopify B2B setup for your {city} business.',
    ],

    'shopify-headless-development' => [
        'kw' => 'shopify headless development',
        'variants' => ['headless shopify', 'shopify hydrogen development', 'shopify storefront api development'],
        'meta' => 'Headless Shopify development for {place} using Hydrogen, Next.js and the Storefront API: custom storefronts with Shopify checkout, built by TopWebExpert.',
        'intro' => '<strong class="kw">Shopify headless development in {city}</strong> separates your storefront from Shopify\'s theme layer, giving your brand a fully custom front end - React, Hydrogen or Next.js - while Shopify still handles products, checkout and orders. TopWebExpert builds headless storefronts for {city} brands in {country} that need speed, content flexibility or a multi-brand architecture a theme cannot deliver.',
        'offer' => [
            '<strong>Hydrogen and Oxygen storefronts</strong> - Shopify\'s own React framework, hosted on Shopify',
            '<strong>Next.js and custom front ends</strong> - Storefront API integration with any framework your {city} team prefers',
            '<strong>Headless CMS integration</strong> - Sanity, Contentful or Storyblok driving content alongside Shopify data',
            '<strong>Performance engineering</strong> - edge rendering and caching for {country} and global audiences',
            '<strong>Honest headless assessment</strong> - we will tell you if a theme would serve you better',
        ],
        'process' => [
            '<strong>Architecture review</strong> - data sources, content model, hosting and checkout flow',
            '<strong>Design system and components</strong> - a reusable front-end library for your brand',
            '<strong>Storefront build</strong> - product, collection, search, cart and account experiences',
            '<strong>Launch and observability</strong> - monitoring, error tracking and deployment pipeline',
        ],
        'why' => 'Headless is powerful and expensive to get wrong. We have shipped both Hydrogen and Next.js storefronts for {country} brands, and we start every {city} headless project by validating that the business case justifies the added complexity.',
        'faq' => [
            ['Is headless right for my {city} store?', 'Usually only for brands with heavy content needs, unusual UX, multiple front ends or performance targets a theme cannot meet. We assess this before quoting.'],
            ['Does headless Shopify still use Shopify checkout?', 'Yes. Checkout, payments and orders stay on Shopify; only the storefront is custom.'],
            ['What does a headless build cost?', 'More than a theme build - budgets for {country} brands typically start where a premium theme project ends.'],
        ],
        'cta' => 'Ask for a headless assessment - {city} brands get a clear recommendation, not a sales pitch.',
    ],

    'shopify-migration' => [
        'kw' => 'shopify migration',
        'variants' => ['migrate to shopify', 'shopify migration service', 'ecommerce platform migration to shopify'],
        'meta' => 'Shopify migration services for {place}: products, customers, orders, SEO redirects and design moved to Shopify with zero data loss by TopWebExpert.',
        'intro' => 'A <strong class="kw">Shopify migration for a {city} store</strong> is a data project first and a design project second. Products, variants, customers, order history, reviews, gift cards and every URL Google knows about have to arrive intact. TopWebExpert has migrated stores to Shopify for {city} merchants from WooCommerce, Magento, BigCommerce, WordPress, OpenCart, PrestaShop, Wix and custom platforms across {country}.',
        'offer' => [
            '<strong>Full data migration</strong> - products, images, categories, customers, orders, discounts and reviews',
            '<strong>SEO preservation</strong> - URL mapping and 301 redirects so {country} rankings carry over',
            '<strong>Theme and design</strong> - recreate the current design or use the move to redesign',
            '<strong>App and integration replacement</strong> - equivalent Shopify apps chosen and configured',
            '<strong>Parallel run and cut-over</strong> - go live with no downtime and a rollback plan',
        ],
        'process' => [
            '<strong>Migration audit</strong> - data volumes, customisations, integrations and risks listed',
            '<strong>Test migration</strong> - a full trial run into a development store, verified item by item',
            '<strong>Final migration</strong> - delta sync of new orders and customers right before cut-over',
            '<strong>Post-migration checks</strong> - redirects, search console, payments and email flows verified for your {city} store',
        ],
        'why' => 'Botched migrations lose orders, break rankings and annoy customers. Ours are rehearsed: every {city} migration is run in full at least once before the real switch, and we do not sign off until your {country} traffic and revenue match the old platform.',
        'faq' => [
            ['Will I lose my Google rankings when migrating to Shopify?', 'Not with proper redirects and metadata migration. Most {country} stores we migrate keep or improve rankings within weeks.'],
            ['Can order history be migrated?', 'Yes, including order status, line items and customer links, so your {city} team keeps full history.'],
            ['How long does a Shopify migration take?', 'Two to six weeks depending on catalogue size and customisations.'],
        ],
        'cta' => 'Get a free migration audit for your {city} store and a fixed quote to move it to Shopify.',
    ],

    'woocommerce-to-shopify-migration' => [
        'kw' => 'woocommerce to shopify migration',
        'variants' => ['migrate woocommerce to shopify', 'woocommerce to shopify migration service', 'move from woocommerce to shopify'],
        'meta' => 'WooCommerce to Shopify migration for {place}: products, customers, orders, SEO and design moved from WordPress/WooCommerce to Shopify by TopWebExpert.',
        'intro' => 'Tired of plugin conflicts, hosting bills and security patches? A <strong class="kw">WooCommerce to Shopify migration in {city}</strong> moves your store to a hosted platform that just works. TopWebExpert migrates WooCommerce stores for {city} merchants - products with all variations and attributes, customers, orders, coupons, reviews and blog posts - and rebuilds the design on Shopify for {country} shoppers.',
        'offer' => [
            '<strong>WooCommerce data export and mapping</strong> - variable products, attributes, categories and custom fields mapped to Shopify variants, options and metafields',
            '<strong>Customers and orders</strong> - accounts, addresses and complete order history',
            '<strong>Blog and content pages</strong> - WordPress posts and pages moved into Shopify',
            '<strong>SEO redirects</strong> - WooCommerce URL structures redirected to Shopify equivalents for {country} search traffic',
            '<strong>Plugin replacement</strong> - each WooCommerce plugin matched to a Shopify app or custom feature',
        ],
        'process' => [
            '<strong>Store audit</strong> - plugins, customisations and data quality reviewed',
            '<strong>Trial migration</strong> - full import into a Shopify development store for your {city} team to check',
            '<strong>Design build</strong> - theme built while the trial is reviewed',
            '<strong>Cut-over</strong> - final sync, DNS switch and redirect verification',
        ],
        'why' => 'WooCommerce stores accumulate custom fields, plugin data and half-finished configurations that generic migration tools miss. We inspect the database itself, so nothing your {city} business relies on is left behind in {country}.',
        'faq' => [
            ['Can WooCommerce variable products be migrated to Shopify?', 'Yes. Attributes become Shopify options and variants; extra attributes beyond three become metafields.'],
            ['What happens to my WordPress blog?', 'Posts are migrated to Shopify blogs, or your WordPress site can remain at a subdomain - we advise based on your {country} SEO.'],
            ['Will WooCommerce customer passwords transfer?', 'Passwords cannot be exported from any platform; {city} customers receive an activation email or reset on first login.'],
        ],
        'cta' => 'Send us your WooCommerce store URL and get a fixed migration quote for {place} within a day.',
    ],

    'magento-to-shopify-migration' => [
        'kw' => 'magento to shopify migration',
        'variants' => ['migrate magento to shopify', 'magento 2 to shopify migration', 'adobe commerce to shopify'],
        'meta' => 'Magento to Shopify migration for {place}: complex catalogues, configurable products, customer groups and B2B pricing moved from Magento 1/2 to Shopify or Shopify Plus.',
        'intro' => 'Magento gives you control at the cost of hosting, patching and expensive development. A <strong class="kw">Magento to Shopify migration in {city}</strong> trades that overhead for a platform your {city} team can run. TopWebExpert migrates Magento 1, Magento 2 and Adobe Commerce stores for {country} merchants, handling configurable products, attribute sets, customer groups, tiered pricing and multi-store setups.',
        'offer' => [
            '<strong>Catalogue migration</strong> - configurable, bundle and grouped products mapped to Shopify products, variants and bundles',
            '<strong>Customer groups and pricing</strong> - tier and group pricing rebuilt with Shopify B2B or discount logic',
            '<strong>Multi-store to Shopify Markets or expansion stores</strong> - the right structure for your {country} operations',
            '<strong>Extension replacement</strong> - each Magento extension matched to a Shopify app or custom build',
            '<strong>URL rewrites and SEO</strong> - Magento URL rewrites mapped to Shopify redirects',
        ],
        'process' => [
            '<strong>Magento audit</strong> - attribute sets, extensions, integrations and data volumes',
            '<strong>Data model design</strong> - how each Magento concept becomes a Shopify concept',
            '<strong>Trial migration and verification</strong> - counts and spot-checks signed off by your {city} team',
            '<strong>Go-live</strong> - final delta migration and switch',
        ],
        'why' => 'Magento migrations are the most complex on Shopify, and generic migration apps flatten your data. Our {city} Magento projects are led by developers who know both platforms, so {country} merchants keep the catalogue structure that makes them money.',
        'faq' => [
            ['Can Magento configurable products move to Shopify?', 'Yes. Up to three configurable attributes become Shopify options; more complex setups use metafields or a bundle structure we design with you.'],
            ['Do I need Shopify Plus after Magento?', 'Larger {city} Magento stores with B2B, multi-store or custom checkout usually do; simpler stores run well on standard plans.'],
            ['How long does a Magento to Shopify migration take?', 'Four to ten weeks for most {country} stores, depending on catalogue complexity.'],
        ],
        'cta' => 'Book a Magento migration audit for your {city} store - we will show you exactly how your data maps to Shopify.',
    ],

    'wordpress-to-shopify-migration' => [
        'kw' => 'wordpress to shopify migration',
        'variants' => ['wordpress to shopify migration service', 'move wordpress site to shopify', 'convert wordpress to shopify'],
        'meta' => 'WordPress to Shopify migration service for {place}: move your WordPress or WooCommerce site to Shopify with content, products, SEO redirects and a new design.',
        'intro' => 'Whether your WordPress site sells through WooCommerce or has outgrown a brochure site, a <strong class="kw">WordPress to Shopify migration in {city}</strong> gives your business a store that is faster, safer and easier to run. TopWebExpert moves WordPress sites for {city} businesses to Shopify: pages, posts, media, products and SEO signals, rebuilt on a Shopify theme designed for {country} customers.',
        'offer' => [
            '<strong>Content migration</strong> - pages, blog posts, images and internal links moved into Shopify',
            '<strong>Products (if WooCommerce)</strong> - full product, customer and order migration',
            '<strong>Design rebuild</strong> - your WordPress design recreated or refreshed as a Shopify theme',
            '<strong>Forms and lead capture</strong> - contact and quote forms rebuilt with Shopify apps',
            '<strong>SEO redirects</strong> - every WordPress URL redirected so {country} rankings are preserved',
        ],
        'process' => [
            '<strong>Site audit</strong> - content inventory, plugins and traffic pages that must be protected',
            '<strong>Content and product import</strong> - into a Shopify development store',
            '<strong>Theme build</strong> - design and templates for your {city} brand',
            '<strong>Launch</strong> - DNS switch, redirect testing and search console updates',
        ],
        'why' => 'WordPress sites carry years of content and links. Losing them in a migration costs {city} businesses traffic for months. We map and redirect every URL and keep your best-performing {country} content intact on Shopify.',
        'faq' => [
            ['Can I keep my WordPress blog and move only the shop to Shopify?', 'Yes. Many {city} businesses keep WordPress at blog.yourdomain and run the store on Shopify; we set up both.'],
            ['Will my contact forms work on Shopify?', 'Yes - forms are rebuilt with Shopify apps and connected to your email or CRM.'],
            ['Does Shopify handle a site with hundreds of pages?', 'Yes. Pages and blogs scale well, and navigation is rebuilt to suit your {country} audience.'],
        ],
        'cta' => 'Ask for a free WordPress to Shopify migration plan for your {city} site.',
    ],

    'bigcommerce-to-shopify-migration' => [
        'kw' => 'bigcommerce to shopify migration',
        'variants' => ['migrate bigcommerce to shopify', 'bigcommerce to shopify plus', 'switch from bigcommerce to shopify'],
        'meta' => 'BigCommerce to Shopify migration for {place}: products, options, customers, orders, redirects and theme moved to Shopify with no downtime by TopWebExpert.',
        'intro' => 'Moving from one hosted platform to another should be smooth, and a <strong class="kw">BigCommerce to Shopify migration in {city}</strong> can be - when product options, customer groups and URL structures are handled properly. TopWebExpert migrates BigCommerce stores for {city} merchants to Shopify or Shopify Plus, preserving data and {country} search rankings and taking advantage of Shopify\'s larger app ecosystem.',
        'offer' => [
            '<strong>Products and options</strong> - BigCommerce option sets and modifiers mapped to Shopify variants and line-item properties',
            '<strong>Customers, groups and orders</strong> - full history with customer group pricing rebuilt',
            '<strong>Redirects</strong> - BigCommerce URL patterns redirected to Shopify equivalents',
            '<strong>Theme rebuild</strong> - Stencil theme recreated as a Shopify theme for your {city} brand',
            '<strong>App replacement</strong> - BigCommerce apps matched with Shopify apps or custom features',
        ],
        'process' => [
            '<strong>Audit and mapping</strong> - options, modifiers and custom fields reviewed',
            '<strong>Trial migration</strong> - verified in a development store by your {city} team',
            '<strong>Theme and apps</strong> - built in parallel',
            '<strong>Cut-over</strong> - delta sync and switch with redirect verification for {country} traffic',
        ],
        'why' => 'BigCommerce modifiers and option sets do not map one-to-one to Shopify, and that is where automated tools break. We design the mapping for each {city} store by hand, so {country} customers see the same buying options after the move.',
        'faq' => [
            ['Can BigCommerce product modifiers be migrated?', 'Yes - as variants where possible and as line-item properties or custom apps where the option logic is more complex.'],
            ['Will my BigCommerce redirects still work?', 'We migrate existing redirects and add new ones for every URL that changes, protecting {country} rankings.'],
            ['How long does the migration take?', 'Two to five weeks for most {city} BigCommerce stores.'],
        ],
        'cta' => 'Get a fixed quote to migrate your {city} BigCommerce store to Shopify.',
    ],

    'shopify-to-shopify-plus-migration' => [
        'kw' => 'shopify to shopify plus migration',
        'variants' => ['upgrade to shopify plus', 'shopify plus upgrade service', 'shopify plus migration'],
        'meta' => 'Shopify to Shopify Plus migration for {place}: plan the upgrade, customise checkout, add Functions, B2B and multi-store, and launch on Plus without disruption.',
        'intro' => 'Upgrading is easy; using Plus well is not. A <strong class="kw">Shopify to Shopify Plus migration in {city}</strong> with TopWebExpert turns the plan change into a real upgrade: customised checkout, Shopify Functions, B2B, expansion stores and automation configured so your {city} store in {country} immediately earns back the higher fee.',
        'offer' => [
            '<strong>Plus business case</strong> - which Plus features will pay for the plan for your {city} store',
            '<strong>Checkout customisation</strong> - checkout extensions, branding and upsells built on day one',
            '<strong>Shopify Functions and Flow</strong> - custom discount, delivery and automation logic',
            '<strong>Expansion stores and Markets</strong> - structure for new {country} regions or brands',
            '<strong>Team enablement</strong> - training on Plus-only tools such as Launchpad and Flow',
        ],
        'process' => [
            '<strong>Readiness review</strong> - current store, apps and processes assessed against Plus capabilities',
            '<strong>Upgrade plan</strong> - features prioritised by revenue impact',
            '<strong>Implementation</strong> - checkout, Functions and integrations built and tested',
            '<strong>Launch</strong> - upgrade timed around your {city} trading calendar',
        ],
        'why' => 'Merchants who upgrade to Plus without a plan pay more for the same store. Our Plus migrations for {country} brands come with a feature roadmap, so the {city} business sees new capability from the first month.',
        'faq' => [
            ['Does upgrading to Shopify Plus require a new store?', 'No. The same store is upgraded; we then add the Plus features and integrations.'],
            ['Is Shopify Plus worth it for a {city} store?', 'Typically once monthly revenue, order volume or B2B/checkout needs justify it - we run the numbers with you.'],
            ['Can you migrate our checkout customisations from scripts?', 'Yes. Legacy Shopify Scripts are rebuilt as Shopify Functions and checkout extensions for {country} stores.'],
        ],
        'cta' => 'Request a Shopify Plus readiness review for your {city} store.',
    ],

    'shopify-seo-services' => [
        'kw' => 'shopify seo services',
        'variants' => ['shopify seo agency', 'shopify seo expert', 'shopify seo company'],
        'meta' => 'Shopify SEO services for {place}: technical SEO, collection and product page optimisation, content and link building that grows organic sales for Shopify stores.',
        'intro' => 'Shopify is SEO-friendly out of the box, but ranking against established {city} competitors takes real work. TopWebExpert\'s <strong class="kw">Shopify SEO services for {city}</strong> stores combine technical fixes only a Shopify developer can make with content and authority building targeted at {country} shoppers ready to buy.',
        'offer' => [
            '<strong>Technical Shopify SEO</strong> - duplicate collection URLs, faceted navigation, speed, schema and crawl control',
            '<strong>Collection and product page optimisation</strong> - titles, descriptions, copy and internal linking at scale',
            '<strong>Keyword and category strategy</strong> - mapping {country} search demand to your catalogue structure',
            '<strong>Content and blog</strong> - buying guides and comparison content that ranks and converts',
            '<strong>Authority building</strong> - digital PR and relevant links from {country} and industry sites',
        ],
        'process' => [
            '<strong>SEO audit</strong> - technical, content and authority gaps ranked by revenue impact',
            '<strong>Foundation fixes</strong> - theme-level SEO issues resolved by our developers',
            '<strong>Monthly optimisation</strong> - pages optimised, content published, links earned',
            '<strong>Reporting</strong> - rankings, organic revenue and next steps for your {city} store each month',
        ],
        'why' => 'Most SEO agencies do not touch Liquid; most Shopify developers do not do SEO. TopWebExpert does both, which is why our {city} clients see technical fixes shipped the same month they are found - not left in a report.',
        'faq' => [
            ['How long before Shopify SEO shows results?', 'Technical fixes often lift {country} rankings within weeks; content and authority work compound over three to six months.'],
            ['Can you fix duplicate content on Shopify collections?', 'Yes. We handle canonical tags, collection/product URL duplication and tag pages at theme level.'],
            ['Do you write product descriptions?', 'Yes - optimised copy for products and collections, written for {city} buyers rather than search engines alone.'],
        ],
        'cta' => 'Request a free Shopify SEO audit for your {city} store.',
    ],

    'shopify-conversion-rate-optimization' => [
        'kw' => 'shopify conversion rate optimization',
        'variants' => ['shopify cro agency', 'shopify cro services', 'increase shopify conversion rate'],
        'meta' => 'Shopify conversion rate optimization for {place}: data-driven CRO audits, A/B testing, product page and checkout improvements that turn traffic into sales.',
        'intro' => 'You are already paying for traffic; <strong class="kw">Shopify conversion rate optimization for {city}</strong> stores makes each visit worth more. TopWebExpert runs CRO programmes for {city} merchants that combine analytics, user behaviour recordings and structured A/B testing with the design and development changes that {country} shoppers respond to.',
        'offer' => [
            '<strong>CRO audit</strong> - funnel analysis, heat-maps and session recordings turned into a ranked test list',
            '<strong>Product page optimisation</strong> - imagery, information hierarchy, trust signals and offers',
            '<strong>Cart and checkout improvements</strong> - shipping clarity, payment options and Plus checkout customisation',
            '<strong>A/B testing programme</strong> - hypotheses tested, winners implemented in the theme',
            '<strong>Post-purchase and retention</strong> - upsells, email flows and repeat-purchase paths',
        ],
        'process' => [
            '<strong>Measure</strong> - analytics and tracking verified so decisions rest on real {country} data',
            '<strong>Diagnose</strong> - where and why {city} visitors leave',
            '<strong>Test</strong> - prioritised experiments run monthly',
            '<strong>Ship</strong> - winning changes built permanently into your theme',
        ],
        'why' => 'A one-point conversion lift on a {city} store doing steady traffic is worth more than most marketing budgets. Our CRO work is done by the developers who implement it, so every winning test becomes a permanent improvement to your {country} store.',
        'faq' => [
            ['What is a good conversion rate for a Shopify store?', 'It varies by category and market; most {country} stores sit between one and three percent, and the best exceed four. We benchmark your store against its own category.'],
            ['Do I need a lot of traffic for CRO?', 'For A/B testing, yes; for audit-led improvements, no - smaller {city} stores gain most from fixing obvious friction first.'],
            ['Is CRO a one-off or ongoing?', 'Both are available: a one-off audit and fixes, or a monthly programme.'],
        ],
        'cta' => 'Find out why your {city} store is not converting - request a CRO audit.',
    ],

    'shopify-speed-optimization' => [
        'kw' => 'shopify speed optimization',
        'variants' => ['shopify speed optimization service', 'shopify page speed', 'shopify store slow loading fix'],
        'meta' => 'Shopify speed optimization for {place}: faster load times, better Core Web Vitals and higher conversion through theme code, image and app optimisation by TopWebExpert.',
        'intro' => 'Every extra second of load time costs a {city} store sales and rankings. <strong class="kw">Shopify speed optimization for {city}</strong> merchants is where TopWebExpert developers dig into the theme, apps and assets that slow a store down and fix them properly - not with a plug-in, but with code. Stores across {country} typically see load times cut by half.',
        'offer' => [
            '<strong>Speed audit</strong> - Lighthouse, Core Web Vitals and real-user data for {country} visitors',
            '<strong>Theme code optimisation</strong> - render-blocking scripts, critical CSS, lazy loading and clean Liquid',
            '<strong>App clean-up</strong> - removing leftover app code and replacing heavy apps with lighter options',
            '<strong>Image and font optimisation</strong> - modern formats, correct sizing and preloading',
            '<strong>Third-party script control</strong> - tracking and chat tools loaded without blocking the page',
        ],
        'process' => [
            '<strong>Baseline</strong> - scores and load times recorded for key {city} templates',
            '<strong>Optimisation on a duplicate theme</strong> - no risk to the live store',
            '<strong>Verification</strong> - before and after comparisons on real devices',
            '<strong>Publish and guidance</strong> - how to keep the store fast as you add content',
        ],
        'why' => 'Speed apps hide symptoms; developers fix causes. Our speed work for {country} merchants removes the reasons a {city} store is slow, so the improvement lasts beyond the next theme update.',
        'faq' => [
            ['Why is my Shopify store slow?', 'Usually a mix of app scripts, un-optimised images, heavy theme code and third-party tags - the audit shows which for your {city} store.'],
            ['Can you guarantee a Lighthouse score?', 'We guarantee measurable improvement against your baseline; scores depend on apps you choose to keep.'],
            ['Will speed optimisation change how my store looks?', 'No. Design stays the same; only the underlying code and asset delivery change.'],
        ],
        'cta' => 'Send your store URL for a free speed check - {city} merchants get a report within one business day.',
    ],

    'shopify-maintenance-services' => [
        'kw' => 'shopify maintenance services',
        'variants' => ['shopify support services', 'shopify store maintenance', 'shopify support and maintenance'],
        'meta' => 'Shopify maintenance and support services for {place}: monthly updates, bug fixes, theme changes, app management and monitoring from TopWebExpert.',
        'intro' => 'A Shopify store is never finished. <strong class="kw">Shopify maintenance services for {city}</strong> businesses give you a developer on call for theme changes, app updates, bug fixes and seasonal campaigns without hiring in-house. TopWebExpert supports {city} merchants across {country} with fixed monthly plans and same-day response.',
        'offer' => [
            '<strong>Monthly development hours</strong> - theme edits, new sections, landing pages and fixes',
            '<strong>App and theme updates</strong> - tested before they reach your live store',
            '<strong>Monitoring</strong> - uptime, speed and checkout checks with alerts',
            '<strong>Campaign support</strong> - sale banners, promotions and seasonal changes delivered on schedule for {country} events',
            '<strong>Priority support</strong> - a named contact and guaranteed response times for your {city} team',
        ],
        'process' => [
            '<strong>Onboarding audit</strong> - we learn your store, apps and integrations',
            '<strong>Monthly plan</strong> - hours allocated to your priorities',
            '<strong>Ticket-based delivery</strong> - requests tracked, estimated and reported',
            '<strong>Monthly review</strong> - what was done, what is next',
        ],
        'why' => 'Waiting a week for a freelancer to fix a broken checkout is expensive. Our maintenance clients in {city} get a team that already knows their store, so fixes ship in hours and improvements ship every month across {country}.',
        'faq' => [
            ['What is included in Shopify maintenance?', 'Development hours, updates, monitoring and support - plans for {city} stores are sized from a few hours a month upwards.'],
            ['Can I roll unused hours over?', 'Yes, within the quarter.'],
            ['Do you support stores you did not build?', 'Yes - most maintenance clients in {country} come to us with stores built elsewhere.'],
        ],
        'cta' => 'Choose a Shopify maintenance plan for your {city} store and get a developer on call this week.',
    ],

    'shopify-checkout-customization' => [
        'kw' => 'shopify checkout customization',
        'variants' => ['shopify checkout extensibility', 'shopify checkout upsell', 'custom shopify checkout'],
        'meta' => 'Shopify checkout customization for {place}: checkout UI extensions, upsells, custom fields, branding, validation and Functions built for Shopify Plus by TopWebExpert.',
        'intro' => 'Checkout is where the money changes hands, and on Shopify Plus it is fully customisable. <strong class="kw">Shopify checkout customization for {city}</strong> stores with TopWebExpert means checkout UI extensions, branded layouts, upsells, custom fields, delivery rules and validation built with Shopify\'s current checkout extensibility - future-proof and fast for {country} shoppers.',
        'offer' => [
            '<strong>Checkout UI extensions</strong> - upsells, trust banners, gift options, delivery notes and custom fields',
            '<strong>Checkout branding</strong> - colours, typography and layout matched to your {city} brand',
            '<strong>Shopify Functions</strong> - custom discounts, delivery options and payment method rules',
            '<strong>Validation and B2B rules</strong> - address checks, order minimums and account-based logic for {country}',
            '<strong>Thank-you and order status pages</strong> - post-purchase upsells and tracking experiences',
        ],
        'process' => [
            '<strong>Checkout review</strong> - current flow, drop-off points and what Plus allows',
            '<strong>Extension design</strong> - what appears where, and why',
            '<strong>Build and test</strong> - extensions built and tested in a development store',
            '<strong>Deploy and measure</strong> - conversion and AOV tracked after launch for your {city} store',
        ],
        'why' => 'Legacy checkout.liquid customisations are being retired; extensions are the future. We build checkout customisations for {country} Plus merchants exclusively on the supported extensibility stack, so a {city} store never has to rebuild it again.',
        'faq' => [
            ['Do I need Shopify Plus to customise checkout?', 'Full checkout customisation requires Plus; on standard plans we customise the cart, thank-you page and order status page.'],
            ['Can you migrate our checkout.liquid changes?', 'Yes - they are rebuilt as checkout extensions and Functions.'],
            ['Can checkout upsells be targeted?', 'Yes, by cart contents, customer tags, market or {country} region.'],
        ],
        'cta' => 'Show us your checkout and get a scoped quote for customisation for your {city} store.',
    ],

    'outsource-shopify-development' => [
        'kw' => 'outsource shopify development',
        'variants' => ['offshore shopify developer', 'shopify developer for hire remote', 'shopify development outsourcing'],
        'meta' => 'Outsource Shopify development to TopWebExpert from {place}: offshore Shopify developers with overlap hours, clear pricing, QA and full code ownership.',
        'intro' => 'Businesses in {city} <strong class="kw">outsource Shopify development</strong> to TopWebExpert because it removes the hardest part of growing a store - finding and keeping good developers. Our offshore Shopify team works in overlap with {country} business hours, communicates in writing, follows your process and delivers code you own, at a fraction of local {city} agency cost.',
        'offer' => [
            '<strong>Project-based outsourcing</strong> - fixed-scope builds, migrations and features',
            '<strong>Offshore developers on retainer</strong> - monthly capacity for {city} brands and agencies',
            '<strong>Overlap hours</strong> - working time aligned with {country} for daily stand-ups and same-day answers',
            '<strong>Process and QA</strong> - Git, staging themes, code review and tested deliveries',
            '<strong>IP and confidentiality</strong> - NDA, full code ownership and no reuse of your work',
        ],
        'process' => [
            '<strong>Requirements</strong> - written scope or a backlog you already have',
            '<strong>Team assignment</strong> - developer and project lead introduced to your {city} team',
            '<strong>Delivery</strong> - sprints with demos, or ticket-based work for retainers',
            '<strong>Reporting</strong> - weekly progress and monthly hours reports',
        ],
        'why' => 'Outsourcing fails on communication and quality, not on skill. TopWebExpert answers both with a named project lead, written updates in {country} hours and a QA step before anything is handed to a {city} client.',
        'faq' => [
            ['How does time-zone difference work?', 'Our teams schedule overlap hours with {country}; urgent issues are handled through an escalation channel.'],
            ['Who owns the code when I outsource?', 'You do - every line, from the first commit, belongs to your {city} business.'],
            ['Can we start with a small task?', 'Yes. Most outsourcing relationships start with a single task to test the fit.'],
        ],
        'cta' => 'Outsource your next Shopify project from {place} - request rates and availability.',
    ],

    'white-label-shopify-development' => [
        'kw' => 'white label shopify development',
        'variants' => ['white label shopify agency', 'shopify development partner for agencies', 'shopify subcontracting'],
        'meta' => 'White label Shopify development for agencies in {place}: TopWebExpert builds Shopify stores, themes and apps under your brand with NDA, agency pricing and reliable delivery.',
        'intro' => 'Agencies and freelancers in {city} use TopWebExpert as their <strong class="kw">white label Shopify development</strong> partner: you sell and manage the client, we build under your brand. Reliable capacity for {city} agencies across {country}, with NDA protection, agency pricing and delivery your clients will attribute to you.',
        'offer' => [
            '<strong>White label store builds</strong> - full Shopify and Plus builds delivered as your work',
            '<strong>Theme and feature development</strong> - overflow capacity for your existing {city} clients',
            '<strong>Migrations and integrations</strong> - specialist work you can now offer',
            '<strong>Support and maintenance</strong> - ongoing client retainers fulfilled by us, billed by you',
            '<strong>Agency tooling</strong> - shared boards, staging access and reporting you can forward to clients',
        ],
        'process' => [
            '<strong>Partnership setup</strong> - NDA, rate card and communication rules',
            '<strong>Per-project briefs</strong> - you send the brief, we return a fixed estimate you can mark up',
            '<strong>Build under your brand</strong> - no TopWebExpert branding anywhere in deliverables',
            '<strong>Handover</strong> - documented delivery your {country} account managers can present',
        ],
        'why' => 'White label only works when the partner never embarrasses you. Our agency partners in {country} get consistent developers, written estimates that hold, and a team that understands it is invisible to your {city} clients.',
        'faq' => [
            ['Will our clients know TopWebExpert is involved?', 'No. All communication goes through your {city} agency and deliverables carry your brand.'],
            ['Do you offer agency pricing?', 'Yes - partner rates for agencies that send regular work, with volume tiers.'],
            ['Can you join client calls as our team?', 'Yes, when you want technical backup, introduced as part of your {country} team.'],
        ],
        'cta' => 'Set up a white label Shopify partnership for your {city} agency - ask for the partner rate card.',
    ],

    'dedicated-shopify-developer' => [
        'kw' => 'dedicated shopify developer',
        'variants' => ['hire dedicated shopify developer', 'shopify developer monthly retainer', 'full time shopify developer'],
        'meta' => 'Hire a dedicated Shopify developer for {place}: full-time or part-time developers working only on your store, with a project lead, reporting and flexible monthly contracts.',
        'intro' => 'A <strong class="kw">dedicated Shopify developer for your {city} business</strong> works only on your store, learns it inside out and becomes part of your team - without recruitment, payroll or the risk of a solo freelancer. TopWebExpert provides dedicated developers to {city} merchants and agencies across {country} on flexible monthly contracts.',
        'offer' => [
            '<strong>Full-time or part-time developer</strong> - 80 to 160 hours a month reserved for your store',
            '<strong>Project lead included</strong> - planning, QA and a second pair of eyes at no extra cost',
            '<strong>Your tools, your process</strong> - Slack, Jira, GitHub or whatever your {city} team uses',
            '<strong>Skill coverage</strong> - Liquid, JavaScript, app development, integrations and Plus features',
            '<strong>Replacement guarantee</strong> - if the fit is wrong, we replace the developer',
        ],
        'process' => [
            '<strong>Needs discussion</strong> - skills, hours and the work backlog',
            '<strong>Developer match and interview</strong> - you meet the developer before starting',
            '<strong>Onboarding week</strong> - store, codebase and processes learned',
            '<strong>Ongoing delivery</strong> - daily updates, weekly demos and monthly reports for your {country} team',
        ],
        'why' => 'Rotating freelancers means re-explaining your store every month. A dedicated developer builds knowledge that compounds - after three months they know your {city} store better than anyone, and every task gets faster.',
        'faq' => [
            ['What is the minimum contract?', 'One month, renewing monthly - most {country} clients stay for a year or more.'],
            ['Can the developer work in our time zone?', 'Yes - dedicated developers align their hours with {city} business hours.'],
            ['Can we scale up to a team?', 'Yes. Add developers, a designer or a QA engineer as the workload grows.'],
        ],
        'cta' => 'Hire a dedicated Shopify developer for your {city} store - request profiles and availability.',
    ],

    'shopify-liquid-developer' => [
        'kw' => 'shopify liquid developer',
        'variants' => ['liquid theme developer', 'shopify liquid expert', 'shopify liquid customization'],
        'meta' => 'Expert Shopify Liquid developer for {place}: custom sections, templates, metafield logic and theme features written in clean, fast Liquid by TopWebExpert.',
        'intro' => 'Liquid is the language every Shopify theme is written in, and a real <strong class="kw">Shopify Liquid developer in {city}</strong> can make a theme do almost anything without another app. TopWebExpert Liquid developers build custom sections, templates, product logic and dynamic content for {city} merchants and agencies across {country}.',
        'offer' => [
            '<strong>Custom sections and blocks</strong> - drag-and-drop components built to your design',
            '<strong>Complex product logic</strong> - variant rules, bundles, personalisation and conditional content',
            '<strong>Metafield and metaobject templates</strong> - structured content rendered anywhere in the theme',
            '<strong>Theme refactoring</strong> - cleaning years of Liquid edits into maintainable code',
            '<strong>Agency Liquid support</strong> - subcontract Liquid work for your {city} agency',
        ],
        'process' => [
            '<strong>Spec</strong> - what the section or logic must do, with examples',
            '<strong>Build on a duplicate theme</strong> - versioned in Git',
            '<strong>Theme Check and testing</strong> - validated and tested across templates and devices',
            '<strong>Delivery</strong> - documented so your {country} team can use and edit it',
        ],
        'why' => 'Good Liquid is fast, readable and survives theme updates; bad Liquid is why {city} stores slow down and break. Our developers write Liquid the way Shopify\'s own Dawn theme does, so {country} merchants inherit a theme they can keep building on.',
        'faq' => [
            ['Can Liquid replace apps?', 'Often. Many {city} stores pay monthly for apps that a few hundred lines of Liquid replace permanently.'],
            ['Do you work on any theme?', 'Yes - Dawn, premium themes and fully custom themes for {country} stores.'],
            ['Do you offer hourly Liquid work?', 'Yes. Small tasks are billed hourly with a minimum of one hour.'],
        ],
        'cta' => 'Send the Liquid task you need done and get an estimate for your {city} store today.',
    ],

    'shopify-api-integration' => [
        'kw' => 'shopify api integration',
        'variants' => ['shopify integration services', 'shopify third party integration', 'shopify custom integration'],
        'meta' => 'Shopify API integration services for {place}: connect Shopify to ERP, CRM, WMS, marketplaces, accounting and custom systems with reliable, monitored integrations.',
        'intro' => 'Your store is one system among many. <strong class="kw">Shopify API integration for {city}</strong> businesses connects Shopify to the tools that run the rest of the company - ERP, CRM, warehouse, accounting, marketplaces, POS - so orders, stock and customers flow automatically. TopWebExpert builds and maintains integrations for {city} merchants throughout {country}.',
        'offer' => [
            '<strong>Admin and Storefront API integrations</strong> - orders, products, inventory, customers and fulfilment sync',
            '<strong>Webhook-driven automation</strong> - real-time events pushed to your other systems',
            '<strong>Middleware and iPaaS</strong> - direct builds or platforms such as Make, Zapier and Celigo when appropriate',
            '<strong>Marketplace and channel sync</strong> - Amazon, eBay and {country} marketplaces connected to Shopify inventory',
            '<strong>Monitoring and error handling</strong> - retries, alerts and logs so integrations do not fail silently',
        ],
        'process' => [
            '<strong>Integration mapping</strong> - systems, data fields, direction and frequency documented',
            '<strong>Build</strong> - API scopes, rate limits and versioning handled correctly',
            '<strong>Test with real data</strong> - in a development store connected to sandbox systems',
            '<strong>Deploy and monitor</strong> - with dashboards for your {city} operations team',
        ],
        'why' => 'Integrations are judged by what happens when something goes wrong. Ours are built with queues, retries and alerts, so a {city} store in {country} does not discover a sync failure from an angry customer.',
        'faq' => [
            ['Which systems can you integrate with Shopify?', 'Any system with an API or file exchange - ERPs, CRMs, WMS, accounting, PIM, marketplaces and custom software used by {country} businesses.'],
            ['Real-time or scheduled sync?', 'Both, chosen per data type: orders in real time, product updates on a schedule, for example.'],
            ['Do you maintain the integration afterwards?', 'Yes - {city} clients keep us on a support plan covering API changes and monitoring.'],
        ],
        'cta' => 'List the systems your {city} store needs connected and get an integration plan and quote.',
    ],

    'shopify-erp-integration' => [
        'kw' => 'shopify erp integration',
        'variants' => ['shopify netsuite integration', 'shopify sap integration', 'shopify dynamics 365 integration'],
        'meta' => 'Shopify ERP integration for {place}: connect Shopify with NetSuite, SAP, Microsoft Dynamics, Odoo, Tally or custom ERPs for orders, inventory, pricing and fulfilment sync.',
        'intro' => 'When the ERP is the source of truth, the store has to keep up. <strong class="kw">Shopify ERP integration for {city}</strong> companies keeps products, prices, stock, orders, customers and invoices synchronised between Shopify and NetSuite, SAP Business One, Microsoft Dynamics 365, Odoo, Tally or an in-house ERP. TopWebExpert delivers ERP integrations for {city} manufacturers, distributors and retailers across {country}.',
        'offer' => [
            '<strong>NetSuite, SAP, Dynamics, Odoo and Tally connectors</strong> - built directly or on proven middleware',
            '<strong>Order and fulfilment flow</strong> - Shopify orders into the ERP, shipments and tracking back to Shopify',
            '<strong>Inventory and pricing sync</strong> - multi-location stock and customer-specific pricing for {country} B2B',
            '<strong>Customer and invoice sync</strong> - accounts, credit terms and invoices aligned',
            '<strong>Exception handling</strong> - dashboards and alerts for failed or mismatched records',
        ],
        'process' => [
            '<strong>Data and process mapping</strong> - every field and business rule documented with your {city} finance and operations teams',
            '<strong>Architecture</strong> - direct API, middleware or hybrid, chosen for cost and reliability',
            '<strong>Build and parallel testing</strong> - run against the live ERP sandbox',
            '<strong>Go-live and support</strong> - with a hyper-care period and ongoing monitoring',
        ],
        'why' => 'ERP integrations fail on business rules, not technology - tax codes, units of measure, partial shipments. Our {country} ERP projects start with mapping those rules with the people who own them in your {city} business, so the integration matches how you actually operate.',
        'faq' => [
            ['Which ERPs do you integrate with Shopify?', 'NetSuite, SAP Business One and S/4HANA, Microsoft Dynamics 365, Odoo, Tally and custom ERPs used by {country} companies.'],
            ['Can B2B pricing from the ERP show in Shopify?', 'Yes - customer-specific price lists sync to Shopify B2B catalogs or a wholesale app.'],
            ['How long does an ERP integration take?', 'Six to twelve weeks is typical for a {city} company, depending on the ERP and number of flows.'],
        ],
        'cta' => 'Tell us your ERP and get an integration roadmap for your {city} Shopify store.',
    ],

    'shopify-multi-store-setup' => [
        'kw' => 'shopify multi store setup',
        'variants' => ['shopify markets setup', 'shopify expansion store', 'multiple shopify stores management'],
        'meta' => 'Shopify multi-store setup for {place}: Shopify Markets, expansion stores, multi-currency, multi-language and shared inventory configured for international and multi-brand selling.',
        'intro' => 'Selling in more than one country, currency or brand raises a structural question: one store with Shopify Markets, or several stores? <strong class="kw">Shopify multi-store setup for {city}</strong> businesses with TopWebExpert starts by answering that properly, then builds the structure - markets, expansion stores, currencies, languages, duties and shared inventory - for a {country} brand going international.',
        'offer' => [
            '<strong>Shopify Markets configuration</strong> - currencies, languages, domains, pricing and duties per market',
            '<strong>Expansion stores</strong> - separate stores for regions or brands with synced products and inventory',
            '<strong>Translation and localisation</strong> - content, checkout and notifications in each market\'s language',
            '<strong>International payments and shipping</strong> - gateways, rates and tax settings for each {country} export market',
            '<strong>Central management</strong> - reporting and workflows across all stores',
        ],
        'process' => [
            '<strong>Expansion strategy</strong> - target markets, product ranges and legal requirements',
            '<strong>Structure decision</strong> - Markets vs expansion stores, documented with trade-offs',
            '<strong>Configuration and build</strong> - stores, markets, inventory sync and localisation',
            '<strong>Launch by market</strong> - phased go-live with testing from each region',
        ],
        'why' => 'Choosing the wrong structure is expensive to undo. We have set up single-store Markets and twenty-store enterprises for {country} brands, and we advise {city} merchants on the option that fits their operations rather than the one that bills the most.',
        'faq' => [
            ['Should I use Shopify Markets or separate stores?', 'Markets suits one catalogue with localised pricing; separate stores suit distinct catalogues, teams or legal entities. We assess this for your {city} business.'],
            ['Can inventory be shared across stores?', 'Yes, with inventory sync apps or custom integration.'],
            ['Do you handle duties and taxes for international selling?', 'Yes - Markets Pro or configured duty and tax settings per {country} destination.'],
        ],
        'cta' => 'Planning to sell beyond {city}? Ask for a multi-store and Markets consultation.',
    ],

    'shopify-store-audit' => [
        'kw' => 'shopify store audit',
        'variants' => ['free shopify audit', 'shopify site review', 'shopify store review'],
        'meta' => 'Professional Shopify store audit for {place}: conversion, speed, SEO, theme code and app review with a prioritised action plan. Free initial audit from TopWebExpert.',
        'intro' => 'Before spending on ads, a redesign or new apps, find out what is actually holding your store back. A <strong class="kw">Shopify store audit for {city}</strong> merchants by TopWebExpert reviews conversion, speed, SEO, theme quality, apps and analytics, and returns a prioritised plan your {city} team can act on - whether or not you hire us to do it in {country}.',
        'offer' => [
            '<strong>Conversion audit</strong> - funnel, product pages, cart, checkout and trust signals',
            '<strong>Speed and Core Web Vitals</strong> - measured for {country} mobile visitors',
            '<strong>SEO audit</strong> - technical issues, content gaps and duplicate URLs',
            '<strong>Theme and app review</strong> - code quality, leftover app code and unnecessary subscriptions',
            '<strong>Analytics and tracking check</strong> - GA4, pixels and Shopify reports validated',
        ],
        'process' => [
            '<strong>Free initial audit</strong> - the top issues on your {city} store, delivered in one business day',
            '<strong>Full audit (optional)</strong> - a complete scored report with fixes ranked by impact',
            '<strong>Walkthrough call</strong> - findings explained, questions answered',
            '<strong>Action plan</strong> - what to fix first, estimated cost and expected gain',
        ],
        'why' => 'Audits from marketing agencies sell marketing; audits from app vendors sell apps. Ours are written by developers and CRO specialists with no product to push, which is why {country} merchants use the report to brief whichever partner they choose.',
        'faq' => [
            ['Is the Shopify store audit really free?', 'The initial audit is free for {city} stores; the full scored report is a fixed-price service.'],
            ['What do I need to provide?', 'Your store URL - and collaborator access if you want the full theme and analytics review.'],
            ['Will you fix what you find?', 'If you want us to - many {country} merchants use the audit as a standalone report.'],
        ],
        'cta' => 'Request your free Shopify store audit for {place} now.',
    ],

    'shopify-one-product-store-design' => [
        'kw' => 'shopify one product store design',
        'variants' => ['single product shopify store', 'one product store design', 'shopify landing page store'],
        'meta' => 'Shopify one product store design for {place}: high-converting single-product stores with landing-page style layouts, upsells and fast checkout by TopWebExpert.',
        'intro' => 'A one-product store lives or dies on its product page. <strong class="kw">Shopify one product store design for {city}</strong> founders by TopWebExpert builds the store as a long-form landing page - hero, benefits, proof, offer, FAQ, checkout - engineered for cold traffic from {country} and international ads.',
        'offer' => [
            '<strong>Landing-page style home and product page</strong> - the whole story on one scroll',
            '<strong>Offer structure</strong> - bundles, quantity breaks and post-purchase upsells built in',
            '<strong>Trust and proof</strong> - reviews, UGC, guarantees and comparison sections',
            '<strong>Speed for paid traffic</strong> - a lean theme that loads instantly on {country} mobile networks',
            '<strong>Tracking</strong> - Meta, TikTok and Google pixels with server-side events',
        ],
        'process' => [
            '<strong>Product and offer workshop</strong> - positioning, price, bundles and guarantees',
            '<strong>Page design</strong> - mobile-first, section by section',
            '<strong>Build and app setup</strong> - upsells, reviews and tracking configured',
            '<strong>Launch-ready hand-over</strong> - test orders placed and ad pixels verified for {city} campaigns',
        ],
        'why' => 'Most one-product stores are built from a generic theme and convert poorly. Our {city} single-product builds are designed like sales pages, because that is what a one-product store in {country} is.',
        'faq' => [
            ['How fast can a one product store be built?', 'Seven to ten days for most {city} founders, product content permitting.'],
            ['Can it grow into a multi-product store later?', 'Yes - the theme supports collections, so adding products in {country} later is simple.'],
            ['Do you help with the product copy?', 'Yes - conversion copy for the product page is included or available as an add-on.'],
        ],
        'cta' => 'Launching a single product in {city}? Get a fixed quote for a one product Shopify store.',
    ],

    'shopify-dropshipping-store-design' => [
        'kw' => 'shopify dropshipping store design',
        'variants' => ['dropshipping store setup', 'shopify dropshipping store development', 'dropshipping website design'],
        'meta' => 'Shopify dropshipping store design and setup for {place}: niche store design, supplier app integration, product import and conversion-ready pages by TopWebExpert.',
        'intro' => 'Dropshipping is competitive, and a store that looks like every other dropshipping store loses. <strong class="kw">Shopify dropshipping store design for {city}</strong> entrepreneurs by TopWebExpert creates a branded, trustworthy storefront with supplier apps integrated, products imported cleanly and pages built to convert paid traffic from {country} and worldwide.',
        'offer' => [
            '<strong>Niche store design</strong> - a brand identity, not a supplier catalogue',
            '<strong>Supplier and fulfilment app setup</strong> - DSers, Zendrop, CJ, Spocket or your preferred supplier connected',
            '<strong>Product import and clean-up</strong> - titles, descriptions, images and variants made presentable',
            '<strong>Conversion features</strong> - urgency, bundles, reviews and upsells configured responsibly',
            '<strong>Policies and trust pages</strong> - shipping, returns and legal pages written for {country} buyers',
        ],
        'process' => [
            '<strong>Niche and supplier selection support</strong> - if you are still deciding',
            '<strong>Design and build</strong> - typically one week for a {city} dropshipping store',
            '<strong>Product setup</strong> - initial catalogue imported and optimised',
            '<strong>Launch checklist</strong> - payments, pixels and test orders verified',
        ],
        'why' => 'We are honest with {city} dropshippers: the store is the easy part, and we build it properly and fast so your budget and time go into products and marketing in {country}.',
        'faq' => [
            ['How much does a dropshipping store cost?', 'Dropshipping store packages are our most affordable builds, priced fixed for {city} founders.'],
            ['Which supplier apps do you support?', 'All major ones - and we advise which suits your {country} shipping expectations.'],
            ['Can you build a general store?', 'We can, but niche stores convert better and we will say so.'],
        ],
        'cta' => 'Get a fixed-price dropshipping store built for {place} this week.',
    ],

    'shopify-fashion-store-design' => [
        'kw' => 'shopify fashion store design',
        'variants' => ['shopify clothing store design', 'fashion ecommerce website shopify', 'apparel store shopify development'],
        'meta' => 'Shopify fashion store design for {place}: clothing and apparel stores with lookbooks, size guides, filters, variant swatches and editorial design by TopWebExpert.',
        'intro' => 'Fashion sells on imagery, sizing confidence and effortless browsing. <strong class="kw">Shopify fashion store design for {city}</strong> brands by TopWebExpert delivers editorial layouts, colour and size swatches, lookbooks, smart filters and size guides - the details that turn browsers into buyers for clothing, footwear and accessory brands across {country}.',
        'offer' => [
            '<strong>Editorial store design</strong> - campaign-led homepages and collection pages that feel like a brand, not a marketplace',
            '<strong>Variant swatches and size guides</strong> - reducing returns for {country} shoppers',
            '<strong>Lookbooks and shop-the-look</strong> - outfit-based merchandising',
            '<strong>Filtering and search</strong> - by size, colour, fit, fabric and price',
            '<strong>Returns and exchanges flow</strong> - self-service returns configured for {city} customers',
        ],
        'process' => [
            '<strong>Brand and range review</strong> - collections, seasons and merchandising rules',
            '<strong>Design</strong> - homepage, collection, product, lookbook and campaign templates',
            '<strong>Build</strong> - theme development with fashion-specific features',
            '<strong>Launch</strong> - timed to your {city} collection drop',
        ],
        'why' => 'Fashion stores have specific needs that generic Shopify builds ignore - sizing, colourways, seasonal merchandising, returns. Our {country} fashion clients get a store built around those needs by a team that has launched apparel brands in {city} and beyond.',
        'faq' => [
            ['Can you integrate size-recommendation tools?', 'Yes - fit finders and size charts are configured per {country} sizing standard.'],
            ['Do you support pre-orders and drops?', 'Yes, including countdowns, waitlists and limited releases for {city} launches.'],
            ['Can you connect our POS for the boutique?', 'Yes - Shopify POS unifies the {city} store and online inventory.'],
        ],
        'cta' => 'Launching or refreshing a fashion brand in {city}? Book a store design consultation.',
    ],

    ];
    return $t;
}

require_once __DIR__ . '/functions.php'; // location_label()

// Render the full page body for a templated service. Returns null when no template exists.
function service_page_html($slug, $serviceName, $city, $country) {
    $t = service_templates()[$slug] ?? null;
    if (!$t) return null;
    $place = location_label($city, $country);
    $fill = function ($s) use ($city, $country, $place, $serviceName, $t) {
        return str_replace(['{city}', '{country}', '{place}', '{name}', '{kw}'], [$city, $country, $place, $serviceName, $t['kw']], $s);
    };
    $li = fn($items) => implode('', array_map(fn($i) => '<li>' . $fill($i) . '</li>', $items));
    $faq = '';
    foreach ($t['faq'] as [$q, $a]) $faq .= '<h3>' . $fill($q) . "</h3>\n<p>" . $fill($a) . "</p>\n";
    $industries = '<ul class="tag-list"><li>Fashion &amp; Apparel</li><li>Beauty &amp; Cosmetics</li><li>Health &amp; Supplements</li><li>Home &amp; Furniture</li>'
        . '<li>Electronics &amp; Gadgets</li><li>Food &amp; Beverage</li><li>Jewellery &amp; Accessories</li><li>Sports &amp; Outdoors</li>'
        . '<li>B2B &amp; Wholesale</li><li>Subscription Boxes</li><li>Digital Products</li><li>Dropshipping Brands</li></ul>';

    return "<h2>{$fill('Professional {name} for {city} businesses')}</h2>\n<p>{$fill($t['intro'])}</p>\n"
        . "<h2>{$fill('What our {name} service in {city} includes')}</h2>\n<ul class=\"check-list\">{$li($t['offer'])}</ul>\n"
        . "<h2>{$fill('How we deliver {name} for {city} businesses')}</h2>\n<ol class=\"steps\">{$li($t['process'])}</ol>\n"
        . "<h2>{$fill('Why {city} brands choose TopWebExpert')}</h2>\n<p>{$fill($t['why'])}</p>\n"
        . "<h2>{$fill('Shopify industries we serve in {city}')}</h2>\n<p>{$fill('Our {kw} work covers every kind of Shopify merchant in {city}:')}</p>\n{$industries}\n"
        . "<h2>{$fill('{name} in {city} - frequently asked questions')}</h2>\n{$faq}"
        . "<h2>{$fill('Get started with {name} in {city}')}</h2>\n<p>{$fill($t['cta'])}</p>";
}

// Meta tags for a templated service page. Returns [meta_title, meta_description, meta_keywords].
function service_page_meta($slug, $serviceName, $city, $country) {
    $t = service_templates()[$slug] ?? null;
    if (!$t) return null;
    $place = location_label($city, $country);
    $kwTitle = ucwords($t['kw']);
    $title = "$serviceName in $city | $kwTitle $city - TopWebExpert";
    $desc = str_replace(['{city}', '{country}', '{place}'], [$city, $country, $place], $t['meta']);
    $keys = [$t['kw'] . " $city", $t['kw'] . " in $city"];
    foreach ($t['variants'] as $v) $keys[] = "$v $city";
    $keys[] = $t['kw'] . " $country";
    return [substr($title, 0, 255), substr($desc, 0, 255), substr(implode(', ', $keys), 0, 255)];
}
