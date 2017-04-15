<?php
require_once "pdo.php";
// Demand a GET parameter

// session_start();

?>
<!DOCTYPE html>
<html>
<head>
<title>Yu Liu's Profile View</title>
<?php require_once "bootstrap.php"; ?>
</head>
<body>
<div class="container">

<h1>Profile Information</h1>

<?php
$stmt1 = $pdo->prepare("SELECT first_name, last_name, headline, summary, email, profile_id FROM Profile WHERE profile_id = :xyz");
// $stmt = $pdo->prepare("SELECT make, auto_id FROM autos where auto_id = :xyz");
$stmt1->execute(array(":xyz" => $_GET['profile_id']));
while ( $row = $stmt1->fetch(PDO::FETCH_ASSOC) ) {
    echo "<p>";
    echo("First Name: "." ".$row['first_name']);
    echo("</p><br>");
    echo "<p>";
    echo("Last Name: "." ".$row['last_name']);
    echo("</p><br>");
    echo "<p>";
    echo("Email: "." ".$row['email']);
    echo("</p><br>");
    echo "<p>";
    echo("Headline: <br>");
    // echo("<p>");
    echo($row['headline']);
    echo("</p><br>");
    echo "<p>";
    echo("Summary: <br>");
    // echo("<p>");
    echo($row['summary']);
    echo("</p><br>");
}


echo("<p>Education</p><ul>");
$stmt0 = $pdo->prepare("SELECT year, name FROM Education JOIN Institution ON Education.institution_id = Institution.institution_id WHERE profile_id = :xyz");
// $stmt = $pdo->prepare("SELECT make, auto_id FROM autos where auto_id = :xyz");
$stmt0->execute(array(":xyz" => $_GET['profile_id']));
while ( $row = $stmt0->fetch(PDO::FETCH_ASSOC) ) {
    echo "<li>";
    echo($row['year'].": ".$row['name']);
    echo("</li>");
}
echo("</ul>");


echo("<p>Positions</p><ul>");
$stmt1 = $pdo->prepare("SELECT year, description FROM Position WHERE profile_id = :xyz");
// $stmt = $pdo->prepare("SELECT make, auto_id FROM autos where auto_id = :xyz");
$stmt1->execute(array(":xyz" => $_GET['profile_id']));
while ( $row = $stmt1->fetch(PDO::FETCH_ASSOC) ) {
    echo "<li>";
    echo($row['year'].": ".$row['description']);
    echo("</li>");
}
echo("</ul>");
?>

<a href="index.php">Done</a>

</div>
</body>
</html>
