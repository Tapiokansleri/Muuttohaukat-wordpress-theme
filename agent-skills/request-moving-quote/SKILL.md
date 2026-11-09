---
name: request-moving-quote
description: Prepare a quote request for a home move or a business move with Muuttohaukat: which address to open, which choices can be prefilled, and what a person must still enter and send.
---

# Request a moving quote from Muuttohaukat

Muuttohaukat Oy (https://muuttohaukat.com) is a Finnish moving company. A quote request is made with a web form; a person sends it.

## Open the form

- **Home move:** https://muuttohaukat.com/tarjouspyynto/kotimuutto/
- **Business move:** https://muuttohaukat.com/tarjouspyynto/yritysmuutto/

In a browser that supports WebMCP, the tool `open_prefilled_quote_request` on https://muuttohaukat.com/ opens the home move form with the choices below already filled in. It never sends the form.

## Choices that can be prefilled (home move form, query parameters)

| Parameter | Meaning | Values |
|---|---|---|
| `pa` | apartment size in m² | whole number 20–150 |
| `pkt` | package | `perus`, `laatikot`, `taysi`, `taysi-laatikot` |
| `lisa` | extras, comma separated | `siivous`, `piano`, `kaluste`, `varastointi` |
| `laatikot` | number of moving boxes | whole number 1–500 |
| `paivat` | box rental days | whole number 1–90 |
| `matka` | box delivery distance | `alle-12-km`, `12-30-km`, `yli-30-km`, `nouto` |
| `arvio` | the price range you showed, in euros | `low-high`, e.g. `970-1290` |
| `lahto`, `kohde` | the cities, when moving to another city | municipality names, e.g. `Helsinki` and `Tampere` |
| `km` | the distance between them that the estimate used | whole number |

Example: `https://muuttohaukat.com/tarjouspyynto/kotimuutto/?pa=60&pkt=laatikot&lisa=siivous`

## What a person must do

- Enter the move date, the addresses and the contact details. Do not invent or guess them.
- Check the form and send it. Do not send the form for the user without the user's clear confirmation of the details.
- Muuttohaukat answers the request with a quote; the price in `arvio` is only the estimate that was shown to the user.

Estimate first? See the skill `estimate-moving-price`.
