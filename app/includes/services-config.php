<?php
// Master list of services: slug => [name, default image]
//
// Routing note: /{service-slug}-{city-slug} is matched longest-slug-first, so a
// service slug may share a prefix with another ("shopify-developer" /
// "shopify-development" / "shopify-development-company") as long as no slug is
// exactly another slug plus "-" plus a city slug.
require_once __DIR__ . '/service-templates.php';

function services_list() {
    return [
        'search-engine-optimization' => ['Search Engine Optimization', 'seo-services.png'],
        'social-media-optimization' => ['Social Media Optimization', 'smo-drive-traffic.png'],
        'pay-per-click' => ['Pay-Per-Click (PPC)', 'ppc-management-service.png'],
        'whatsapp-marketing' => ['WhatsApp Marketing', 'whatsapp-direct-communication.png'],
        'sms-marketing' => ['SMS Marketing', 'target-message.png'],
        'content-marketing' => ['Content Marketing', 'customize-content-marketing.png'],
        'web-development-company' => ['Website Designing & Development', 'ecommerce-website-development.png'],
        'graphic-designing' => ['Graphic Designing', 'graphic-design-visual-communication.png'],
        'google-marketing' => ['Google Ads Management', 'google-ads-lead-generation.png'],
        'facebook-marketing' => ['Facebook Ads Management', 'facebook-ads-management.png'],
        'ecommerce-website-development-company' => ['E-Commerce Website Development', 'ecommerce-optimization.png'],
        'website-maintenance-services' => ['Website Maintenance', 'professional-website-maintenance-service.png'],
        'local-seo' => ['Local SEO', 'leading-seo-company.png'],
        'google-my-business' => ['Google Business Profile Optimization', 'google-ads-improvement.png'],
        'email-marketing' => ['Email Marketing', 'target-message.png'],
        'youtube-marketing' => ['YouTube Marketing', 'social-media-maximize-visibility.png'],
        'instagram-marketing' => ['Instagram Marketing', 'social-media-brand-engagement.png'],
        'linkedin-marketing' => ['LinkedIn Marketing', 'smo-industry-trend.png'],
        'influencer-marketing' => ['Influencer Marketing', 'Brand-Awareness.png'],
        'online-reputation-management' => ['Online Reputation Management', 'high-conversion-rate.png'],
        'lead-generation' => ['Lead Generation Services', 'Lead-Generation.png'],
        'wordpress-development' => ['WordPress Development', 'responsive-and-mobile-friendly-design.png'],
        'shopify-development' => ['Shopify Development', 'ecommerce-identity.png'],
        'app-development' => ['Mobile App Development', 'APP-Engagement.png'],
        'ui-ux-design' => ['UI/UX Design', 'interactive-design.png'],
        'landing-page-design' => ['Landing Page Design', 'Conversion-Optimization.png'],
        'logo-design' => ['Logo Design & Branding', 'graphic-make-brand-identity.png'],
        'software-development' => ['Custom Software Development', 'customize-solutions-for-your-bussiness.png'],
        'real-estate-marketing' => ['Real Estate Digital Marketing', 'Lead-Generation.png'],

        // ---- Shopify keyword services (September 2026, from the Shopify Agency Keyword
        //      Research pack). Page copy for each lives in service-templates.php. ----
        'shopify-developer' => ['Shopify Developer', 'ecommerce-website-development.png'],
        'shopify-agency' => ['Shopify Agency', 'ecommerce-identity.png'],
        'shopify-development-company' => ['Shopify Development Company', 'ecommerce-optimization.png'],
        'shopify-expert' => ['Shopify Expert & Consultant', 'customize-solutions-for-your-bussiness.png'],
        'shopify-web-design' => ['Shopify Web Design', 'the-art-of-engaging-design.png'],
        'shopify-redesign' => ['Shopify Store Redesign', 'interactive-design.png'],
        'shopify-theme-development' => ['Shopify Theme Development', 'responsive-and-mobile-friendly-design.png'],
        'shopify-app-development' => ['Shopify App Development', 'APP-Engagement.png'],
        'shopify-store-setup' => ['Shopify Store Setup', 'ecommerce-analysis.png'],
        'shopify-plus-agency' => ['Shopify Plus Agency', 'high-conversion-rate.png'],
        'shopify-b2b-development' => ['Shopify B2B & Wholesale Development', 'Lead-Generation.png'],
        'shopify-headless-development' => ['Shopify Headless Development', 'Testing-Optimization.png'],
        'shopify-migration' => ['Shopify Migration Services', 'automate-marketing.png'],
        'woocommerce-to-shopify-migration' => ['WooCommerce to Shopify Migration', 'seo-optimize-ecommerce-website.png'],
        'magento-to-shopify-migration' => ['Magento to Shopify Migration', 'seo-optimize-website.png'],
        'wordpress-to-shopify-migration' => ['WordPress to Shopify Migration', 'why-website-maintenance-matter.png'],
        'bigcommerce-to-shopify-migration' => ['BigCommerce to Shopify Migration', 'Tracking-Analytics.png'],
        'shopify-to-shopify-plus-migration' => ['Shopify to Shopify Plus Migration', 'Reporting.png'],
        'shopify-seo-services' => ['Shopify SEO Services', 'seo-services.png'],
        'shopify-conversion-rate-optimization' => ['Shopify Conversion Rate Optimization', 'Conversion-Optimization.png'],
        'shopify-speed-optimization' => ['Shopify Speed Optimization', 'Generates-Traffic.png'],
        'shopify-maintenance-services' => ['Shopify Maintenance Services', 'professional-website-maintenance-service.png'],
        'shopify-checkout-customization' => ['Shopify Checkout Customization', 'opt-in-and-opt-out-compilance-message.png'],
        'outsource-shopify-development' => ['Outsource Shopify Development', 'cost-effective-messages.png'],
        'white-label-shopify-development' => ['White Label Shopify Development', 'Brand-Awareness.png'],
        'dedicated-shopify-developer' => ['Dedicated Shopify Developer', 'personalized-seo-services.png'],
        'shopify-liquid-developer' => ['Shopify Liquid Developer', 'seo-friendly-content.png'],
        'shopify-api-integration' => ['Shopify API Integration', 'instant-and-direct-communication.png'],
        'shopify-erp-integration' => ['Shopify ERP Integration', 'optimize-message-result.png'],
        'shopify-multi-store-setup' => ['Shopify Multi-Store Setup', 'smo-industry-trend.png'],
        'shopify-store-audit' => ['Shopify Store Audit', 'ppc-analyzepng.png'],
        'shopify-one-product-store-design' => ['Shopify One Product Store Design', 'target-message.png'],
        'shopify-dropshipping-store-design' => ['Shopify Dropshipping Store Design', 'ppc-ads.png'],
        'shopify-fashion-store-design' => ['Shopify Fashion Store Design', 'graphic-make-brand-identity.png'],
    ];
}

// Service slugs that have their own dedicated main service page
function service_has_page($slug) {
    static $withPages = [
        'search-engine-optimization', 'social-media-optimization', 'pay-per-click', 'whatsapp-marketing',
        'sms-marketing', 'content-marketing', 'web-development-company', 'graphic-designing',
        'google-marketing', 'facebook-marketing', 'ecommerce-website-development-company', 'website-maintenance-services',
    ];
    return in_array($slug, $withPages, true);
}

// Original content generator for a service+city page.
// Services with a dedicated template (service-templates.php) get their own copy;
// everything else uses the generic long-form structure below (h2/h3, check-lists,
// numbered steps, industry tags; intro rotates by city+service hash for variety).
function generate_city_page_content($serviceName, $city, $country, $serviceSlug = null) {
    if ($serviceSlug !== null) {
        $html = service_page_html($serviceSlug, $serviceName, $city, $country);
        if ($html !== null) return $html;
    }
    $kw = "<strong class=\"kw\">{$serviceName} in {$city}</strong>";
    $kwCompany = "<strong class=\"kw\">best {$serviceName} company in {$city}</strong>";
    $variant = crc32($city . $serviceName) % 3;

    $intros = [
        "Searching for the {$kwCompany}? Your search ends here. <strong>TopWebExpert</strong> delivers result-driven {$kw} that helps local businesses attract more customers, generate qualified leads and grow revenue online. From ambitious startups to established brands across {$city}, {$country}, we combine global best practices with a deep understanding of your market to build a digital presence that truly performs.",
        "Every successful business in {$city} today has one thing in common — a powerful online presence. <strong>TopWebExpert</strong> offers professional {$kw} designed to put your brand in front of the right customers at the right time. Whether you serve customers locally in {$city} or across {$country}, our proven strategies turn online visibility into real business growth.",
        "Your customers in {$city} are searching online right now — is your business the one they find? With expert {$kw} from <strong>TopWebExpert</strong>, it can be. We help businesses of every size across {$city}, {$country} stand out from the competition, win customer trust and convert clicks into loyal, paying customers.",
    ];

    $benefits = "<ul class=\"check-list\">"
        . "<li><strong>Proven results</strong> — 500+ successful projects and 150+ satisfied clients worldwide</li>"
        . "<li><strong>Local market focus</strong> — strategies tailored for {$city} customers and competition</li>"
        . "<li><strong>Transparent reporting</strong> — clear monthly reports, no confusing jargon, no hidden costs</li>"
        . "<li><strong>Dedicated experts</strong> — a personal point of contact who understands your business</li>"
        . "<li><strong>Affordable packages</strong> — flexible pricing for startups, SMEs and enterprises alike</li>"
        . "<li><strong>Fast turnaround</strong> — we respect deadlines and deliver on time, every time</li>"
        . "</ul>";

    $steps = "<ol class=\"steps\">"
        . "<li><strong>Free consultation &amp; audit</strong> — we study your business, your competitors in {$city} and your growth goals, completely free.</li>"
        . "<li><strong>Custom strategy</strong> — our specialists design a {$serviceName} plan built specifically for your audience in {$city} and across {$country}.</li>"
        . "<li><strong>Expert execution</strong> — our experienced team implements every step with precision, keeping you informed throughout.</li>"
        . "<li><strong>Measure &amp; optimize</strong> — we track performance daily and continuously fine-tune for maximum return on investment.</li>"
        . "<li><strong>Grow &amp; scale</strong> — as results come in, we scale what works so your business in {$city} keeps growing month after month.</li>"
        . "</ol>";

    $industries = "<ul class=\"tag-list\">"
        . "<li>Real Estate</li><li>Healthcare &amp; Clinics</li><li>Education &amp; Coaching</li><li>E-Commerce &amp; Retail</li>"
        . "<li>Restaurants &amp; Hospitality</li><li>Manufacturing</li><li>Finance &amp; Insurance</li><li>Travel &amp; Tourism</li>"
        . "<li>Salons &amp; Wellness</li><li>Startups &amp; SaaS</li><li>Legal &amp; Consulting</li><li>Local Services</li>"
        . "</ul>";

    $why = "Choosing the right partner for <strong class=\"kw\">{$serviceName}</strong> makes all the difference. Businesses in {$city} face growing competition every day, and a generic, one-size-fits-all approach simply does not work. That is why TopWebExpert builds every campaign around <strong>your</strong> industry, <strong>your</strong> audience and <strong>your</strong> goals. We start with a detailed audit, craft a clear strategy, execute with precision and report results transparently — so you always know exactly what you are getting for your investment.";

    $results = "When you partner with TopWebExpert for {$kw}, you can expect <strong>higher visibility on Google</strong>, <strong>more qualified inquiries</strong>, <strong>better conversion rates</strong> and a brand that customers in {$city} recognize and trust. Our clients typically see measurable improvement within the first few months — and because we focus on sustainable, long-term growth, the results keep compounding over time.";

    $local = "From small local shops to large enterprises, {$city} has a vibrant and fast-growing business community. Standing out here requires more than just being online — it requires being <strong>found first</strong>, looking professional and giving customers a reason to choose you. Our team understands what works for businesses in {$country}, and we bring that experience to every {$serviceName} project we handle in {$city}.";

    $cta = "Whether you are a startup taking your first step online, a growing company ready to scale, or an established brand in {$city} looking for better results — <strong>TopWebExpert</strong> is ready to help you win. Contact us today for a <strong class=\"kw\">free consultation</strong> about {$serviceName} in {$city}, {$country}, and discover why 150+ clients around the world trust us with their digital growth.";

    return "<h2>Professional {$serviceName} in {$city}, {$country}</h2>\n<p>{$intros[$variant]}</p>\n"
        . "<h2>Why {$city} Businesses Choose TopWebExpert</h2>\n<p>{$why}</p>\n{$benefits}\n"
        . "<h2>Our {$serviceName} Process in {$city}</h2>\n{$steps}\n"
        . "<h2>Industries We Serve in {$city}</h2>\n<p>Our {$serviceName} solutions are trusted by businesses across every major industry in {$city}:</p>\n{$industries}\n"
        . "<h2>Results You Can Expect</h2>\n<p>{$results}</p>\n"
        . "<h2>Built for the {$city} Market</h2>\n<p>{$local}</p>\n"
        . "<h2>Get Started with the Best {$serviceName} Team in {$city}</h2>\n<p>{$cta}</p>";
}

// Meta tags for a service+city page: [meta_title, meta_description, meta_keywords].
// Templated services carry keyword-specific meta; the rest use the generic pattern.
function generate_city_page_meta($serviceSlug, $serviceName, $city, $country) {
    $m = service_page_meta($serviceSlug, $serviceName, $city, $country);
    if ($m !== null) return $m;
    return [
        "$serviceName in $city | Best $serviceName Company in $city - TopWebExpert",
        "Top-rated $serviceName services in $city, $country. TopWebExpert helps $city businesses grow with expert $serviceName. Free consultation — contact us today!",
        "$serviceName $city, $serviceName in $city, best $serviceName $city, $serviceName company $city, $serviceName agency $country",
    ];
}

// Shopify family: the original shopify-development service plus the 34 keyword services.
function is_shopify_service($slug) {
    return $slug === 'shopify-development' || isset(service_templates()[$slug]);
}

// Where a city page's "About this service" / breadcrumb link points: the dedicated
// service page when one exists, the Shopify hub for Shopify services, else Our Services.
function service_hub_url($slug) {
    if (service_has_page($slug)) return '/' . $slug;
    if (is_shopify_service($slug)) return '/shopify-services';
    return '/our-services';
}
