# TWIN BASE implementation

- Independent LP: `/twin-base/`, with isolated HTML/CSS/JavaScript and local fonts.
- Vehicle-site changes are confined to related-business links on home, business and company pages.
- Requirements source: `TWIN_BASE_Requirements.md`, provided by the user.
- Milk white `#FDFBF7`, cream `#F6EFE6`, mint `#DDEEE3`, blue `#DCE8F2`, peach `#F6E1D6`; text `#6F655D`.
- Zen Maru Gothic Medium and M PLUS Rounded 1c Regular come from the official Google Fonts repository and are served locally as WOFF2. OFL copyright/license notices are in `public/twin-base/fonts/`.
- Instagram is confirmed as `https://www.instagram.com/twinbase_dogrun?utm_source=qr`. LINE remains unset and opens an accessible preparation dialog. No invented accounts or collection forms.
- Address is the supplied dog-run address, **not the car dealership's address**. Maps links to the user-confirmed `https://maps.app.goo.gl/CMuM3UB9zTNHnDWb7`; no fabricated coordinates or opening hours. Confirmed on 2026-11-17: dog run 30 minutes / 500 yen, hours announced on Instagram, parking for 3 vehicles.
- Unconfirmed facility-condition labels and the café-menu row were removed at the user’s request; events remain examples with no announced date or commitment. Structured data contains only supplied business identity, address and parent organization.
- Preview remains `noindex,follow`, consistent with the current temporary site.

## Photo update — 2026-11-17

The supplied `IMG_7466.HEIC` shows two dogs on the artificial-turf run. It is used as the hero photograph at `/twin-base/assets/hero-dogs.jpg`. HEIC was converted to a web JPEG, oriented and resized without changing its content. The portrait frame uses `object-fit: contain`, preserving both dogs and the full composition. The hero has a static `src` so it also works without JavaScript.

The user’s second inline photograph (container facility and corgi) is requested for the “気ままに、のびのび。人工芝ドッグラン” feature. Its original bytes are not available in the workspace; a downloadable JPG/PNG has been requested. That feature and the terrace still retain photo placeholders pending delivery. No substitute was fabricated.

The original logo file also remains pending; the typographic brand fallback is retained. Once supplied, remove only the circular logo background and preserve the original drawing and lettering.

Configure `heroPhotoPath`, `facilityPhotoPath` and `logoPath` in `public/twin-base/settings.js`. The facility setting is independent of the hero so the two requested images will not overwrite one another. Failed/unset images retain their placeholders.

## Confirmed social URLs

Set `instagramUrl` and `lineUrl` in `settings.js`. HTTPS Instagram and official LINE domains are validated before links are enabled. New-tab links include `noopener noreferrer`. No dummy external account is used.

## Validation

Run the zero-dependency preview server and `PREVIEW_URL=http://127.0.0.1:3003 node tests/twin-base.cjs`. Test mobile/desktop widths, menu/focus/Escape, missing social setup, reduced motion, no-JavaScript content, image configuration and social link configuration.

After actual image delivery, check the transparent logo boundary at 1x/2x sizes, hero dog visibility at mobile widths, and repeat screenshots before calling the LP complete.

## Photo placement correction — 2026-11-17

The user clarified that the container facility/corgi landscape is the main visual. The uploaded HEIC of two dogs belongs to the artificial-turf dog-run feature. The dogs photo has been moved there, with its own `dogrunPhotoPath`. The hero returns to its landscape frame and an explicit photo placeholder while awaiting the original facility image file. The inline facility image can be seen in chat but still has no accessible original bytes. Earlier placement notes above are superseded by this correction.

## Facility image delivered — 2026-11-17

The user supplied the original 3240×2200 PNG in a ZIP. It is now the hero and terrace image, optimised as `facility-hero.webp` without changing the composition. The built-in “イメージ写真” label is preserved. The hero has a static source for no-JavaScript display; its landscape frame and modest corner radius keep the building, corgi and label visible. The earlier facility-image blocker is resolved. The two-dog HEIC remains exclusively in the artificial-turf feature. The original logo is still pending.

## Rest feature photo — 2026-11-17

The “ひと休みまで、楽しい時間に。” section now uses the supplied cherry-blossom/dog JPG from its ZIP, copied unchanged as `sakura-dog.jpg`. The centered 3:4 CSS frame displays the photograph area while hiding the screenshot’s black bars. The hero and dog-run photographs remain separate. Alt text describes the actual dog and cherry trees, not a terrace.

## Usage rules and hours — 2026-11-17

Confirmed by the user: small/medium dogs only; large dogs cannot use the run; vaccination certificate presentation is not required. Dog run operates 24 hours, café and staffed hours are 10:00–19:00. The previous Instagram-only hours notice is superseded; Instagram remains the source for changes. No assumption of 24-hour staffing, vaccination exemption, year-round operation, or weight thresholds is added.

## Official LINE — 2026-11-17

The user confirmed `https://lin.ee/OwGAOS5` for TWIN BASE. All four LINE buttons (mobile menu, guide, footer, mobile fixed CTA) now use that URL through the shared settings. This supersedes earlier LINE-pending notes for TWIN BASE only; TWIN ASSIST LINE remains pending.
