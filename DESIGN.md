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
| `--color-gold` | `#B8860B` | **Hero section and the top announcement bar only**, as an accent. Do not use elsewhere. Text on gold is white `#FFFFFF` |
| `--color-stock` | `#22C55E` | The availability dot inside the black "במלאי" tag only |
| `--color-star` | `#FFB323` | Review stars only (filled) |
| `--color-verified` | `#E6F4F1` | Background of the "מאומת" pill in reviews only |
| `--color-sale` | `#E24A49` | Discount tags and the current price of products on sale |

Rules:
- Page background is always `#FFFFFF`.
- `#F5F5F5` appears only on top of a white background.
- Green appears only as the availability dot, yellow `#FFB323` only on review stars, and mint `#E6F4F1` only on the verified pill. Gold never appears outside the hero and the announcement bar. Red never appears outside discount tags and sale prices.
- Announcement bar: gold background, white Polin SemiBold (600) text, running continuously (marquee). This is the only exception to "text is always `#202020`".

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
- `fonts/Polin-Regular.woff2` → weight 400
- `fonts/Polin-Semibold.woff2` → weight 600

Only these two weights exist, which matches the system (Regular for body/caption, SemiBold for headings and Body strong). Do not request other weights, to avoid faux-bold.

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
  --fw-regular: 400; --fw-semibold: 600;

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
- No label pill on the banner. Navigation dots: plain white dots (8px) straight on the image, bottom, aligned to the same start edge as the title; the active dot gets a soft white ring (24px, 30% white). No pill behind the dots.

## 5d. Category grid

- Below the hero. Section padding `8vw` top and bottom, `6vw` on the sides (same start edge as the hero text). 3 columns by 2 rows on desktop, 2 columns on mobile. Gap `20px` (`15px` on mobile).
- Square cards (`aspect-ratio: 1`), `12px` radius, lifestyle photo from the Media library, `object-fit: cover`.
- Name bar at the bottom of each card: white 82% with a light blur, centered Body SemiBold, `#202020`.
- Hover: the whole card grows to 1.03 (0.5s, `cubic-bezier(.2,0,0,1)`), same on product cards. A second photo of the same category fades in over the first, 0.5s `ease-in-out`. No shadows.
- Only live categories with real products are shown: שרשראות, טבעות, עגילים, תליונים, צמידים, plus a "כל התכשיטים" card to the shop. Future categories (מוסונייט, גברים) are not shown until they exist.

## 5e. Product grid with text tabs

- Below the category grid. Side padding `6vw`, bottom `8vw` (the category grid already provides the top space).
- Tabs: text only, centered, no borders, no backgrounds, no underline. H5 size, Regular at 55% opacity; the active tab is SemiBold at 100%. One tab per live category (שרשראות, עגילים, טבעות, צמידים, תליונים). Switching fades the grid out and in (0.35s). On mobile the tabs stay on one row that scrolls horizontally (no scrollbar, edge to edge), and the chosen tab scrolls to the center.
- Grid: 4 columns (3 tablet, 2 mobile), up to 8 products per tab, gap 20px (30px between rows).
- Card: framed (1px `rgba(32,32,32,.14)`, 12px radius, 15px padding, white). Top row of tags (fully round, 100px radius): a black "במלאי" pill with a green dot (`--color-stock`, 10px, soft ring), and on a sale a red `#E24A49` "חסכו ₪X" pill with white text . Then a square image (12px radius), product name (Body SemiBold, max 2 lines), a Caption line with specs separated by bullets (colors, up to N carat), and the price in `#202020` SemiBold. On a sale the current price turns sale red `#E24A49` (SemiBold) with the old price struck through next to it, same size, 60% opacity. The frame is an explicit exception to "no frames on cards".
- Product names are shown without marketing tails after a dash. Data (names, prices, images, links) comes from the live site.

## 5f. Product rails (carousel)

- Three rails stacked below the product grid: הנמכרים ביותר, מבצעים, חדשים בחנות. Each rail = a lead card on the start side plus a strip of product cards. Each rail has the same side padding (`6vw`) and bottom space (`6vw`).
- Lead card: 300px wide, same height as the cards, 12px radius, lifestyle photo with a flat overlay `rgba(32,32,32,.32)`. Bottom start: small Caption label, H4 title (SemiBold, white), and a black 56px square button with the magicoon `arrow-left` icon (same shine hover). The whole card links to the shop.
- Cards: the same product card as the grid (framed, tags, name, specs, price), fixed 250px wide, 20px gap.
- Motion: the strip moves slowly and linearly (26px per second), endless loop, toward the lead card. Draggable with mouse or touch; clicks right after a drag do not open the product. Pauses while off screen. No auto motion for reduced motion (still draggable).
- If a rail has too few products to fill the width, it stays still and aligned to the lead card (no loop, no drag).
- Data: best sellers by popularity, sale by on_sale, new by date. The silver investment category is excluded (jewelry only).

## 5g. Reviews (WooCommerce)

- Header row: H4 "ביקורות" with a thin divider, then filled stars (`--color-star`), the average (SemiBold) and the count in Body at 60% opacity. At the other end: filters. Gap 20px, side padding `6vw`, bottom `8vw`.
- Filters (all work): a "עם תמונות" toggle (black when active), a rating select (all, 5 to 1 stars) with the magicoon `filter` icon, and a sort select (relevance, newest, highest, lowest) with the magicoon `sort` icon. They use a 1px `rgba(32,32,32,.3)` outline and 12px radius, an explicit exception to "no frames" because they are form controls.
- Grid: 5 cards in a row and 2 rows (10 per page; 3 columns under 1200px, 2 columns on mobile, so a long list does not mean endless scrolling), gap 20px. Changing a filter fades the grid.
- Card: `#F5F5F5` surface, 12px radius, no frame. Square photo on top (the review photo, or the product image when the review has none). Name (Body SemiBold) with a "מאומת" pill on `--color-verified` `#E6F4F1`, date (Caption, 60%), five filled stars in `#FFB323`, review text (Body, max 4 lines), a thin divider and the reviewed product (44px thumbnail, 12px radius, name in Caption SemiBold).
- WooCommerce mapping: average = `average_rating`, count = `review_count`, sort = `orderby` (`date_gmt`, `rating`) and `order`, rating filter = the rating of each review, "verified" = `verified`, photo = the review image. In Elementor build with the Product Reviews widgets or a Loop Grid over reviews, with the same classes.
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
- Menu: the same links as the main header with the same underline hover and the same dropdowns (חנות, מדיניות), plus a **Best Sellers** tag as the last item of the menu, at its far left end: black pill, white SemiBold 16px text with 0.05em letter spacing (the same spacing is on the "Best Sellers" button in the main header), with the same light sweep on hover.
- Under 1100px the menu is hidden and only the logo and the icons remain.

## 5l. Mobile header and menu (phones only, up to 767px)

- The main header is one row: hamburger alone on the start side (magicoon `menu`, 28px), the logo exactly in the middle, and the three tools (search, account, cart) close together on the end side with a 10px gap. The text menu row and the "Best Sellers" button are not shown in the header on phones. The sticky header v2 uses the same three zones.
- The hamburger opens a drawer from the start side (88vw, max 380px, white, 0.4s slide) over a `rgba(32,32,32,.45)` backdrop; the page does not scroll behind it. Close with the X (magicoon `times`), the backdrop, a link, or Escape.
- Drawer: logo and close button on top, then the menu as a vertical list in Body SemiBold: בית, חנות (opens a sub list with the live categories and the "בקרוב" ones muted), אודות, מחשבון זהב וכסף, מדיניות (opens the policy links), צור קשר. At the bottom, the **Best Sellers** button, full width, black, bold.
- Desktop and tablet are unchanged.

## 5m. Scroll animation

- None. Sections appear in place with no reveal, fade or slide animation when scrolling.

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
