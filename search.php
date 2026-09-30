<?php include('navbar.php');

// Every skater that has raced in an Alberta club; filtered live in the browser.
$sql = "SELECT fName, lName, club, skaterID FROM skaters AS A NATURAL JOIN (SELECT clubName AS club FROM club WHERE alberta = TRUE) AS B GROUP BY skaterID ORDER BY lName, fName;";
$result = mysqli_query($conn, $sql) or die(mysqli_error($conn));
$skaters = array();
while ($rows = mysqli_fetch_assoc($result)) {
    $skaters[] = array('id' => (int)$rows['skaterID'], 'f' => $rows['fName'], 'l' => $rows['lName'], 'c' => $rows['club']);
}
?>

<link rel="stylesheet" href="css/search.css?v=<?php echo filemtime(__DIR__ . '/css/search.css'); ?>">

<main class = "search-page">
    <div class = "search-hero">
        <img src = "images/DSC00762-scaled.jpg" alt = "">
        <div class = "search-frost">
            <div class = "search-title bebas-neue">Skater Search</div>
        </div>
    </div>

    <div class = "search-panels">
    <div class = "search-card search-left">
        <div class = "search-card-title bebas-neue">Search</div>
        <div class = "search-bar">
            <input id = "q" type = "search" placeholder = "Enter Details" autocomplete = "off" autocapitalize = "off" spellcheck = "false">
        </div>
        <div class = "search-card-title bebas-neue">First Name</div>
        <div id = "fLetters" class = "letters"></div>
        <div class = "search-card-title bebas-neue">Last Name</div>
        <div id = "lLetters" class = "letters"></div>
    </div>

    <div class = "search-card search-right">
        <div class = "search-card-title bebas-neue">Skaters</div>
        <p class = "search-note">Only skaters that raced one of the <a href = "competitions.php">competitions</a> in the database can be found.</p>
        <div id = "results"></div>
        <p id = "count" class = "search-count"></p>
    </div>
    </div>
</main>

<script>
const SKATERS = <?php echo json_encode($skaters, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_UNESCAPED_UNICODE); ?>;
const LETTERS = <?php echo json_encode(array_values($letters)); ?>;
const norm = s => (s || '').normalize('NFD').replace(/[̀-ͯ]/g, '').toLowerCase();
const esc = s => String(s).replace(/[&<>"']/g, c => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[c]));
SKATERS.forEach(s => { s.key = norm(s.f + ' ' + s.l + ' ' + s.l + ' ' + s.f); s.fn = norm(s.f); s.ln = norm(s.l); });

const params = new URLSearchParams(location.search);
let flet = LETTERS.includes(params.get('f')) ? params.get('f') : '';
let llet = LETTERS.includes(params.get('l')) ? params.get('l') : '';
const input = document.getElementById('q');
input.value = params.get('q') || '';

function letterRow(el, current, which) {
    el.innerHTML = LETTERS.map(x =>
        '<a class = "' + (x === current ? 'letterbutton-selected' : 'letterbutton') + ' bebas-neue darktext" href = "#" data-w = "' + which + '" data-l = "' + x + '">' + x + '</a>').join('');
}

function update() {
    letterRow(document.getElementById('fLetters'), flet, 'f');
    letterRow(document.getElementById('lLetters'), llet, 'l');
    const tokens = norm(input.value.trim()).split(/\s+/).filter(Boolean);
    const out = document.getElementById('results');
    try {
        const p = new URLSearchParams();
        if (input.value.trim()) p.set('q', input.value.trim());
        if (flet) p.set('f', flet);
        if (llet) p.set('l', llet);
        history.replaceState(null, '', p.toString() ? '?' + p : location.pathname);
    } catch (e) {}
    const count = document.getElementById('count');
    if (!tokens.length && !flet && !llet) {
        count.textContent = SKATERS.length + ' eligible skaters';
        out.innerHTML = '';
        return;
    }
    const f = norm(flet), l = norm(llet);
    const m = SKATERS.filter(s => (!f || s.fn.startsWith(f)) && (!l || s.ln.startsWith(l)) && tokens.every(t => t.length === 1 ? (s.fn.startsWith(t) || s.ln.startsWith(t)) : s.key.includes(t)));
    count.textContent = m.length + ' of ' + SKATERS.length + ' eligible skaters';
    if (!m.length) {
        out.innerHTML = '<p class = "search-empty">No skaters found</p>';
        return;
    }
    out.innerHTML = '<table class = "results-table"><tr class = "head"><th>First Name</th><th>Last Name</th><th>Club</th></tr>' +
        m.map((s, i) => '<tr' + (i % 2 ? ' class = "odd"' : '') + ' onclick = "window.location=\'athlete.php?id=' + s.id + '\';"><td>' + esc(s.f) + '</td><td>' + esc(s.l) + '</td><td>' + esc(s.c) + '</td></tr>').join('') + '</table>';
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
<?php include('footer.php');
