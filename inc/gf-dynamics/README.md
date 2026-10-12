# GF Dynamics (theme)

Forwards Gravity Forms submissions to the existing Azure Function `AddOfferToDynamics` using the **same JSON envelope** as LibreForm. Lives in the Muuttohaukat theme under `inc/gf-dynamics*` so it survives with theme updates and does not need a separate plugin.

LibreForm forwarding in `inc/forms.php` is untouched.

## Requirements

- Gravity Forms 2.5+ (Basic or Pro) — without GF this code is a no-op
- LibreForm posts still present (their numeric IDs are impersonated in the payload)

## Setup

1. Install and activate Gravity Forms.
2. Open **Lomakkeet → Dynamics** — confirm the endpoint and click **Testaa yhteys**.
3. Rebuild each offer form in Gravity Forms. For `Toimipiste`, set choice **values** to branch emails (`helsinki@muuttohaukat.com`, etc.), not city names.
4. Open the form → **Asetukset → Dynamics** → add a feed:
   - **Impersonate LibreForm** — pick the matching LibreForm post (local reference IDs: kotimuutto `1793`, yritysmuutto `1791`, tarvikkeet `1795`; production IDs may differ).
   - **Field mapping** — map LibreForm keys (`Nimi`, `LähtöPA`, …) to GF fields.
   - Optionally enable **Test mode** to store the payload without calling Azure.
5. Endpoint defaults to the theme’s D365 setting (`Teeman asetukset`). Override under **Lomakkeet → Asetukset → Dynamics** if needed.

## Checkbox rules (must match LibreForm)

| Key | Checked value | Unchecked |
|-----|---------------|-----------|
| `LähtöHissi`, `KohdeHissi` | `on` | omit from payload |
| Service toggles (`Pakkauspalvelu`, …) | `Kyllä` | omit |
| `Käyttöehdot` | `Hyväksytty` | omit (required on form) |

A yes/no question asked as a radio (e.g. lift: **Ei** / **On**) can be mapped to a checkbox key: negative answers (`Ei`, `No`, `0`, `false`, `off`) are omitted like an unchecked box, any other answer becomes the canonical checked value.

## Form rules (`form-rules.php`)

Every form with an active Dynamics feed gets these, found through the feed's field map (no field IDs in code):

- **Dates** mapped to `Muuttopvm`, `TarvikkeidenToimituspvm`, `TarvikkeidenPalautuspvm`: not today, not in the past, return not before delivery or moving date (server-side, same messages as the LibreForm form); the date pickers start from tomorrow. Use the date format `dd.mm.yyyy`.
- **?date=Y-m-d** (moving day calendar links) fills a date field whose *Allow field to be populated dynamically* parameter is `date`.
- **Lead event**: a successful submission pushes `{"event": "gf_lead_submit", "gf_form_id": …, "wplf_form_id": …, "lomake": "kotimuutto"}` to the dataLayer. Before a GF form replaces a LibreForm one, add a GTM Custom Event trigger on `gf_lead_submit` to the lead conversion tags (GA4 and Google Ads); the current trigger only fires for forms with the class `wplf-form`.

## Lisätiedot template

Fields Dynamics has no key for (alternative dates, preferred contact method, extra services…) can be sent inside `Lisätiedot`. In the feed, fill **Lisätiedot template** and insert fields with the merge tag picker:

```text
{Lisätiedot:38}

Vaihtoehtoiset muuttopäivät: {Vaihtoehtoiset muuttopäivät:43}
Toivottu yhteydenottotapa: {Toivottu yhteydenottotapa:45}
```

- A line whose merge tags are all empty is left out.
- A filled template replaces the `Lisätiedot` field mapping; an empty template keeps the mapping.
- Check the result in test mode (entry → **Dynamics** box → Payload), or with **Lähetä uudelleen** after editing the template.

## Resend

On an entry’s detail screen, the **Dynamics** meta box shows status and a **Lähetä uudelleen** button.

## Verify offline

```bash
php inc/gf-dynamics/docs/verify-payload-shape.php
```
