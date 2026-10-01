<?php
// Who appears on the public pages.
// When this is true, a skater is shown in search, rankings, club pages and profiles only if a birthdate is on file.
// Set it to false to show every skater again.
if (!defined('REQUIRE_BIRTHDAY')) define('REQUIRE_BIRTHDAY', true);

// SQL condition for "this skater may be shown". $col is the skaterID column to test, e.g. 'skaterID' or 'S.skaterID'.
function visible_sql($col = 'skaterID') {
    if (!REQUIRE_BIRTHDAY) return '1 = 1';
    return "$col IN (SELECT DISTINCT skaterID FROM skaters WHERE dob IS NOT NULL)";
}
