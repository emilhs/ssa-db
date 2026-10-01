<?php include('navbar.php');
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !csrf_ok($_POST['csrf'] ?? '')) { $_POST = array(); admin_block('Your session expired, nothing was changed. Please try again.'); }

if (isset($_POST["makealberta"])) {
    $clubName = $_POST["clubName"];

    if ($clubName != NULL){
        $sql = "UPDATE club SET alberta = TRUE
        WHERE clubName = '$clubName';";
        $result1 = mysqli_query($conn, $sql) or die(mysqli_error());
    }
}

if (isset($_POST["notalberta"])) {
    $clubName = $_POST["clubName"];

    if ($clubName != NULL){
        $sql = "UPDATE club SET alberta = FALSE
        WHERE clubName = '$clubName';";
        $result1 = mysqli_query($conn, $sql) or die(mysqli_error());
    }
}


$sqlCurrent = "SELECT DISTINCT clubName FROM club ORDER BY clubName ASC;";
$current = array();
$result = mysqli_query($conn, $sqlCurrent);
// Verify that SQL Query is executed or not
if($result == TRUE) {
    // Count the number of rows which will be a way to verify if there is data in the database
    $count = mysqli_num_rows($result);
    // Initialize display of Athlete Number 
    if($count > 0){
        ?>
        <table>
        <?php
        while($rows = mysqli_fetch_assoc($result)){
            $club = $rows['clubName'];
            $current[] = $club;
        }
        ?>
        </table>
        <?php
    }
}

$sqlAll = "SELECT DISTINCT club FROM skaters ORDER BY club ASC;";
$result = mysqli_query($conn, $sqlAll);
// Verify that SQL Query is executed or not
if($result == TRUE) {
    // Count the number of rows which will be a way to verify if there is data in the database
    $count = mysqli_num_rows($result);
    // Initialize display of Athlete Number 
    if($count > 0){
        while($rows = mysqli_fetch_assoc($result)){
            $clubName = $rows['club'];
            #echo in_array($clubName, $current);
            if (!in_array($clubName, $current)){
                $clubInsert = "INSERT INTO club SET clubName = '$clubName', alberta = NULL";
                $result3 = mysqli_query($conn, $clubInsert) or die(mysqli_error());
            }
        }
    }
}
?>
<link rel="stylesheet" href="../css/admin.css?v=<?php echo filemtime(__DIR__ . '/../css/admin.css'); ?>">
<?php
$token = csrf_token();
$clubRows = array();
$cq = mysqli_query($conn, "SELECT c.clubName, c.alberta, (SELECT COUNT(DISTINCT s.skaterID) FROM skaters s WHERE s.club = c.clubName) AS skaters FROM club c ORDER BY c.alberta ASC, c.clubName ASC");
while ($cr = mysqli_fetch_assoc($cq)) $clubRows[] = $cr;
$unassigned = 0;
foreach ($clubRows as $cr) if ($cr['alberta'] === null) $unassigned++;
?>

<main class = "admin-page admin-wide">
    <h1 class = "admin-h1 bebas-neue">Assign Province</h1>
    <p class = "admin-hint">Mark each club as Alberta or not. <?php echo $unassigned ? '<strong>' . $unassigned . '</strong> club' . ($unassigned == 1 ? '' : 's') . ' still need' . ($unassigned == 1 ? 's' : '') . ' a province.' : 'Every club has a province.'; ?></p>

    <section class = "edit-card">
        <?php foreach ($clubRows as $i => $cr) { ?><form id = "cl<?php echo $i; ?>" method = "post"><input type = "hidden" name = "csrf" value = "<?php echo $token; ?>"><input type = "hidden" name = "clubName" value = "<?php echo htmlspecialchars($cr['clubName']); ?>"></form><?php } ?>
        <div class = "edit-scroll">
        <table class = "edit-table filterable">
            <tr><th>Club</th><th>Skaters</th><th>Province</th></tr>
            <?php foreach ($clubRows as $i => $cr) { $f = 'cl' . $i; $a = $cr['alberta']; ?>
            <tr>
                <td><?php echo htmlspecialchars($cr['clubName']); ?><?php if ($a === null) { ?> <span class = "pair-tag pair-warn">Needs province</span><?php } ?></td>
                <td><?php echo (int)$cr['skaters']; ?></td>
                <td class = "seg">
                    <?php if ($a !== null && $a > 0) { ?><span class = "pair-btn sel">Alberta</span><?php } else { ?><button form = "<?php echo $f; ?>" class = "pair-btn" type = "submit" name = "makealberta" value = "1">Alberta</button><?php } ?><?php if ($a !== null && $a <= 0) { ?><span class = "pair-btn sel">Not Alberta</span><?php } else { ?><button form = "<?php echo $f; ?>" class = "pair-btn" type = "submit" name = "notalberta" value = "1">Not Alberta</button><?php } ?>
                </td>
            </tr>
            <?php } ?>
        </table>
        </div>
    </section>
</main>
<?php
$filterLabel = 'Filter clubs'; include('rowfilter.php');
include("../footer.php");

$getinfo = "SELECT * FROM club;"


?>