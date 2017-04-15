<?php
require_once "pdo.php";
require_once "util.php";
// Demand a GET parameter

session_start();

if(isset($_POST["cancel"])){
    header('Location: index.php');
    return;
}

if(!isset($_SESSION["name"]) || strlen($_SESSION["name"]) < 1 ){
    die('ACCESS DENIED');
}


if ( isset($_POST['first_name']) && isset($_POST['last_name']) && isset($_POST['email']) && isset($_POST['headline']) && isset($_POST['summary'])) {
    $msg = validateProfile();
    if ( is_string($msg) ){
        $_SESSION['error'] = $msg;
        header("Location: add.php");
        return;
    }

    $msg = validatePos();
    if ( is_string($msg) ){
        $_SESSION['error'] = $msg;
        header("Location: add.php");
        return;
    }

    $msg = validateEdu();
    if ( is_string($msg) ){
        $_SESSION['error'] = $msg;
        header("Location: add.php");
        return;
    }

    // data is valid, time to insert
    $stmt = $pdo->prepare("INSERT INTO Profile (first_name, last_name, email, headline, summary, user_id) VALUES (:firstname, :lastname, :email, :headline, :summary, :userid)");
    $stmt->execute(array(
        ':firstname' => htmlentities($_POST["first_name"]),
        ':lastname' => htmlentities($_POST["last_name"]),
        ':email' => htmlentities($_POST["email"]),
        ':headline' => htmlentities($_POST["headline"]),
        ':summary' => htmlentities($_POST["summary"]),
        ':userid' => $_SESSION['user_id']
    ));

    $profile_id = $pdo->lastInsertId();

    $rank = 1;
    for ( $i=1; $i<9; $i++ ) {
        if ( !isset($_POST['year'.$i]) ) continue;
        if ( !isset($_POST['desc'.$i]) ) continue;
        $year = $_POST['year'.$i];
        $desc = $_POST['desc'.$i];

        $stmt = $pdo->prepare('INSERT INTO Position (profile_id, rank, year, description) VALUES (:pid, :rank, :year, :desc)');
        $stmt->execute(array(
            ':pid' => $profile_id,
            ':rank' => $rank,
            ':year' => $year,
            ':desc' => $desc
        ));
        $rank ++;
    }

    $rank = 1;
    for($i=1; $i<=9; $i++) {
        if ( !isset($_POST['edu_year'.$i]) ) continue;
        if ( !isset($_POST['edu_school'.$i]) ) continue;
        $year = $_POST['edu_year'.$i];
        $school = $_POST['edu_school'.$i];

        // lookup the school if it is there
        $institution_id = false;
        $stmt = $pdo->prepare('SELECT institution_id FROM Institution WHERE name = :name');
        $stmt->execute(array(':name' => $school));
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if($row !== false) $institution_id = $row['institution_id'];

        // if there are no institution, insert it
        if($institution_id === false){
            $stmt = $pdo->prepare('INSERT INTO Institution (name) VALUES (:name)');
            $stmt->execute(array(
                ':name' => $school
            ));
            $institution_id = $pdo->lastInsertId();
        }


        $stmt = $pdo->prepare('INSERT INTO Education (profile_id, rank, year, institution_id) VALUES ( :pid, :rank, :year, :iid)');
        $stmt->execute(array(
                    ':pid' => $profile_id,
                    ':rank' => $rank,
                    ':year' => $year,
                    ':iid' => $institution_id)
        );
        $rank++;

    }

    $_SESSION["success"] = "Profile added";
    header('Location:index.php');
    return;
}

?>
<!DOCTYPE html>
<html>
<head>
<title>Yu Liu's Profile Add</title>
<?php require_once "head.php"; ?>
</head>
<body>
<div class="container">

<h1>Adding Profile for <?=htmlentities($_SESSION['name']); ?></h1>

<?php flashMessages();   ?>

<form method="post">
<p>First Name:
<input type="text" name="first_name" size="40"></p>
<p>Last Name:
<input type="text" name="last_name" size="40"></p>
<p>Email:
<input type="text" name="email"></p>
<p>Headline:
<input type="text" name="headline"></p>
<p>Summary:<br>
<textarea name="summary" rows="8" cols="80"></textarea></p>
<p>Education: <input type="button" id="addEdu" value="+">
<div id="edu_fields"></div></p>
<br>
<p>Position: <input type="button" id="addPos" value="+">
<div id="position_fields"></div></p>
<br><br>
<input type="submit" name="add" value="Add">
<input type="submit" name="cancel" value="Cancel">
</form>

</div>

<!-- <script src="http://www.wa4e.com/solutions/res-education/js/jquery-1.10.2.js"></script> -->
<!-- <script src="http://cdn.static.runoob.com/libs/jquery/1.10.2/jquery.min.js"></script> -->
<!-- <script src="http://www.wa4e.com/solutions/res-education/js/jquery-ui-1.11.4.js"></script> -->
<script type="text/javascript">
    var countPos = 0;
    var countEdu = 0;

    // http://stackoverflow.com/questions/17650776/add-remove-html-inside-div-using-javascript
    $(document).ready(function(){
        window.console && console.log('Document ready called');
        $('#addPos').click(function(event){
            // http://api.jquery.com/event.preventdefault/
            event.preventDefault();
            if ( countPos >= 9 ) {
                alert("Maximum of nine position entries exceeded");
                return;
            }
            countPos++;
            window.console && console.log("Adding position "+countPos);
            $('#position_fields').append(
                '<div id="position'+countPos+'"> \
                <p>Year: <input type="text" name="year'+countPos+'" value="" /> \
                <input type="button" value="-" \
                    onclick="$(\'#position'+countPos+'\').remove();return false;"></p> \
                <textarea name="desc'+countPos+'" rows="8" cols="80"></textarea>\
                </div>');
        });

        $('#addEdu').click(function(event){
            // http://api.jquery.com/event.preventdefault/
            event.preventDefault();
            if ( countPos >= 9 ) {
                alert("Maximum of nine education entries exceeded");
                return;
            }
            countEdu++;
            window.console && console.log("Adding position "+countPos);
            $('#edu_fields').append(
                '<div id="edu'+countEdu+'"> \
                <p>Year: <input type="text" name="edu_year'+countEdu+'" value="" /> \
                <input type="button" value="-" \
                    onclick="$(\'#edu'+countEdu+'\').remove();return false;"></p> \
                <p>School: <input class="school" type="text" name="edu_school'+countEdu+'" size=60 /></p>\
                </div>');
            $('.school').autocomplete({source: "school.php"});
        });

        $('.school').autocomplete({source: "school.php"});
    });

</script>



</body>
</html>
