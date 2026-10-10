# Aktivitetskalender – kildeprofil

Kildekandidat 5.3.2 er synkronisert fra den lokale, moduldelte pluginen. Tidligere main inneholder 4.7, mens den gamle opplastede ZIP-en inneholder 5.3. Arkivet oppdateres med gjennomgangen 2026-10-10; ingen ny stabil release er publisert.

Inngang: ms-activities.php. Moduler: includes/helpers.php, rendering.php, ajax.php og shortcodes.php. Ressurser lastes fra public/css/ms-activities.css og public/js/ms-activities.js. De gamle, ubrukte assets-filene er fjernet.

Kontrakter: aktiviteter; ms_calendar, ms_activities, ms_activities_toggle; ms_load_calendar med m/y; group_ms_activities og f1–f5 for date_from, date_to, status, link og activity_info. Frontend kan lese lagrede metadata når ACF mangler. Redigering bruker ACF.

GitHub Actions kontrollerer syntaks. Ingen faktisk WordPress/ACF-integrasjon eller kompatibilitetsmatrise er verifisert i repository-ryddingen. Kontroller datoer, flere shortcodes, tema, mobil og tastatur på staging før stabil utgivelse.

.cpanel.yml definerer bred kopiering av rotens ikke-skjulte filer til en servermappe. Automatisk trigger og faktisk deploymiljø er ikke etablert. Derfor klargjøres endringen på en separat gren inntil deployflyten er avklart.
