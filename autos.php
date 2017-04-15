<?php
require_once "pdo.php";
// Demand a GET parameter
if ( ! isset($_GET['name']) || strlen($_GET['name']) < 1  ) {   // where is 'name' come from
    die('Name parameter missing');
}

// If the user requested logout go back to index.php
if ( isset($_POST['logout']) ) {
    header('Location: index.php');
    exit();
}

$failure = false;  // If we have no POST data
$success = "Record inserted";

if ( isset($_POST['make']) && isset($_POST['year']) && isset($_POST['mileage']) ) {
    if ( strlen($_POST['make']) < 1 ) {
        $failure = "Make is required";
    }
    else {
        if(!is_numeric($_POST['year']) || !is_numeric($_POST['mileage'])){
            $failure = "Mileage and year must be numeric";
        }
    }
}

?>
<!DOCTYPE html>
<html>
<head>
<title>Yu Liu's Tracking Auto</title>
<?php require_once "bootstrap.php"; ?>
</head>
<body>
<div class="container">

<?php
    if(isset($_REQUEST['name'])){
        echo "<h1>Tracking Autos for ";
        echo htmlentities($_REQUEST['name']);
        echo "</h1>";
    }
?>

<?php
if ( $failure !== false ) {
    echo('<p style="color: red;">'.htmlentities($failure)."</p>\n");
}
else{
    echo('<p style="color: green;">'.htmlentities($success)."</p>\n");
}
?>

<form method="post">
<p>Make:
<input type="text" name="make" size="40"></p>
<p>Year:
<input type="text" name="year"></p>
<p>Mileage:
<input type="text" name="mileage"></p>
<input type="submit" name="add" value="Add">
<input type="submit" name="logout" value="Logout">
</form>

<h1>Automobiles</h1>

<ul>
<?php
if ($failure ==false){
    $stmt = $pdo->prepare("INSERT INTO autos (make, years, mileage) VALUES (:mk, :yr, :mi)");
    $stmt->execute(array(
        ':mk' => htmlentities($_POST['make']),
        ':yr' => $_POST['year'],
        ':mi' => $_POST['mileage'])
    );

    $stmt1 = $pdo->query("SELECT make, years, mileage FROM autos");
    while ( $row = $stmt1->fetch(PDO::FETCH_ASSOC) ) {
        echo "<li>";
        echo($row['years']);
        echo(" ");
        echo($row['make']);
        echo(" / ");
        echo($row['mileage']);
        echo("</li>");
    }
}
?>
</ul>

</div>
</body>
</html>
