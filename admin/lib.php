<?php
// Shared helpers for the admin data tools (merge.php, checks.php)

function csrf_token() {
    if (empty($_SESSION['csrf'])) { $_SESSION['csrf'] = bin2hex(random_bytes(16)); }
    return $_SESSION['csrf'];
}
function csrf_ok($token) {
    return !empty($_SESSION['csrf']) && is_string($token) && hash_equals($_SESSION['csrf'], $token);
}

// "Élisabeth  Anne-Marie" -> "elisabeth anne marie" (ignores case, accents, hyphens, dots, apostrophes)
function norm_name($s) {
    $map = array(
        'à'=>'a','á'=>'a','â'=>'a','ã'=>'a','ä'=>'a','å'=>'a','ā'=>'a','ç'=>'c','ć'=>'c','č'=>'c','è'=>'e','é'=>'e','ê'=>'e','ë'=>'e','ē'=>'e','ė'=>'e','ę'=>'e',
        'ì'=>'i','í'=>'i','î'=>'i','ï'=>'i','ī'=>'i','ł'=>'l','ñ'=>'n','ń'=>'n','ò'=>'o','ó'=>'o','ô'=>'o','õ'=>'o','ö'=>'o','ø'=>'o','ō'=>'o','œ'=>'oe',
        'š'=>'s','ś'=>'s','ß'=>'ss','ù'=>'u','ú'=>'u','û'=>'u','ü'=>'u','ū'=>'u','ý'=>'y','ÿ'=>'y','ž'=>'z','ź'=>'z','ż'=>'z','æ'=>'ae'
    );
    $s = strtr(mb_strtolower((string)$s, 'UTF-8'), $map);
    $s = preg_replace('/[-.\'’]/u', ' ', $s);
    return trim(preg_replace('/\s+/u', ' ', $s));
}
function has_accents($s) { return (bool)preg_match('/[^\x00-\x7F]/', (string)$s); }
function known_gender($g) { return ($g === 'M' || $g === 'F') ? $g : null; }
function season_label($season) { return sprintf('%02d-%02d', ($season - 1) % 100, $season % 100); }

// Give every season row of a skater the same birthdate and recalculate each season's age category
function apply_dob($conn, $skaterID, $dob) {
    $stmt = mysqli_prepare($conn, "UPDATE skaters SET dob = ? WHERE skaterID = ?");
    mysqli_stmt_bind_param($stmt, 'si', $dob, $skaterID);
    mysqli_stmt_execute($stmt);
    $res = mysqli_query($conn, "SELECT season FROM skaters WHERE skaterID = " . (int)$skaterID);
    $upd = mysqli_prepare($conn, "UPDATE skaters SET age = ? WHERE skaterID = ? AND season = ?");
    while ($r = mysqli_fetch_assoc($res)) {
        $season = (int)$r['season'];
        $age = (string)get_agecat($dob, $season);
        mysqli_stmt_bind_param($upd, 'sii', $age, $skaterID, $season);
        mysqli_stmt_execute($upd);
    }
}

// The season that is currently running (a season is named for the year it ends: 2024-25 = 2025; new season starts in July)
function current_season() {
    return (int)date('Y') + ((int)date('n') >= 7 ? 1 : 0);
}
function season_ok($season) {
    return $season >= 2000 && $season <= current_season() + 1;
}

// Alberta skaters (anyone with a season at an Alberta club)
function alberta_skaters_sql() {
    return "SELECT DISTINCT s3.skaterID FROM skaters s3 JOIN club c3 ON c3.clubName = s3.club WHERE c3.alberta = TRUE";
}

// Competitions saved with an impossible season (e.g. 1970)
function bad_comps($conn) {
    $max = current_season() + 1;
    $res = mysqli_query($conn, "SELECT c.compID, c.compName, c.season, MIN(d.date) AS firstDate,
            (SELECT COUNT(*) FROM results r WHERE r.compID = c.compID) AS results
        FROM comps c LEFT JOIN dates d ON d.compID = c.compID
        WHERE c.season < 2000 OR c.season > $max
        GROUP BY c.compID, c.compName, c.season ORDER BY c.season, c.compName");
    $out = array();
    while ($r = mysqli_fetch_assoc($res)) $out[] = $r;
    return $out;
}

// Skaters that need a human look: bad seasons, big gaps between seasons, club changes, conflicting gender/birthdate
function attention_skaters($conn) {
    $sql = "SELECT s.skaterID, s.season, s.club, s.gender, s.dob, s.age, s.fName, s.lName,
                   (SELECT COUNT(*) FROM results r JOIN comps c ON c.compID = r.compID WHERE r.skaterID = s.skaterID AND c.season = s.season) AS res
            FROM skaters s JOIN (" . alberta_skaters_sql() . ") a ON a.skaterID = s.skaterID
            ORDER BY s.skaterID, s.season";
    $res = mysqli_query($conn, $sql);
    $by = array();
    while ($r = mysqli_fetch_assoc($res)) {
        $id = (int)$r['skaterID'];
        if (!isset($by[$id])) $by[$id] = array('id' => $id, 'rows' => array());
        $by[$id]['rows'][] = array('season' => (int)$r['season'], 'club' => $r['club'], 'gender' => known_gender($r['gender']),
                                   'dob' => $r['dob'], 'age' => $r['age'], 'fName' => $r['fName'], 'lName' => $r['lName'], 'res' => (int)$r['res']);
    }
    $out = array();
    foreach ($by as $id => $s) {
        $rows = $s['rows'];
        $last = end($rows);
        $reasons = array();
        $seasons = array_column($rows, 'season');
        foreach ($seasons as $se) { if (!season_ok($se)) { $reasons['bad'] = 'Impossible season'; break; } }
        for ($i = 1; $i < count($rows); $i++) {
            if ($rows[$i]['season'] - $rows[$i - 1]['season'] > 10) { $reasons['gap'] = 'Gap of more than 10 years between seasons'; }
            if (mb_strtolower(trim($rows[$i]['club'])) !== mb_strtolower(trim($rows[$i - 1]['club']))) { $reasons['club'] = 'Club changed'; }
        }
        $genders = array_unique(array_filter(array_column($rows, 'gender')));
        if (count($genders) > 1) $reasons['gender'] = 'Different genders in different seasons';
        $dobs = array_unique(array_filter(array_column($rows, 'dob')));
        if (count($dobs) > 1) $reasons['dob'] = 'Different birthdates in different seasons';
        if ($reasons) {
            $out[] = array('id' => $id, 'fName' => $last['fName'], 'lName' => $last['lName'], 'rows' => $rows, 'reasons' => $reasons,
                           'sig' => implode(',', array_keys($reasons)) . ':' . implode(',', $seasons));
        }
    }
    usort($out, function ($a, $b) {
        // Serious problems (bad season / gap / conflicts) before plain club changes
        $sa = (int)(count($a['reasons']) === 1 && isset($a['reasons']['club']));
        $sb = (int)(count($b['reasons']) === 1 && isset($b['reasons']['club']));
        return ($sa - $sb) ?: strcmp($a['lName'] . $a['fName'], $b['lName'] . $b['fName']);
    });
    return $out;
}

// ---- Guards that stop impossible seasons from being saved ----
function admin_block($message) {
    echo '<p class = "admin-msg err">' . htmlspecialchars($message) . '</p>';
    return false;
}
// A real calendar date in YYYY-MM-DD form that falls in a possible season
function guard_comp_date($date) {
    $d = DateTime::createFromFormat('Y-m-d', (string)$date);
    if (!$d || $d->format('Y-m-d') !== $date) return admin_block('Nothing was saved: enter a valid competition date.');
    $season = (int)$d->format('Y') + ((int)$d->format('n') > 6 ? 1 : 0);
    if (!season_ok($season)) return admin_block('Nothing was saved: that date gives season ' . $season . ', which is outside 2000-' . (current_season() + 1) . '.');
    return true;
}
// A season year that is possible (2025 means 2024-25)
function guard_season($season) {
    if (!ctype_digit((string)$season) || !season_ok((int)$season)) return admin_block('Nothing was saved: season "' . $season . '" is not valid (it must be between 2000 and ' . (current_season() + 1) . ').');
    return true;
}

// Merge skater $remove into skater $keep (results, points and seasons move over; the removed skater is deleted)
function merge_skaters($conn, $keep, $remove) {
    if ($keep <= 0 || $remove <= 0) return array(false, 'Missing skater IDs.');
    if ($keep === $remove) return array(false, "Can't merge a skater into itself.");
    foreach (array($keep, $remove) as $id) {
        $r = mysqli_query($conn, "SELECT COUNT(*) AS c FROM skaters WHERE skaterID = " . (int)$id);
        $row = mysqli_fetch_assoc($r);
        if ((int)$row['c'] === 0) return array(false, "Skater #$id not found.");
    }
    mysqli_begin_transaction($conn);
    try {
        // 1. All results and points move to the kept skater
        mysqli_query($conn, "UPDATE results SET skaterID = $keep WHERE skaterID = $remove");
        mysqli_query($conn, "UPDATE points SET skaterID = $keep WHERE skaterID = $remove");

        // 2. Seasons: drop the duplicate's row if the kept skater already has that season, otherwise move it over
        $seasons = mysqli_query($conn, "SELECT season FROM skaters WHERE skaterID = $remove");
        while ($s = mysqli_fetch_assoc($seasons)) {
            $season = (int)$s['season'];
            $has = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) AS c FROM skaters WHERE skaterID = $keep AND season = $season"));
            if ((int)$has['c'] > 0) {
                mysqli_query($conn, "DELETE FROM skaters WHERE skaterID = $remove AND season = $season");
            } else {
                mysqli_query($conn, "UPDATE skaters SET skaterID = $keep WHERE skaterID = $remove AND season = $season");
            }
        }

        // 3. The kept skater's birthdate and gender win; fill them in from the other record if missing
        $info = mysqli_fetch_assoc(mysqli_query($conn, "SELECT MAX(dob) AS dob FROM skaters WHERE skaterID = $keep"));
        $dob = $info['dob'];
        if ($dob) apply_dob($conn, $keep, $dob);
        $gRes = mysqli_query($conn, "SELECT gender FROM skaters WHERE skaterID = $keep AND gender IN ('M','F') ORDER BY season DESC LIMIT 1");
        if ($g = mysqli_fetch_assoc($gRes)) {
            $stmt = mysqli_prepare($conn, "UPDATE skaters SET gender = ? WHERE skaterID = ?");
            mysqli_stmt_bind_param($stmt, 'si', $g['gender'], $keep);
            mysqli_stmt_execute($stmt);
        }
        mysqli_commit($conn);
    } catch (Throwable $e) {
        mysqli_rollback($conn);
        return array(false, 'Merge failed: ' . $e->getMessage());
    }
    return array(true, "Merged skater #$remove into #$keep.");
}


// "1:23.456" / "83.456" / "53" -> milliseconds, or null when it isn't a time
function parse_time_ms($s) {
    if (!preg_match('/^\s*(?:(\d+):)?(\d+)(?:\.(\d{1,3}))?\s*$/', (string)$s, $m)) return null;
    $ms = isset($m[3]) ? (int)str_pad($m[3], 3, '0') : 0;
    $total = (((int)$m[1]) * 60 + (int)$m[2]) * 1000 + $ms;
    return $total > 0 ? $total : null;
}
// milliseconds -> "m:ss.mmm"
function format_time_ms($ms) {
    $ms = (int)$ms;
    return sprintf('%d:%02d.%03d', intdiv($ms, 60000), intdiv($ms % 60000, 1000), $ms % 1000);
}

// ---- Lighten the database: remove skaters who have never been in Alberta ----

// Clubs that skaters belong to but that have no province yet (club.alberta is NULL, or the club is missing)
function clubs_without_province($conn) {
    $res = mysqli_query($conn, "SELECT s.club, COUNT(DISTINCT s.skaterID) AS skaters
        FROM skaters s LEFT JOIN club c ON c.clubName = s.club
        WHERE c.clubName IS NULL OR c.alberta IS NULL
        GROUP BY s.club ORDER BY skaters DESC, s.club");
    $out = array();
    while ($r = mysqli_fetch_assoc($res)) $out[] = $r;
    return $out;
}

// IDs of skaters with no season at an Alberta club (only meaningful once every club has a province)
function non_alberta_ids($conn) {
    $res = mysqli_query($conn, "SELECT s.skaterID FROM skaters s LEFT JOIN club c ON c.clubName = s.club
        GROUP BY s.skaterID HAVING COALESCE(MAX(c.alberta), 0) = 0");
    $ids = array();
    while ($r = mysqli_fetch_assoc($res)) $ids[] = (int)$r['skaterID'];
    return $ids;
}

// Everything the lighten page needs: per-skater, per-season counts for never-Alberta skaters
function lighten_data($conn) {
    $ids = non_alberta_ids($conn);
    $skaters = array();
    $seasons = array();
    $bump = function ($season, $key, $n = 1) use (&$seasons) {
        if (!isset($seasons[$season])) $seasons[$season] = array('rows' => 0, 'results' => 0, 'points' => 0);
        $seasons[$season][$key] += $n;
    };
    foreach (array_chunk($ids, 500) as $chunk) {
        $in = implode(',', $chunk);
        $q = mysqli_query($conn, "SELECT skaterID, season, club, fName, lName FROM skaters WHERE skaterID IN ($in) ORDER BY season");
        while ($r = mysqli_fetch_assoc($q)) {
            $id = (int)$r['skaterID']; $se = (int)$r['season'];
            $skaters[$id]['name'] = $r['fName'] . ' ' . $r['lName'];
            $skaters[$id]['club'] = $r['club'];
            $skaters[$id]['s'][$se] = array('rows' => 1, 'results' => 0);
            $bump($se, 'rows');
        }
        $q = mysqli_query($conn, "SELECT r.skaterID, c.season, COUNT(*) AS n FROM results r JOIN comps c ON c.compID = r.compID WHERE r.skaterID IN ($in) GROUP BY r.skaterID, c.season");
        while ($r = mysqli_fetch_assoc($q)) {
            $id = (int)$r['skaterID']; $se = (int)$r['season'];
            if (!isset($skaters[$id]['s'][$se])) $skaters[$id]['s'][$se] = array('rows' => 0, 'results' => 0);
            $skaters[$id]['s'][$se]['results'] += (int)$r['n'];
            $bump($se, 'results', (int)$r['n']);
        }
        $q = mysqli_query($conn, "SELECT p.skaterID, c.season, COUNT(*) AS n FROM points p JOIN comps c ON c.compID = p.compID WHERE p.skaterID IN ($in) GROUP BY p.skaterID, c.season");
        while ($r = mysqli_fetch_assoc($q)) { $bump((int)$r['season'], 'points', (int)$r['n']); }
    }
    ksort($seasons);
    $latest = mysqli_fetch_assoc(mysqli_query($conn, "SELECT MAX(season) AS m FROM skaters"));
    return array('ids' => $ids, 'skaters' => $skaters, 'seasons' => $seasons, 'latest' => (int)$latest['m']);
}

// Delete out-of-province skaters' season entries, results and points for the chosen seasons only
function delete_seasons($conn, $ids, $seasons) {
    $counts = array('rows' => 0, 'results' => 0, 'points' => 0);
    $seasonList = implode(',', array_map('intval', $seasons));
    $compIDs = array();
    $q = mysqli_query($conn, "SELECT compID FROM comps WHERE season IN ($seasonList)");
    while ($r = mysqli_fetch_assoc($q)) $compIDs[] = (int)$r['compID'];
    mysqli_begin_transaction($conn);
    try {
        foreach (array_chunk($ids, 200) as $chunk) {
            $in = implode(',', array_map('intval', $chunk));
            if ($compIDs) {
                $cin = implode(',', $compIDs);
                mysqli_query($conn, "DELETE FROM results WHERE skaterID IN ($in) AND compID IN ($cin)");
                $counts['results'] += mysqli_affected_rows($conn);
                mysqli_query($conn, "DELETE FROM points WHERE skaterID IN ($in) AND compID IN ($cin)");
                $counts['points'] += mysqli_affected_rows($conn);
            }
            mysqli_query($conn, "DELETE FROM skaters WHERE skaterID IN ($in) AND season IN ($seasonList)");
            $counts['rows'] += mysqli_affected_rows($conn);
        }
        mysqli_commit($conn);
    } catch (Throwable $e) {
        mysqli_rollback($conn);
        throw $e;
    }
    return $counts;
}
