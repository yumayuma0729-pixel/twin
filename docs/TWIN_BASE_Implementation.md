# TWIN BASE implementation

- Independent LP: `/twin-base/`, with isolated HTML/CSS/JavaScript and local fonts.
- Vehicle-site changes are confined to related-business links on home, business and company pages.
- Requirements source: `TWIN_BASE_Requirements.md`, provided by the user.
- Milk white `#FDFBF7`, cream `#F6EFE6`, mint `#DDEEE3`, blue `#DCE8F2`, peach `#F6E1D6`; text `#6F655D`.
- Zen Maru Gothic Medium and M PLUS Rounded 1c Regular come from the official Google Fonts repository and are served locally as WOFF2. OFL copyright/license notices are in `public/twin-base/fonts/`.
- Instagram and LINE are intentionally unset. Buttons display an accessible preparation dialog until confirmed official URLs are supplied. No invented accounts or collection forms.
- Address is the supplied dog-run address, **not the car dealership's address**. Maps links to an address search; no fabricated coordinates, hours, prices or parking capacity.
- Facilities with unconfirmed details are labelled; events are examples with no announced date or commitment. Structured data contains only supplied business identity, address and parent organization.
- Preview remains `noindex,follow`, consistent with the current temporary site.

## Assets still required

The text specification was downloaded as a file. The two inline images could be visually inspected in chat, but their original bytes were not exposed as downloadable files or workspace files. No synthetic copies have been substituted for the logo or facility photograph.

The page currently has **explicit temporary photo placeholders** and a typographic brand fallback. It is not claimed to satisfy the specification's image-delivery requirement until these files are supplied:

1. Original TWIN BASE logo PNG/JPEG. Remove only its circular background, preserve the dog/fence/grass/lettering/colors, save tightly cropped transparent PNG as `/twin-base/assets/logo.png`.
2. Original facility photograph PNG/JPEG. Preserve its composition and dog, optimise without redesigning the photograph, save `/twin-base/assets/facility.webp` (or JPG).

Set paths in `public/twin-base/settings.js`:

```js
logoPath:'/twin-base/assets/logo.png',
facilityPhotoPath:'/twin-base/assets/facility.webp'
```

The hero uses the entire image with `object-fit: contain` inside an organic frame, preserving the dog. Other image slots also use `contain`. Failed/unset image paths keep the clearly labelled placeholder and cause no broken-image icon. Configured images replace placeholders only after successful loading.

## Confirmed social URLs

Set `instagramUrl` and `lineUrl` in `settings.js`. HTTPS Instagram and official LINE domains are validated before links are enabled. New-tab links include `noopener noreferrer`. No dummy external account is used.

## Validation

Run the zero-dependency preview server and `PREVIEW_URL=http://127.0.0.1:3003 node tests/twin-base.cjs`. Test mobile/desktop widths, menu/focus/Escape, missing social setup, reduced motion, no-JavaScript content, image configuration and social link configuration.

After actual image delivery, check the transparent logo boundary at 1x/2x sizes, hero dog visibility at mobile widths, and repeat screenshots before calling the LP complete.
