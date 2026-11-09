---
name: estimate-moving-price
description: Estimate the price of a home move in Finland from Muuttohaukat's published price list: by apartment size, with or without moving boxes, full service (packing and unpacking) and extras.
---

# Estimate the price of a home move with Muuttohaukat

Muuttohaukat Oy (https://muuttohaukat.com) is a Finnish moving company. This skill tells you where its published prices are and how to get an estimate without guessing.

## Use the live sources, do not calculate from memory

1. **In a browser that supports WebMCP**, open https://muuttohaukat.com/ and call the tool `estimate_moving_price`. It runs the same calculator as the page and returns a price range in euros.
2. **Otherwise** read the price list at https://muuttohaukat.com/kotimuutto/muuttopalvelun-hinta/ (example prices by apartment size, with and without boxes, and the terms of the prices). Prices change; always quote the page, not an earlier answer.

## What the estimate depends on

- **Apartment size** in square metres. The calculator covers 20–150 m². For a bigger home, a quote is requested.
- **Package** (`package`): `perus` = move without boxes, `laatikot` = move and moving boxes, `taysi` = full service (packing, transport, unpacking), `taysi-laatikot` = full service and boxes.
- **Extras** (`extras`): `siivous` = move-out cleaning, `piano` = piano or safe, `kaluste` = furniture assembly, `varastointi` = storage. Some of these are only quoted, not priced; the tool says which.
- **Box rental** (`box_rental`): moving boxes rented separately, for packages that do not include boxes.
- **Moving to another city** (`from_city`, `to_city`): names of Finnish municipalities. When the cities are more than 50 km apart, the estimate gets 0.80 euros for every kilometre between them (an estimated road distance); for shorter distances there is no distance fee.

## Good to know when you answer

- The result is an estimate. The final price is confirmed in Muuttohaukat's offer and depends on, for example, the amount of belongings and the distance.
- Apartments under 80 m² can also be ordered directly at https://tilaamuutto.fi.
- Business moves are quoted separately: https://muuttohaukat.com/yritysmuutto/.
- Ready for the next step? See the skill `request-moving-quote`.
