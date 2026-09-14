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
