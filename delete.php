<?php
require_once "pdo.php";
session_start();

if ( isset($_POST['delete']) && isset($_POST['profile_id']) ) {
    $sql = "DELETE FROM Profile WHERE profile_id = :zip";
    $stmt = $pdo->prepare($sql);
    $stmt->execute(array(':zip' => $_POST['profile_id']));
    $_SESSION['success'] = 'Profile deleted';
    header( 'Location: index.php' ) ;
    return;
}

// Guardian: Make sure that profile_id is present
if ( ! isset($_GET['profile_id']) ) {
  $_SESSION['error'] = "Missing profile_id";
  header('Location: index.php');
  return;
}

if(isset($_POST['cancel'])){
    header('Location: index.php');
    return;
}

$stmt = $pdo->prepare("SELECT first_name, last_name, profile_id FROM Profile where profile_id = :xyz");
$stmt->execute(array(":xyz" => $_GET['profile_id']));
$row = $stmt->fetch(PDO::FETCH_ASSOC);
if ( $row === false ) {
    $_SESSION['error'] = 'Bad value for profile_id';
    header( 'Location: index.php' ) ;
    return;
}

?>
<h1>Deleting Profile</h1>

<?php
    echo('<p>First Name: <span>'.$row['first_name'].'</span></p>');
    echo('<p>Last Name: <span>'.$row['last_name'].'</span></p>');
?>

<form method="post">
<input type="hidden" name="profile_id" value="<?= $row['profile_id'] ?>">
<input type="submit" value="Delete" name="delete" onclick="return myDelete();">
<input type="submit" name="cancel" value="Cancel">
<!-- <a href="index.php">Cancel</a> -->
</form>
<script type="text/javascript">
    function myDelete(){
        var x = true;
        if (confirm("Are you sure to delete the item?") == true) {
            x = true;
        } else {
            x = false;
        }
        return x;
    }
</script>
