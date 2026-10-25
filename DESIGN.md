# ALMORA – Design System (Foundations)

Brand: ALMORA, 925 sterling silver jewelry (almora.co.il). Hebrew-first, **RTL**.
Vibe: delicate, minimal, premium. White space, clean product photography. Flat design, no decoration.
Scope: new homepage. This file covers foundations only (no component specs yet).

---

## 1. Logo
- Black ornamental knot symbol above the serif wordmark "ALMORA".
- Always black (#202020) on white. Do not recolor, stretch or add effects.
- File: provided separately (logo.png / svg).

## 2. Colors

| Token | Hex | Usage |
|---|---|---|
| `--color-text` | `#202020` | All text, tags, **buttons** (button background/border) |
| `--color-bg` | `#FFFFFF` | Page background. Pure white across the whole page |
| `--color-surface` | `#F5F5F5` | Light emphasis, e.g. cards placed on a white background. Optional, not always needed |
| `--color-gold` | `#B8860B` | **Hero section only**, as an accent. Do not use elsewhere. Text on gold is white `#FFFFFF` |
| `--color-stock` | `#22C55E` | The availability dot inside the black "במלאי" tag only |
| `--color-star` | `#FFB323` | Review stars only (filled) |
| `--color-verified` | `#E6F4F1` | Background of the "מאומת" pill in reviews only |
| `--color-sale` | `#E24A49` | Discount tags and the current price of products on sale |

Rules:
- Page background is always `#FFFFFF`.
- `#F5F5F5` appears only on top of a white background.
- Green appears only as the availability dot, yellow `#FFB323` only on review stars, and mint `#E6F4F1` only on the verified pill. Gold never appears outside the hero. Red never appears outside discount tags and sale prices.
- Announcement bar: black (`#202020`) background, white Polin Medium (500) text at 20px (18px on phones), running continuously (marquee). Medium at a slightly larger size renders cleaner than SemiBold while the strip moves. This is the only exception to "text is always `#202020`".

## 3. Shape and depth
- Border radius: **12px** on everything: product images, product grid items, buttons, cards. Uniform.
- Buttons use the same 12px radius. Buttons are black (`#202020`) with white SemiBold text.
- Hover on buttons: same as the live almora.co.il (`em-button-hover-eff`). The button keeps its colors and one light band (`rgba(255,255,255,.25)`, skewed 20deg) sweeps across once, 0.75s.
- Hover on nav links: same as the live site. A 1px line (current text color) grows under the link, width 0 to 100%, 0.4s. No grey backgrounds, no pills. Grey `#F5F5F5` is only for cards and frames.
- Shadows: **none**.

## 4. Typography

Font: **Polin** (פולין), Hebrew serif.

| Style | Desktop | Mobile | Weight | Line height |
|---|---|---|---|---|
| Style | Desktop (≥1025px) | Tablet (768–1024px) | Mobile (≤767px) | Weight | Line height |
|---|---|---|---|---|---|
| H1 (hero/slider headings) | 120px | 80px | 42px | SemiBold (600) | 1em |
| H2 | 80px | 58px | 36px | SemiBold | 1em |
| H3 | 48px | 40px | 30px | SemiBold | 1em |
| H4 | 36px | 32px | 26px | SemiBold | 1em |
| H5 | 24px | 24px | 22px | SemiBold | 1em |
| Body | 18px | 18px | 18px | Regular (400) | 1.3em |
| Body strong | 18px | 18px | 18px | SemiBold (600) | 1.3em |
| Caption / notes / descriptions | 14px | 14px | 14px | Regular (400) | 1.3em |

Weights and line heights are identical across breakpoints; only sizes change. Tablet sizes sit between desktop and mobile (roughly a 2/3 step down from desktop for the large headings), so the hero keeps presence without wrapping badly on a ~800px screen.

Text color is always `#202020`.

### Font files (Polin)
Web fonts, WOFF2, stored in `fonts/`:
- `fonts/Polin-Light.woff2` → weight 300
- `fonts/Polin-Regular.woff2` → weight 400
- `fonts/Polin-Medium.woff2` → weight 500
- `fonts/Polin-Semibold.woff2` → weight 600

Four weights are loaded: Light 300 for refined small text (product names, category labels, review text), Regular 400 for body and prices, Medium 500 for small emphasis, SemiBold 600 for headings and Body strong. Do not request any other weight, to avoid faux-bold. All four files come from the licensed Polin WEB package.

```css
@font-face {
  font-family: "Polin";
  src: url("fonts/Polin-Regular.woff2") format("woff2");
  font-weight: 400; font-style: normal; font-display: swap;
}
@font-face {
  font-family: "Polin";
  src: url("fonts/Polin-Semibold.woff2") format("woff2");
  font-weight: 600; font-style: normal; font-display: swap;
}
```
On WordPress: upload both files to Media (or Elementor > Custom Fonts, family "Polin") and use the uploaded URLs in place of the relative paths.

## 4b. Spacing
| Token | Value | Usage |
|---|---|---|
| `--space-section` | `8vw` (mobile ≤767px: `80px`) | Vertical space between page sections (top/bottom padding of each section) |
| `--space-element-min` | `15px` | Tight gap between related elements (e.g. title to its description, label to value) |
| `--space-element-max` | `20px` | Looser gap between separate elements (e.g. text to button, grid gaps, card padding) |

Rule: element gaps are dynamic, chosen per situation anywhere between 15px and 20px (use the two tokens, or any value in between when the layout needs it). Not tied to breakpoints. Never below 15px or above 20px. Section spacing is always viewport-relative (`vw`), never fixed px.

---

## 5. Tokens (CSS / Elementor)

Platform: WordPress + Elementor only.

```css
:root {
  /* colors */
  --color-text: #202020;
  --color-bg: #FFFFFF;
  --color-surface: #F5F5F5;
  --color-gold: #B8860B;
  --color-sale: #E24A49;
  --color-stock: #22C55E;
  --color-star: #FFB323;
  --color-verified: #E6F4F1;

  /* shape */
  --radius: 12px;
  --shadow: none;

  /* type */
  --font-main: "Polin", serif;
  --fs-h1: 120px; --fs-h2: 80px; --fs-h3: 48px; --fs-h4: 36px; --fs-h5: 24px;
  --fs-body: 18px; --fs-caption: 14px;
  --lh-heading: 1em; --lh-body: 1.3em;
  --fw-light: 300; --fw-regular: 400; --fw-medium: 500; --fw-semibold: 600;

  /* spacing */
  --space-section: 8vw;
  --space-element-min: 15px;
  --space-element-max: 20px;
}
html { direction: rtl; }

/* tablet sizes */
@media (max-width: 1024px) {
  :root {
    --fs-h1: 80px; --fs-h2: 58px; --fs-h3: 40px; --fs-h4: 32px; --fs-h5: 24px;
  }
}

/* mobile sizes */
@media (max-width: 767px) {
  :root {
    --fs-h1: 42px; --fs-h2: 36px; --fs-h3: 30px; --fs-h4: 26px; --fs-h5: 22px;
    --space-section: 80px;
  }
}
```

### Elementor mapping
- Site Settings > Global Colors: Text `#202020`, Background `#FFFFFF`, Surface `#F5F5F5`, Gold `#B8860B`, Sale `#E24A49`.
- Site Settings > Global Fonts: define H1-H5, Body, Body strong and Caption exactly as in the table above (font Polin, size, weight, line height). Set the desktop size first, then Tablet and Mobile in the responsive switcher (Elementor breakpoints: Tablet ≤1024px, Mobile ≤767px).
- Spacing: section top/bottom padding `8vw` (`80px` on mobile); gaps between elements dynamic, 15-20px depending on the situation (same on all breakpoints). Can be set as Global/Custom CSS variables `--space-section`, `--space-element-min`, `--space-element-max`.
- Border radius 12px: set on widgets/containers/images/buttons (or via the global CSS variable `--radius`).
- Shadows: leave all Box Shadow settings off.
- Use Global Colors and Global Fonts in every widget. No hardcoded values.
- The CSS variables above can be added in Site Settings > Custom CSS if needed.

### Live build (almora.co.il, started 2026-10-15)
- New homepage: page "דף בית חדש" (`/home-new/`), template Elementor Canvas, noindex and kept out of the sitemap through Yoast SEO until it replaces the live homepage. Polin loads with font-display swap via the mu-plugin `almora-font-display.php`.
- Polin is installed in Elementor > Custom Fonts (300, 400, 500, 600, woff2).
- Globals are added as **new custom** entries named "ALMORA ..." (colors: Text, Background, Surface, Sale, Stock, Star, Verified, plus the existing Gold; fonts: ALMORA Body, ALMORA Body SemiBold, more as sections need them). The Elementor system colors and fonts are left untouched, because the live site may use them.
- Containers only (no sections or columns), and only the widgets a section needs. Announcement strip: one full-width container and one Ecomus Marquee widget (speed 0.15, gap 8vw, padding 15px top and bottom).
- Hero: one full-width container and one Elementor Pro Slides widget (class `alm-hero`). Each banner holds its image, shade, title, text, button and link. The text field has two lines: line 1 shows on desktop, line 2 on phones. Fonts and colors come from the globals (ALMORA H1 120/80/42, ALMORA Body, ALMORA Button 18/14). Layout details that have no native control (fixed text box, dots, image focus per banner, phone layout) are in the widget's Custom CSS.
- Categories: a grid container (3 columns, 2 on phones, max 1100px, gap 20px / 12px, padding 8vw 6vw, and on phones 80px top and bottom with no side padding so the cards reach the screen edges, keeping their 12px rounded corners) with six link cards. Each card is a container (link, 12px radius, Surface background, native 1.03 hover scale) holding three widgets: main image, hover image and the name (Heading, global font ALMORA H4, white). The square shape, image fill, hover fade, earring zoom and the light shade under the name are in the grid container's Custom CSS.
- Header: one container "Site header (new)" holding the top row (Best Sellers / logo / icons), the menu row, the mobile menu (Off-Canvas) and the sticky header. The menu is the WordPress menu "ALMORA ראשי" (Appearance > Menus), shown by three Nav Menu widgets (header, sticky, mobile), so editing the menu updates all three. Items not live yet carry the CSS classes `alm-soon-tag` (the "בקרוב" pill) and `alm-soon` (muted). Cart: Menu Cart widget with a side cart that opens from the left (380px, 88% on phones, page dimmed behind it). Rows: 72px photo with 8px radius, name in ALMORA Product Name (16 Light, two lines), the quantity as a light grey pill with minus and plus, a magicoon X to remove. Subtotal on one row with a thin top line and the savings as a small red tag. Checkout is the black button on top, "מעבר לסל הקניות" the light grey one under it. The Ecomus "לקוחות קנו גם" carousel is hidden in the new cart. Search: Search Form, full screen, products only. Icons are the magicoon SVGs in the media library. The sticky header and the products-only search come from the Custom Code snippet "ALMORA header (home-new)" (this page only). Later this container becomes the site-wide Theme Builder header.
- Products: one container "Products" holding three widgets. (1) Taxonomy Filter (class `alm-tabs`) on product categories, linked to the grid; it lists only the five categories the grid shows, in the prototype order (set in the container's Custom CSS), font ALMORA Tab 24/20/18. (2) Loop Grid (ID `almgrid`, product query limited to the five categories, 8 per tab, 4/3/2 columns) that uses the Loop Item template "ALMORA Product Card". (3) An HTML widget with the empty theme quick view window. The card template is built from containers, Image, Heading and Icon widgets with dynamic tags; the "ALMORA" tag group (second photo, colour swatches, savings, stock, price) comes from the mu-plugin `almora-elementor-tags.php`, so it keeps working without any other plugin. The Custom Code snippet "ALMORA products (home-new)" (this page only) opens the necklaces tab on load, fades the grid between tabs, keeps the address clean, and connects the eye on each card to the theme quick view. The quick view adds to the cart for real: it closes after "הוספה לסל" and the side cart opens on its own (Menu Cart setting "open the cart automatically").
- Product rails: one container "Product rails" holding three rail containers (best sellers, sale, new in store). Each rail is a link card (container with the photo as background, a 32% dark layer, label in ALMORA Caption 14, title in ALMORA H4 36/32/26, and the magicoon arrow as an Icon widget in a 56px black square) next to a Loop Carousel that uses the same "ALMORA Product Card". The carousel moves on its own at a steady speed (autoplay with no pause, speed 9300, linear easing in CSS), can be dragged, and has the thin progress line (94px). The dark part of the line is as wide as the share of products on screen (4 of 10 = 40%), follows the strip at every moment, also while dragging, reaches the end (left) at the last product and jumps back to the start (right) when the strip starts over with the first product. Swiper's own progress bar only moves when the "current" card changes, so the page script draws it instead. 4 cards on desktop, 3 on tablet, 1 plus a peek on phones. Queries: popularity / on sale / newest, 10 products, without the "השקעה בכסף (סילבר)" category, as in the prototype. A rail with fewer products than fit on screen stands still (page script). WooCommerce leaves its popularity sort switched on for the next product loop on the page; the mu-plugin `almora-loop-ordering.php` switches it off after each Elementor loop (new page only for now, site-wide at launch).
- Shop The Look: one container "Shop The Look" with a Heading (global font ALMORA Look Title, 30/22 Light, 0.14em) and a grid container (1.7fr / 1fr, max 1200px, one column on phones). Photo: Elementor Hotspot widget. Each dot is a hotspot item with link "#" and the custom attribute `data-alm-qv|<product ID>`, which opens that product's quick view. Positions are % of the photo; the phone fields hold the positions for the square crop (x = (x − 0.146) / 0.708), so the dots stay on the jewelry. Products: Loop Grid with the Loop Item template "ALMORA Look Row" (72px photo, name, price, eye; 60px on phones), query "manual selection" with Query ID `almora_manual`, which keeps the products in the order they were picked (mu-plugin `almora-loop-ordering.php`). A dot and its row light up together (page script).
- Reviews: one container "Reviews" with the widget "ALMORA Reviews" (mu-plugin `almora-reviews.php`; settings: title, reviews per page, "show sample reviews"). It reads the store's real WooCommerce reviews (rating, verified, product, product category) and a photo per review (review field `almora_review_photo`), and runs the rating / sort / category filters in the browser. The look is in the container's Custom CSS. Sample content: 10 reviews stored as **pending** WooCommerce reviews marked `almora_sample`, so they never show on product pages and never count in ratings; the widget shows them only while "show sample reviews" is on. Their photos are AI-made amateur phone photos of the real pieces (Magnific, Flux.2 Klein with the product photo as reference), one for every review, files in `reviews/generated/`. They show the piece on the neck, ear, hand or in its box, with no face (cropped above the chin, or covered by the phone in a mirror), so they read as real customer shots. Before launch: turn the switch off, delete the 10 pending sample reviews and the media marked `almora_sample`.
- Why buy: container "Why buy" with a Heading and five **Icon Box** widgets (magicoon Light SVGs from the Media library: truck, arrows-repeat, credit-card, diamond, chat-dots). Item widths and the lighter weights are in the container's Custom CSS.
- FAQ: grid container "FAQ" with the intro (Heading, Text Editor, a Heading link "ליצירת קשר"), an **Image Carousel** for the photo (fade, no arrows or dots, one photo per question in the same order) and a **Nested Accordion** (one open at a time, the first open on load, FAQ schema on; each answer is a Text Editor inside its item). Opening a question moves the carousel to the photo with the same number (Custom Code snippet "ALMORA products, rails, look, FAQ and footer"). To add a question, add an accordion item and a photo at the same position.
- Footer: container "Site footer (new)" (html tag footer) with the Ecomus marquee, a Heading (global "ALMORA H3") and an **Elementor Form** for the newsletter (email only; every sign up is saved under Elementor > Submissions and emailed to the site admin address), a grid with the brand column (Image + Text Editor) and three link columns (Heading + Icon List without icons), and the bottom bar (copyright Heading, payment chips in an HTML widget cut from `almora-payment-methods.webp`, social Icon List, credit Heading link). Like the header, it lives on page 7752 for now and moves to a Theme Builder footer at launch.
- iPhone memory (fixed 2026-10-25, cards in the rails showed empty on iPhone in Safari and Chrome): no `will-change` on product cards or marquees (each card became its own graphics layer and iPhone ran out of memory), the hover photo is not shown on touch screens, cards use the `woocommerce_single` size (600px), and missing image sizes were generated for product photos that only had the original. Keep it that way.
- Speed (2026-10-25): WP Rocket on the whole site (page cache with a separate phone cache, minified CSS and JS, deferred JS, scripts delayed until the first touch or mouse move except on cart, checkout and account). Every resized image is WebP (mu-plugin `almora-webp.php`, all existing images regenerated, originals kept). The new home page skips Google Fonts and loads the hero photos at 1536px (1024px on phones). After replacing an image, clear the Elementor CSS, the WP Rocket cache and its saved "above the fold" hints, or the old photo stays preloaded.
- Products grid on the live page: the necklaces tab is chosen on the server (mu-plugin `almora-home-new-speed.php`, Elementor Pro loop filter), so the products are already there when the visitor reaches the section. No script hides the grid or clicks the tab any more.
- Elementor Pro needs the Loop Grid's element ID to be exactly 7 letters or digits, or the tabs cannot reload it ("almgrid").
- Known Elementor Pro bug: the Nav Menu widget crashes the page if its sub-menu indicator is an SVG icon. The arrow is drawn with CSS (magicoon mask) instead.
- Ecomus adds 15px of side padding inside every boxed container. The new page removes it in its page Custom CSS, so the side spacing is exactly the design's.
- The curtain transition is an Elementor Custom Code snippet ("ALMORA hero curtain (home-new)") that loads on this page only. Without it, the slider falls back to a plain fade.
- The site's Additional CSS forces all headings to Playfair with `!important`. The new page overrides it in its own page Custom CSS, using the ALMORA global font, so the live site is not touched.

## 5b. Icons

**Mandatory: the only icon set allowed in this project is magicoon, style Light (the thinnest outline).** No other icon library, no other magicoon style (Regular, Filled, Duotone), no emoji, no icon fonts, no hand-drawn or ad-hoc SVGs.

- Source: `magicoon Library v1.0.fig`, canvas "1 - magicoon (Light)". 1,213 icons, exported as SVG into `icons/<category>/<name>.svg`.
- Also provided: `icons/sprite.svg` (all icons as `<symbol id="name">`), `icons/icons.json` (name, category, file) and `icons/index.html` (searchable preview).
- Grid: 24x24 viewBox. Shapes are filled outlines, so color comes from `fill`, never from `stroke`. Every SVG uses `fill="currentColor"`.
- Color: icons inherit the text color, `#202020` (set `color: var(--color-text)`). No gold or red icons, except a sale icon inside a discount tag.
- Size: 24px by default. 20px for compact UI (tags, inline with caption text), 32px for large touch targets. Never scale the stroke separately, the line weight scales with the icon.
- Alignment: icons sit on the text baseline/center with an element gap of 15px (`--space-element-min`) between icon and label.
- Flat only: no shadows, backgrounds or effects on icons. A circular or rounded container, if needed, uses `--radius` and `--color-surface`.
- If an icon is missing, pick the closest from the set. Do not draw a new one.

```html
<!-- inline sprite, once per page -->
<svg class="icon"><use href="#search"/></svg>
```
```css
.icon { width: 24px; height: 24px; color: var(--color-text); fill: currentColor; flex: none; }
```

Header set (names in the sprite): `search`, `user`, `heart`, `shopping-bag`, `menu`, `times`, `arrow-left`, `arrow-right`, `phone`, `location-pin`, `truck`, `shield-check`, `gift`, `star`.

On WordPress: upload the needed SVGs to Media (or Elementor > Custom Icons as an SVG set) and use only those. Allow SVG uploads with a sanitizing plugin.

---

## 5c. Hero (category banners)

- Full width (100%), height `clamp(520px, 46vw, 860px)`, mobile `130vw`. One banner per live category that has more than 3 products, ordered by product count. Future categories are not shown.
- Image: lifestyle photo from the site Media library, `object-fit: cover`, with a flat overlay `rgba(32,32,32,.38)` so text stays readable. No gradients.
- Text on the banner is white. Second exception to "text is always `#202020`".
- Text block is one fixed box for every banner, so X and Y never move: title (H1, one line, no wrap), paragraph (Body, box is always exactly 3 lines high, copy is 2 to 3 lines), button on a fixed row. Width `min(560px, 86vw)`, 6vw from the start edge, vertically centered.
- Button on the banner: light variant (white background, `#202020` text), same radius. On hover it flips to black (`#202020`) with white text, plus the same light sweep.
- Transition (curtain, subtle): the next image is revealed by 8 vertical strips, each `clip-path: inset(0 0 35%)` to `inset(0)` with opacity 0 to 1, 0.9s `cubic-bezier(.2,0,0,1)`, staggered 35ms starting from the right strip. Text fades out and in (0.45s). Auto advance every 6s, paused on hover. Off when the user prefers reduced motion.
- Phones (up to 767px): the text is one centered group near the bottom of the banner, in a clear hierarchy: the title (H1), one short sentence under it (Body, one line, a separate short line per banner), then the button. The longer paragraph is shown on desktop and tablet only. The dots are centered under the group. Inspired by the Shani Jacobi mobile banner for position and hierarchy only.
- No label pill on the banner. Navigation dots: plain white dots (8px) straight on the image, bottom, aligned to the same start edge as the title; the active dot gets a soft white ring (24px, 30% white). No pill behind the dots.

## 5d. Category grid

- Below the hero. Section padding `8vw` top and bottom, `6vw` on the sides. 3 columns by 2 rows on desktop inside a centered 1100px grid, 2 columns on mobile (28px side padding, 12px gap). Kept slightly smaller than the first version on purpose.
- Square cards (`aspect-ratio: 1`), `12px` radius, lifestyle photo from the Media library, `object-fit: cover`.
- Name on each card (changed 2026-10-25 at the client's request): only the category title, no description and no glass strip. White SemiBold title (H4: 36px, 32px tablet, 26px phones) centered near the bottom (20px, 12px on phones), over a light flat shade `rgba(32,32,32,.18)` on the photo so it stays readable. On phones the cards run edge to edge: the corners on the screen edge are square (0px) and the inner corners stay rounded (12px), set per card in its Border Radius for mobile.
- Hover: the whole card grows to 1.03 (0.5s, `cubic-bezier(.2,0,0,1)`), same on product cards. A second photo of the same category fades in over the first, 0.5s `ease-in-out`. No shadows.
- Only live categories with real products are shown: שרשראות, טבעות, עגילים, תליונים, צמידים, plus a "כל התכשיטים" card to the shop. Future categories (מוסונייט, גברים) are not shown until they exist.

## 5e. Product grid with text tabs

- Below the category grid. Side padding `6vw`, bottom `8vw` (the category grid already provides the top space).
- Tabs: text only, centered, no borders, no backgrounds, no underline, with a thin 1px divider between names (18px tall, 14px on phones, `rgba(32,32,32,.22)`) so each name reads as its own tap. H5 size, Regular at 55% opacity; the active tab is SemiBold at 100%. One tab per live category (שרשראות, עגילים, טבעות, צמידים, תליונים). Switching fades the grid out and in (0.35s). On mobile the tabs stay on one row that scrolls horizontally (no scrollbar, edge to edge), and the chosen tab scrolls to the center.
- Grid: 4 columns (3 tablet, 2 mobile), up to 8 products per tab, gap 20px (30px between rows).
- Card (refined, less busy): a very light frame (1px `rgba(32,32,32,.08)`, 12px radius, 10px padding, 8px on phones), white. Square photo on a white background (8px radius); the product's white studio photo is used first when it has one, the second photo fades in on hover. On the photo: a small red "חסכו ₪X" pill at the top start when on sale, and the quick view button (magicoon `eye`, 36px white circle with a hairline edge, 32px on phones) at the bottom end, shown on hover (always on touch screens). Under the photo: a quiet stock line ("במלאי" in Light 12px at 75% with a 6px green dot, no black pill), the name in Polin Light 16px (14px phones, max 2 lines), metal colour swatches, and the price in Regular 16px (sale price red, old price struck through, smaller).
- Metal swatches: round, 14px (12px phones), with a soft inner hairline; the first is marked with a 1px outline. Up to 4 are shown, then "+N". They show the WooCommerce colour attribute: white gold / silver `linear-gradient(135deg,#7A7A7A,#BFC3C7,#ECEEEF)`, yellow gold `linear-gradient(135deg,#B8892D,#D8B75B,#F3E3B0)`, rose gold `linear-gradient(135deg,#C98C7A,#EFC6B5,#FFF4EF)`, and flat colours for coloured strings. These metal gradients are the only gradients allowed, because they describe the product, not the brand.
- Quick view (eye button): a dialog with the photo and its gallery thumbnails, the stock line, the name (H5 Regular), the price, the colour name with large 24px swatches you can pick, and a black button. On the live site the button is "הוספה לסל" (real add to cart, options picked in the window), with a quiet "הצג פרטים מלאים" link under it; the prototype shows "לעמוד המוצר". The photo in the quick view is not clickable (Elementor would open it in its lightbox), and the options sit 24px above the button. Desktop: centered, 900px wide, two columns. Phones: a bottom sheet (88vh max) with the photo on top. Closes with X, the backdrop or Escape; the page does not scroll behind it. The same card and quick view are used in the grid and in the rails.
- Product photos: final choice with Itai. Until then, white studio photos are preferred; the pendants (תליונים) have no white photo on the site yet.
- Product names are shown without marketing tails after a dash. Data (names, prices, images, links) comes from the live site.

## 5f. Product rails (carousel)

- Three rails stacked below the product grid: הנמכרים ביותר, מבצעים, חדשים בחנות. Each rail = a lead card on the start side plus a strip of product cards. Each rail has the same side padding (`6vw`) and bottom space (`6vw`).
- Lead card: 300px wide, same height as the cards, 12px radius, lifestyle photo with a flat overlay `rgba(32,32,32,.32)`. Bottom start: small Caption label, H4 title (SemiBold, white), and a black 56px square button with the magicoon `arrow-left` icon (same shine hover). The whole card links to the shop.
- Cards: the same product card as the grid (framed, tags, name, specs, price), fixed 250px wide, 20px gap.
- Motion: the strip moves slowly and linearly (26px per second), endless loop, toward the lead card. Draggable with mouse or touch; clicks right after a drag do not open the product. Pauses while off screen. No auto motion for reduced motion (still draggable).
- If a rail has too few products to fill the width, it stays still and aligned to the lead card (no loop, no drag, no progress bar).
- Progress bar under every moving strip (in the spirit of Danon): 94px wide, 2px tall, centered 18px under the cards, track `rgba(32,32,32,.15)`, the dark part `#202020` sized by how much of the strip is visible and moving with the strip (auto motion and drag), right to left as in RTL.
- Data: best sellers by popularity, sale by on_sale, new by date. The silver investment category is excluded (jewelry only).

## 5g. Reviews (WooCommerce)

- Header row: H4 "ביקורות" with a thin divider, filled stars (`--color-star`), the average (SemiBold) and the count at 60% opacity. At the other end two selects: rating (all, 5 to 1) with the magicoon `filter` icon, and sort (relevance, newest, highest, lowest) with the magicoon `sort` icon. 1px `rgba(32,32,32,.3)` outline, 12px radius (form controls are the exception to "no frames"). On phones the two selects share one row.
- Category filter under the header: a text row (הכל, שרשראות, טבעות, עגילים, צמידים, תליונים, סטים), Polin Light 16px at 60% opacity, the active one Medium at 100%, separated by thin 1px dividers (14px tall, `rgba(32,32,32,.25)`). Only categories that have reviews are listed. On phones the row scrolls sideways. The category of a review is the category of the reviewed product.
- Grid: 5 cards per row on desktop, 3 under 1200px, 10 per page, gap 15px. Changing a filter fades the grid.
- Card (compact and quiet): `#F5F5F5` surface, 12px radius, no frame. Photo 4:3 on top (the review photo, or the product image when there is none). Body 12px/14px padding: name (Medium 15px) with a small "מאומת" pill on `--color-verified` and the date at the end (Light 12px, 60%); five filled stars 15px in `#FFB323`; review text Polin Light 15px, max 3 lines; a hairline divider and the reviewed product (32px thumbnail, name in Light 13px, one line).
- Phones: a small horizontal card: 92px square photo on the side, text next to it (2 lines), the product line without a divider. 10px gap between cards.
- WooCommerce mapping: average = `average_rating`, count = `review_count`, sort = `orderby` (`date_gmt`, `rating`) and `order`, rating filter = the rating of each review, category filter = the product's `product_cat`, "verified" = `verified`, photo = the review image.
- Stars are the one place the icon set is used filled: same geometry as magicoon Light `star`, filled `#FFB323`.
- Never publish invented reviews. The content in the prototype is a placeholder until real reviews exist on the site.

## 5h. Why buy (benefits strip)

- Below the reviews. Side padding `6vw`, bottom `8vw`. Centered H4 title (SemiBold), then 5 items in a row (3 per row on tablet, one under the other on mobile, a short last row stays centered), gap 30px.
- Item, everything centered: magicoon Light icon 56px in `#202020` (48px mobile), a Body SemiBold name, and a Caption description at 65% opacity. No cards, no frames, no backgrounds.
- Content comes only from real policies on the site: delivery and tracking, 14 day replace and return, payment methods, materials (silver 925 with moissanite), service hours. Do not add claims that are not on the site.

## 5i. FAQ with changing photo

- Last section. Side padding `6vw`, bottom `8vw`. Three columns in RTL: intro on the start side (H4 title, Body paragraph, text link "ליצירת קשר" with an underline that retracts on hover), a portrait photo (3:4, 12px radius) in the middle, and the accordion on the end side. Tablet: intro on top, photo and accordion below. Mobile: stacked, photo 4:3.
- Accordion: one question open at a time (the first is open on load). Items have a 1px outline (`rgba(32,32,32,.25)`, `#202020` when open), 12px radius, 18px/20px padding. Question in Body SemiBold with the magicoon `plus` icon that rotates into an X when open. The answer slides open, Body at 75% opacity.
- Photo: each question has its own lifestyle photo of a woman wearing the jewelry, from the Media library (no product shots). Opening a question cross fades the photo (0.9s ease in out). Closing a question keeps the current photo.
- Answers use only facts from the site: shipping times, replace and return window, payment methods, materials, original photos, contact details. No dashes in the copy.

## 5j. Footer

- Dark footer: background `#202020` (the text color), text white. This is a deliberate exception to "page background is always white", the same way the announcement bar and the hero use their own colors. No gold, no red.
- Order: (1) a slow linear marquee band of large words (H4, SemiBold: משלוח עד הבית, תשלום נוח, שירות אישי, כסף 925 ואבן מוסונייט) with a thin line under it; (2) newsletter: H3 headline on one side, a white 12px radius email field with a black square arrow button on the other; (3) four columns: brand (logo inverted to white, one line description, address, hours, phone), קטגוריות (live categories only), החברה, תמיכה (all policies); (4) bottom bar: copyright, the payment logos strip, social links as text, and the credit "בניית אתר ע"י עידו בר" as a link in Caption SemiBold at full opacity, with the same white 1px underline that grows on hover.
- Links use the same 1px underline that grows on hover (white at 75% opacity, 100% on hover). Thin dividers are `rgba(255,255,255,.15)`.
- Social networks are written as text because the mandatory magicoon set has no brand logos. Payment methods are small separate chips (32px tall, 24px on mobile so all five stay on one row; radius and gap are a quarter of the height) cut from the supplied logo strip (`assets/payment-methods.webp`): bit, Google Pay, Apple Pay, Mastercard, Visa.
- The newsletter form is client side only in the prototype. Connect it to a real form before launch.

## 5k. Header v2 (sticky)

- No wishlist icon anywhere on the site (main header, sticky header, mobile). Only search, account and cart.
- Appears when the hero reaches the top of the screen (the main header has scrolled away) and then stays fixed at the top while the page scrolls. It slides down (0.45s, `cubic-bezier(.2,0,0,1)`) and slides away again when the user returns above the hero.
- Full width, 72px tall (60px mobile), white, with a 1px bottom line. Three zones: the logo on the start side (48px tall), the open menu in the middle, and search, account and cart on the end side (magicoon icons, same count badge as the main header).
- Menu: the same links as the main header with the same underline hover and the same dropdowns (תכשיטי מוסונייט, תכשיטי גברים, מדיניות), sizes scaling with the screen (14 to 17px) so all items fit on one row, plus a **Best Sellers** tag as the last item of the menu, at its far left end: black pill, white SemiBold 16px text with 0.05em letter spacing (the same spacing is on the "Best Sellers" button in the main header), with the same light sweep on hover.
- Under 1100px the menu is hidden and only the logo and the icons remain.

## 5l. Mobile header and menu (phones only, up to 767px)

- The main header is one row: hamburger alone on the start side (magicoon `menu`, 28px), the logo exactly in the middle, and the three tools (search, account, cart) close together on the end side with a 10px gap. The text menu row and the "Best Sellers" button are not shown in the header on phones. The sticky header v2 uses the same three zones.
- The hamburger opens a drawer from the start side (88vw, max 380px, white, 0.4s slide) over a `rgba(32,32,32,.45)` backdrop; the page does not scroll behind it. Close with the X (magicoon `times`), the backdrop, a link, or Escape.
- Drawer: logo and close button on top, then the menu as a vertical list in Body SemiBold: בית, the four live categories (עגילים, טבעות, צמידים, שרשראות), תכשיטי מוסונייט and תכשיטי גברים (each opens a sub list of "בקרוב" items), אודות, מחשבון זהב וכסף, מדיניות (opens the policy links), צור קשר. At the bottom, the **Best Sellers** button, full width, black, bold, at the regular mobile button size (8px 18px padding, Caption text). It never shrinks when a section is open.
- Desktop and tablet are unchanged.

## 5m. Scroll animation

- None. Sections appear in place with no reveal, fade or slide animation when scrolling.

## 5n. Shop The Look

- Between the "חדשים בחנות" rail and the reviews. Side padding `6vw`, bottom `8vw`. Centered title "SHOP THE LOOK" in Polin Light 30px (22px phones), letter spacing 0.14em.
- Desktop: the model photo (1024:725, 12px radius) on one side and the look's products on the other (1.7fr / 1fr, max 1200px). Phones: the photo cropped square, the list under it.
- Hotspots: white 26px dots on the jewelry with a soft breathing ring (scale 1 to 0.8, 2s, alternate, off for reduced motion). Each dot opens the quick view of its product, which links to the product page. Positions are stored as fractions of the photo, and the dots are re-mapped when the photo is cropped (`--f`), so they stay on the jewelry on every screen.
- Product list: rows with a 72px photo (60px phones), the name in Light, the price, and the quick view eye. A row and its dot highlight each other on hover.
- Photo and products are a first proposal (rigid drop necklace, oval ring, tennis bracelet on IMG_9019) and must be confirmed with Itai.

## 5o. Overall refinement (inspired by Danon and Topten)

- Lighter type across the page, with the approved structure unchanged. Section titles ("ביקורות", "למה לקנות באלמורה?", "לכל שאלה יש תשובה", the newsletter headline, the footer marquee words) are Polin Regular instead of SemiBold. Small labels and the active tab are Medium. Supporting text (benefit descriptions, FAQ intro and answers, review text, product names) is Light.
- SemiBold is kept only where weight carries meaning: the hero title, the titles printed on photos (rail lead cards), buttons, and the footer credit.
- Thin dividers, white product photos, quiet badges, and lots of white space, as in the references. Anything beyond these points is to be agreed with Itai first.

## 5p. Main menu structure

- One row, in this order (right to left): בית, **עגילים, טבעות, צמידים, שרשראות** (the four live categories, as top level items like בית and אודות), **תכשיטי מוסונייט** (category with a dropdown), **תכשיטי גברים** (category with a dropdown), אודות, מחשבון זהב וכסף, מדיניות (dropdown), צור קשר. There is no separate "חנות" item.
- The two category dropdowns are small, open under their own item and list the sub categories they will have: מוסונייט (עגילים, טבעות, צמידים, שרשראות, סטים) and גברים (צמידים, שרשראות). Those categories do not exist on the site yet, so the list is muted and marked "בקרוב" until they are created, then the items become links like the live ones.
- Same underline hover for every item. The sticky header and the phone drawer use the same structure.

---

## 6. Do / Don't
- Do keep everything flat, white and airy. Do use 12px radius everywhere.
- Don't add shadows, gradients or extra colors.
- Don't use gold or red outside their single allowed use.
- Don't change heading weights (all headings SemiBold).
- Don't use Tailwind, shadcn or any framework. Build with Elementor and CSS only.
- Don't use any icon outside magicoon Light (section 5b).

## 7. Open items (not defined yet)
- Grid (columns, container max-width, gutters).
- Component specs (buttons states, cards, forms), to be defined as we design.

Resolved: tablet type sizes, section/element spacing, Polin font files (body text uses Polin as well).
