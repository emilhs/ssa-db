<?php include('navbar.php'); 

$fValid = FALSE;
$lValid = FALSE;

if (isset($_POST["newskater"]) && guard_season($_POST["season"] ?? '')) {
    $fName = $_POST["fName"];
    $lName = $_POST["lName"];
    $gender = $_POST["gender"];
    $age = $_POST["age"];
    $club = $_POST["club"];
    $dob = $_POST["dob"];
    $dob = date("Ymd", strtotime($dob));
    $season = $_POST["season"];

    $sql = "SELECT * FROM skaters WHERE gender = '$gender' AND fName = '$fName' AND lName = '$lName' AND club = '$club' AND age = '$age' AND season = '$season';";
                            $result1 = mysqli_query($conn, $sql) or die(mysqli_error());
                            $count1 = mysqli_num_rows($result1);
    
                            # There is no exact match
                            if ($count1 == 0){
                                # Check existence in any season, just based on name
                                $sql = "SELECT * FROM skaters WHERE fName = '$fName' AND lName = '$lName';";
                                $result1 = mysqli_query($conn, $sql) or die(mysqli_error());
                                $count2 = mysqli_num_rows($result1);

                                # No appearance in any DB
                                if ($count2 == 0){
                                    $skater = "INSERT INTO skaters SET age = '$age', gender = '$gender', fName = '$fName', lName = '$lName', club = '$club', season = '$season', checkInfo = TRUE;";
                                    $result2 = mysqli_query($conn, $skater) or die(mysqli_error());

                                    # retrieve ID
                                    $getskaterID = "SELECT * from skaters WHERE age = '$age' AND gender = '$gender' AND fName = '$fName' AND lName = '$lName' AND club = '$club' AND season = '$season';";
                                    $result3 = mysqli_query($conn, $getskaterID) or die(mysqli_error());
                                    $count3 = mysqli_num_rows($result3);
                                    if ($count3 == 1){
                                        $rows = mysqli_fetch_assoc($result3);
                                        $skaterID = $rows['skaterID'];
                                    }
                                }
                                # This name appears in the DB
                                else if ($count2 > 0){
                                    # First, does this info appear in the season of interest
                                    $relevantinfo = "SELECT * FROM skaters WHERE fName = '$fName' AND lName = '$lName' AND season = '$season';";
                                    $result0 = mysqli_query($conn, $relevantinfo) or die(mysqli_error());
                                    $count0 = mysqli_num_rows($result0);

                                    # there is a match for season and name
                                    if ($count0 == 1){
                                        $rows = mysqli_fetch_assoc($result0);
                                        # get details
                                        $skaterID = $rows['skaterID'];
                                        # there was some contradiction in details so we flag
                                        $skater = "UPDATE skaters SET checkInfo = TRUE WHERE skaterID = '$skaterID' AND season = '$season';";
                                        $result2 = mysqli_query($conn, $skater) or die(mysqli_error());
                                    }
                                    else {
                                        # get most recent info (info from most recent season) based on fname and lname
                                        $getskaterID = "SELECT skaterID, gender, club, age, MAX(season) AS season FROM skaters WHERE fName = '$fName' AND lName = '$lName' GROUP BY fName, lName ORDER BY season ASC;";
                                        $result3 = mysqli_query($conn, $getskaterID) or die(mysqli_error());
                                        $count3 = mysqli_num_rows($result3);
                                         # there is just one skater, get ID right away
                                        if ($count3 == 1){
                                            # is info from most recent season the same as what is being inserted? 
                                            $rows = mysqli_fetch_assoc($result3);
                                            $skaterID = $rows['skaterID'];
                                            $oldseason = $rows['season'];
                                            $oldclub = $rows['season'];
                                            $oldage = $rows['age'];

                                            $FLAG = FALSE;
                                            # if club or age has changed
                                            if ($club != $oldclub or $age != $oldage){
                                                $FLAG = TRUE;
                                            }
                                            # if new season, add to DB
                                            if ($season != $oldseason){
                                                $skater = "INSERT INTO skaters SET fname = '$fName', lname = '$lName', age = '$age', gender = '$gender', club = '$club', skaterID = '$skaterID', season = '$season', checkInfo = '$FLAG';";
                                                $result2 = mysqli_query($conn, $skater) or die(mysqli_error());
                                            }
                                            # set FLAG to True if Flag is true
                                            else if ($FLAG == TRUE){
                                                $skater = "UPDATE skaters SET checkInfo = '$FLAG' WHERE skaterID = '$skaterID' AND season = '$season';";
                                                $result2 = mysqli_query($conn, $skater) or die(mysqli_error());
                                            }
                                        }
                                        # there are multiple skaters with the same name, choose the one that is most similar
                                        # /* WORK IN PROGRESS -- I DON"T THINK THIS IS A CURRENT ISSUE */
                                        else if ($count3 > 1){
                                            echo "DUPLICATE FOUND - CONTACT EMIL";
                                            echo $fName;
                                            echo $lName;
                                            // echo $fName;
                                            // echo $lName;
                                            // $counter = 1;
                                            // while($rows = mysqli_fetch_assoc($result3)){
                                            //     $currentskaterID = $rows['skaterID'];
                                            //     $currentgender = $rows['skaterID'];
                                            //     $currentclub = $rows['skaterID'];
                                            //     $currentage = $rows['age'];

                                            //     $counter++;
                                            // }
                                        }
                                    }
                                }
                            }
                            # There is an exact match
                            else if ($count1 == 1){
                                $rows = mysqli_fetch_assoc($result1);
                                $skaterID = $rows['skaterID'];
                            }
}

if (isset($_GET['f'])){
    $flet = $_GET["f"]; 
    if (in_array($flet, $letters)){
        $fValid = TRUE;
    }
}
if (isset($_GET['l'])){
    $llet = $_GET["l"]; 
    if (in_array($llet, $letters)){
        $lValid = TRUE;
    }
}

?>

<link rel="stylesheet" href="../css/search.css?v=<?php echo filemtime(__DIR__ . '/../css/search.css'); ?>">
<?php
// Every skater (any club), filtered live in the browser.
$allSql = "SELECT s.fName, s.lName, s.club, s.skaterID, MAX(c.alberta) AS alb FROM skaters s LEFT JOIN club c ON c.clubName = s.club GROUP BY s.skaterID ORDER BY s.lName, s.fName;";
$allResult = mysqli_query($conn, $allSql) or die(mysqli_error($conn));
$skatersJs = array();
while ($r = mysqli_fetch_assoc($allResult)) {
    $skatersJs[] = array('id' => (int)$r['skaterID'], 'f' => $r['fName'], 'l' => $r['lName'], 'c' => $r['club'], 'a' => ((int)$r['alb'] === 1));
}
?>

<main class = "search-page">
    <div class = "search-panels">
    <div class = "search-card search-left">
        <div class = "search-card-title bebas-neue">Search</div>
        <div class = "search-bar">
            <input id = "q" type = "search" placeholder = "Enter Details" autocomplete = "off" autocapitalize = "off" spellcheck = "false">
            <label class = "alb-only"><input type = "checkbox" id = "albOnly" checked> Only Alberta athletes</label>
        </div>
        <div class = "search-card-title bebas-neue">First Name</div>
        <div id = "fLetters" class = "letters"></div>
        <div class = "search-card-title bebas-neue">Last Name</div>
        <div id = "lLetters" class = "letters"></div>
    </div>

    <div class = "search-card search-right">
        <div class = "search-card-title bebas-neue">Skaters</div>
        <div id = "results"></div>
        <p id = "count" class = "search-count"></p>
    </div>
    </div>
</main>

<script>
const SKATERS = <?php echo json_encode($skatersJs, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_UNESCAPED_UNICODE); ?>;
const LETTERS = <?php echo json_encode(array_values($letters)); ?>;
const norm = s => (s || '').normalize('NFD').replace(/[\u0300-\u036f]/g, '').toLowerCase();
const esc = s => String(s).replace(/[&<>"']/g, c => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[c]));
SKATERS.forEach(s => { s.key = norm(s.f + ' ' + s.l + ' ' + s.l + ' ' + s.f + ' ' + s.c); s.fn = norm(s.f); s.ln = norm(s.l); });

const params = new URLSearchParams(location.search);
let flet = LETTERS.includes(params.get('f')) ? params.get('f') : '';
let llet = LETTERS.includes(params.get('l')) ? params.get('l') : '';
const input = document.getElementById('q');
input.value = params.get('q') || '';
const MAX_ROWS = 300;
const albBox = document.getElementById('albOnly');
albBox.checked = params.get('alb') !== '0';
albBox.addEventListener('change', update);

function letterRow(el, current, which) {
    el.innerHTML = LETTERS.map(x =>
        '<a class = "' + (x === current ? 'letterbutton-selected' : 'letterbutton') + ' bebas-neue darktext" href = "#" data-w = "' + which + '" data-l = "' + x + '">' + x + '</a>').join('');
}

function update() {
    letterRow(document.getElementById('fLetters'), flet, 'f');
    letterRow(document.getElementById('lLetters'), llet, 'l');
    const tokens = norm(input.value.trim()).split(/\s+/).filter(Boolean);
    const out = document.getElementById('results');
    const count = document.getElementById('count');
    try {
        const p = new URLSearchParams();
        if (input.value.trim()) p.set('q', input.value.trim());
        if (flet) p.set('f', flet);
        if (llet) p.set('l', llet);
        if (!albBox.checked) p.set('alb', '0');
        history.replaceState(null, '', p.toString() ? '?' + p : location.pathname);
    } catch (e) {}
    const pool = albBox.checked ? SKATERS.filter(s => s.a) : SKATERS;
    if (!tokens.length && !flet && !llet) {
        count.textContent = pool.length + (albBox.checked ? ' Alberta skaters' : ' skaters');
        out.innerHTML = '';
        return;
    }
    const f = norm(flet), l = norm(llet);
    const m = pool.filter(s => (!f || s.fn.startsWith(f)) && (!l || s.ln.startsWith(l)) &&
        tokens.every(t => t.length === 1 ? (s.fn.startsWith(t) || s.ln.startsWith(t)) : s.key.includes(t)));
    count.textContent = m.length + ' of ' + pool.length + (albBox.checked ? ' Alberta skaters' : ' skaters') + (m.length > MAX_ROWS ? ' (showing first ' + MAX_ROWS + ')' : '');
    if (!m.length) {
        out.innerHTML = '<p class = "search-empty">No skaters found</p>';
        return;
    }
    out.innerHTML = '<table class = "results-table"><tr class = "head"><th>First Name</th><th>Last Name</th><th>Club</th></tr>' +
        m.slice(0, MAX_ROWS).map((s, i) => '<tr' + (i % 2 ? ' class = "odd"' : '') + ' onclick = "window.location=\'editskater.php?id=' + s.id + '\';"><td>' + esc(s.f) + '</td><td>' + esc(s.l) + '</td><td>' + esc(s.c) + '</td></tr>').join('') + '</table>';
}

document.addEventListener('click', e => {
    const a = e.target.closest('a[data-l]');
    if (!a) return;
    e.preventDefault();
    if (a.dataset.w === 'f') flet = (flet === a.dataset.l) ? '' : a.dataset.l;
    else llet = (llet === a.dataset.l) ? '' : a.dataset.l;
    update();
});
input.addEventListener('input', update);
update();
</script>

<div class = "admin-section-title bebas-neue">Add Skater</div>
<div class = "admin-addwrap">

            <table class = "darktext searchresult arimo">
                <tr class = "toprow">
                    <th class = "row-left">First Name</th>
                    <th class = "row-mid">Last Name</th>
                    <th class = "row-mid">Age</th>
                    <th class = "row-mid">Gender</th>
                    <th class = "row-mid">Club</th>
                    <th class = "row-mid">DOB</th>
                    <th class = "row-mid">Season</th>
                    <th class = "row-right"></th>
                </tr>    
                <form action="" method="post" enctype="multipart/form-data">
                    <tr>    
                    <td><input class = "filltable-wide" type = "text" name = "fName"></input></td>
                    <td><input class = "filltable-wide" type = "text" name = "lName"></input></td>
                    <td><input class = "filltable-wide" type = "text" name = "age"></input></td>
                    <td><input class = "filltable-wide" type = "text" name = "gender"></input></td>
                    <td><input class = "filltable-wide" type = "text" name = "club"></input></td>
                    <td><input class = "filltable-wide" type = "date" name = "dob"></input></td>
                        <td>
                            <select class = "filltable" name = "season">
                                <?php
                                    $sql2 = "SELECT DISTINCT season FROM comps;";
                                    $result = mysqli_query($conn, $sql2);
                                    if($result == TRUE) {
                                        $count = mysqli_num_rows($result);
                                        if($count > 0){
                                            while($rows = mysqli_fetch_assoc($result)){
                                                $s = $rows["season"];
                                                ?>
                                                <option><?php echo $s; ?></option>
                                                <?php
                                            }
                                        }
                                    }
                                ?>
                            </select>
                        </td>
                        <td class = "row-right"><input class = "filesubmission bebas-neue darktext" type = "submit" value="Add Skater" name="newskater"></input></td>
                    </tr>
                </form>
</table>


</div>

<?php include('../footer.php');
