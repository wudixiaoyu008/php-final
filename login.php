<?php // Do not put any HTML above this line
require_once "pdo.php";
session_start();

if ( isset($_POST['cancel'] ) ) {
    // Redirect the browser to game.php
    header("Location: index.php");
    return;
}

$salt = 'XyZzy12*_';
// $stored_hash = '1a52e17fa899cf40fb04cfc42e6352f1';  // Pw is meow123

// $failure = false;  // If we have no POST data

// Check to see if we have some POST data, if we do process it
if ( isset($_POST['email']) && isset($_POST['pass']) ) {
    if ( strlen($_POST['email']) < 1 || strlen($_POST['pass']) < 1 ) {
        $_SESSION["error"] = "Email and password are required";

        header("Location: login.php");    // to prevent refresh again after post, we cannot generate html after post, Must redirect somewhere - even to  the same script - forcing the browser to make a GET after the POST even if header to it self
        error_log("Login fail ".$_POST['email']." $check");
        return;
    }
    else {
        $pos = strpos($_POST['email'], '@');
        if($pos === false){     // not <0, while ===false
            $_SESSION["error"] = "Email must have an at-sign (@)";

            header("Location: login.php");
            error_log("Login fail ".$_POST['email']." $check");
            return;
        }
        else{
            $check = hash('md5', $salt.$_POST['pass']);
            $stmt = $pdo->prepare('SELECT user_id, name FROM users
            WHERE email = :em AND password = :pw');
            $stmt->execute(array( ':em' => $_POST['email'], ':pw' => $check));
            $row = $stmt->fetch(PDO::FETCH_ASSOC);

            if ( $row !== false ) {
                // $_SESSION["name"] = $_POST["email"];
                $_SESSION['name'] = $row['name'];
                $_SESSION['user_id'] = $row['user_id'];
                // $_SESSION['success']
                header("Location: index.php");
                // error_log("Login success ".$_POST['email']);
                return;
            } else {
                $_SESSION["error"] = "Incorrect password";

                header("Location: login.php");
                error_log("Login fail ".$_POST['email']." $check");
                return;
            }
        }
    }
}

// Fall through into the View
?>
<!DOCTYPE html>
<html>
<head>
<?php require_once "bootstrap.php"; ?>
<title>Yu Liu's Login Page</title>
</head>
<body>
<div class="container">
<h1>Please Log In</h1>
<?php

if ( isset($_SESSION["error"]) ) {
    echo('<p style="color: red;">'.htmlentities($_SESSION["error"])."</p>\n");
    unset($_SESSION["error"]);
}
?>
<form method="POST">
<label for="nam">User Name</label>
<input type="text" name="email" id="nam"><br/>
<label for="id_1723">Password</label>
<input type="password" name="pass" id="id_1723"><br/>
<input type="submit" onclick="return doValidate();" value="Log In">
<input type="submit" name="cancel" value="Cancel">
</form>
<p>
For a password hint, view source and find a password hint
in the HTML comments.
<!-- Hint: The password is the four character sound a cat
makes (all lower case) followed by 123. -->
</p>
</div>

<script type="text/javascript">
    function doValidate(){
        console.log('Validating...');
        try {
            pw = document.getElementById('id_1723').value;
            em = document.getElementById('nam').value;
            console.log("Validating pw="+pw);
            console.log("Validating em="+em);
            if (pw == null || pw == "" || em == null || em == "") {
                alert("Both fields must be filled out");
                return false;
            }
            return true;
        } catch(e) {
            return false;
        }
        return false;
    }
</script>


</body>
