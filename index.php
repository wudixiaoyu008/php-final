<?php // Do not put any HTML above this line
session_start();
require_once "pdo.php";
require_once 'util.php';
?>

<!DOCTYPE html>
<html>
<head>
<title>Yu Liu - Resume Registry</title>
<?php require_once "bootstrap.php"; ?>
</head>
<body>
<div class="container">
<h1>Yu Liu's Resume Registry</h1>
<p>

<?php
if(!isset($_SESSION['name']) || !isset($_SESSION['user_id'])){
    // show please login
    // show go to add data without Login
    echo('<a href="login.php">Please log in</a>');
    $stmt = $pdo->query("SELECT first_name, last_name, headline, user_id, profile_id FROM Profile");

    if ($stmt->fetch(PDO::FETCH_ASSOC)){
        $stmt = $pdo->query("SELECT first_name, last_name, headline, user_id, profile_id FROM Profile");
        echo('<table border="1">'."\n");
        echo('<tr><th>Name</th><th>Headline</th></tr>');
        while ( $row = $stmt->fetch(PDO::FETCH_ASSOC) ) {
            echo('<tr><td>');
            echo('<a href="view.php?profile_id='.$row['profile_id'].'">'.htmlentities($row['first_name'])." ".htmlentities($row['last_name']).'</a>');
            echo("</td><td>");
            echo(htmlentities($row['headline']));
            // echo("</td><td>");
            // echo('<a href="edit.php?profile_id='.$row['profile_id'].'">Edit</a> / ');
            // echo('<a href="delete.php?user_id='.$row['profile_id'].'">Delete</a>');
            echo("</td></tr>\n");
        }
        echo('</table>');
    }
    else{
        echo('No rows found');
    }

}

if (isset($_SESSION['name']) && isset($_SESSION['user_id']) ){

    if ( isset($_SESSION['success']) ) {
        echo '<p style="color:green">'.$_SESSION['success']."</p>\n";
        unset($_SESSION['success']);
    }

    $stmt = $pdo->query("SELECT first_name, last_name, headline, user_id, profile_id FROM Profile");

    if ($stmt->fetch(PDO::FETCH_ASSOC)){
        $stmt = $pdo->query("SELECT first_name, last_name, headline, user_id, profile_id FROM Profile");
        echo('<table border="1">'."\n");
        echo('<tr><th>Name</th><th>Headline</th><th>Action</th></tr>');
        while ( $row = $stmt->fetch(PDO::FETCH_ASSOC) ) {
            echo('<tr><td>');
            echo('<a href="view.php?profile_id='.$row['profile_id'].'">'.htmlentities($row['first_name'])." ".htmlentities($row['last_name']).'</a>');
            echo("</td><td>");
            echo(htmlentities($row['headline']));
            echo("</td><td>");
            echo('<a href="edit.php?profile_id='.$row['profile_id'].'">Edit</a> / ');
            echo('<a href="delete.php?profile_id='.$row['profile_id'].'">Delete</a>');
            echo("</td></tr>\n");
        }
        echo('</table>');
    }
    else{
        echo('No rows found');
    }
    echo('<br>');
    echo('<a href="add.php">Add New Entry</a><br>');
    echo('<a href="logout.php">Logout</a>');
}

?>

</div>
</body>
</html>
