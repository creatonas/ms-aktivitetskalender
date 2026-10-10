# Aktivitetskalender

WordPress-plugin for aktiviteter med liste, månedsoversikt og kombinert visning. Advanced Custom Fields brukes til redigering av aktivitetsdata.

## Endringer i 5.3.2

- Infoikonet rendres som et eksplisitt HTML-element med fast størrelse, mørk bakgrunn og hvitt symbol.
- Ikonet er ikke lenger avhengig av temaets font eller pseudo-element-rendering.

## Endringer i 5.3.1

- Infoikonet har fylt mørk bakgrunn og hvitt symbol.
- Ressursversjonen er oppdatert slik at nettlesere og mellomlagring henter den nye CSS-filen.

## Shortcodes

- `[ms_activities]` viser kommende aktiviteter som liste.
- `[ms_calendar]` viser aktiviteter i en månedsoversikt.
- `[ms_activities_toggle]` viser en tilgjengelig bryter mellom liste og måned.

## Oppgradering fra 5.2

Versjon 5.3 beholder posttypen `aktiviteter`, alle shortcode-navn og ACF-feltene `date_from`, `date_to`, `status`, `link` og `activity_info`. Ingen datamigrering er nødvendig.

## Endringer i 5.3

- CSS og JavaScript er flyttet til egne, betinget lastede filer.
- AJAX-parametere valideres og begrenses.
- Ugyldige datoer og statuser håndteres kontrollert.
- Kalenderen henter bare aktiviteter som kan overlappe valgt måned.
- Modal, kalendernavigasjon og visningsbryter har forbedret tastatur- og skjermleserstøtte.
- Frontend kan lese lagrede felt uten fatal feil dersom ACF midlertidig mangler.
- Arrangementlenken vises som knapp i modalvinduet.

Aktivitetspostene slettes ikke ved deaktivering eller avinstallering.

## Repository-struktur

`ms-activities.php` laster modulene i `includes/` og ressursene i `public/`. Kildeversjon er 5.3.2. Den tidligere rot-ZIP-en 5.3 er fjernet fordi den ikke samsvarer med kilden. Pakk runtime-filene med `ms-activities/` som toppmappe.

GitHub Actions kontrollerer PHP/JavaScript-syntaks. WordPress/ACF, tema, tastaturbruk og flere kalendere på samme side krever integrasjonstest. Dette er kildekodevedlikehold, ikke en stabil release eller produksjonsdeploy.

`.cpanel.yml` definerer serverkopiering. Avklar trigger, gren og destinasjon før merge/deploy.
