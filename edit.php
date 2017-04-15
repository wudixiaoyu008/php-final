<?php
require_once "pdo.php";
require_once "util.php";
session_start();

//////////////////////////////////////  row1['rank'], countPos   js/php

if(isset($_POST['cancel'])){
    header('Location: index.php');
    return;
}
if(!isset($_SESSION['user_id'])){
    die("ACCESS DENIED");
    return;
}
// make sure the request parameter is present
if(!isset($_REQUEST['profile_id'])){
    $_SESSION['error'] = "Missing profile_id";
    header('Location: index.php');
    return;
}

//load up the profile in question
$stmt = $pdo->prepare('SELECT * FROM Profile WHERE profile_id = :prof AND user_id = :uid');
$stmt->execute(array(':prof' => $_REQUEST['profile_id'],':uid' => $_SESSION['user_id']));
$profile = $stmt->fetch(PDO::FETCH_ASSOC);
if($profile === false){
    $_SESSION['error'] = "Could not load profile";
    header('Location: index.php');
    return;
}

// handle the incoming data
if ( isset($_POST['first_name']) && isset($_POST['last_name'])
     && isset($_POST['email']) && isset($_POST['headline']) && isset($_POST['summary']) && isset($_POST['save']) ) {

    $msg = validateProfile();
    if ( is_string($msg) ){
        $_SESSION['error'] = $msg;
        header("Location: edit.php?profile_id=".$_REQUEST['profile_id']);
        return;
    }

    // validate the position entries if present
    $msg = validatePos();
    if ( is_string($msg) ){
        $_SESSION['error'] = $msg;
        header("Location: edit.php?profile_id=".$_REQUEST['profile_id']);
        return;
    }

    // validate the education entries if present
    $msg = validateEdu();
    if ( is_string($msg) ){
        $_SESSION['error'] = $msg;
        header("Location: edit.php?profile_id=".$_REQUEST['profile_id']);
        return;
    }

    // Data validation should go here (see add.php)
    $sql = "UPDATE Profile SET first_name = :firstname,
            last_name = :lastname, email = :email, headline = :headline, summary = :summary
            WHERE profile_id = :profile_id AND user_id = :uid";
    $stmt = $pdo->prepare($sql);
    $stmt->execute(array(
        ':firstname' => $_POST['first_name'],
        ':lastname' => $_POST['last_name'],
        ':email' => $_POST['email'],
        ':headline' => $_POST['headline'],
        ':summary' => $_POST['summary'],
        ':uid' => $_SESSION['user_id'],
        ':profile_id' => $_REQUEST['profile_id']));

    // Clear out the old position entries
    $stmt = $pdo->prepare('DELETE FROM Position WHERE profile_id=:pid');
    $stmt->execute(array( ':pid' => $_REQUEST['profile_id']));
    // just use REQUEST, since it merges GET and POST, etc

    // Insert the position entries
    $rank = 1;
    for($i=1; $i<=9; $i++) {
        if ( !isset($_POST['year'.$i]) ) continue;
        if ( !isset($_POST['desc'.$i]) ) continue;
        $year = $_POST['year'.$i];
        $desc = $_POST['desc'.$i];

        $stmt = $pdo->prepare('INSERT INTO Position (profile_id, rank, year, description)
        VALUES ( :pid, :rank, :year, :desc)');
        $stmt->execute(array(
            ':pid' => $_REQUEST['profile_id'],
            ':rank' => $rank,
            ':year' => $year,
            ':desc' => $desc)
        );
        $rank++;
    }

    // Clear out the old Education entries
    $stmt = $pdo->prepare('DELETE FROM Education WHERE profile_id=:pid');
    $stmt->execute(array( ':pid' => $_REQUEST['profile_id']));
    // just use REQUEST, since it merges GET and POST, etc

    // Insert the Education entries
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

        $stmt = $pdo->prepare('INSERT INTO Education (profile_id, rank, year, institution_id)
        VALUES ( :pid, :rank, :year, :iid)');
        $stmt->execute(array(
            ':pid' => $_REQUEST['profile_id'],
            ':rank' => $rank,
            ':year' => $year,
            ':iid' => $institution_id)
        );
        $rank++;

    }

    $_SESSION['success'] = 'Profile updated';
    header( 'Location: index.php' ) ;

    return;
}

// load up the position and education rows
$positions = loadPos($pdo, $_REQUEST['profile_id']);
$schools = loadEdu($pdo, $_REQUEST['profile_id']);

?>

<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<title>Editing Profile</title>
<?php require_once "head.php"; ?>
</head>
<body>

    <div class="container">
        <h1>Editing Profile for <?=htmlentities($_SESSION['name']); ?></h1>
        <?php
            flashMessages();
        ?>
        <form method="post" action="edit.php">
        <input type="hidden" name="profile_id" value="<?=htmlentities($_REQUEST['profile_id']); ?>">
        <p>First Name:
        <input type="text" name="first_name" size="60" value="<?=htmlentities($profile['first_name']); ?>"></p>
        <p>Last Name:
        <input type="text" name="last_name" size="60" value="<?=htmlentities($profile['last_name']); ?>"></p>
        <p>Email:
        <input type="text" name="email" size="30" value="<?=htmlentities($profile['email']); ?>"></p>
        <p>Headline:
        <input type="text" name="headline" size="80" value="<?=htmlentities($profile['headline']); ?>"></p>
        <p>Summary:<br>
        <textarea name="summary" rows="8" cols="80"><?=htmlentities($profile['summary']); ?></textarea><br>

        <?php

            $countEdu = 0;
            echo('<p>Education: <input type="button" id="addEdu" value="+">'."\n");
            echo('<div id="edu_fields">'."\n");
            if(count($schools)>0){
                    foreach($schools as $school){
                        $countEdu++;
                        echo('<div id="edu'.$countEdu.'">');
                        echo('<p>Year: <input type="text" name="edu_year'.$countEdu.'" value="'.$school['year'].'" /><input type="button" value="-" onclick="$(\'#edu'.$countEdu.'\').remove(); return false;"/></p><p>School: <input type="text" size="80" name="edu_school'.$countEdu.'" class="school" value="'.htmlentities($school['name']).'" />');
                        echo("\n</div>\n");
                    }
            }
            echo("</div></p>\n");

            $countPos = 0;
            echo('<p>Position: <input type="button" id="addPos" value="+">'."\n");
            echo('<div id="position_fields">'."\n");
            if(count($positions)>0){
                    foreach($positions as $position){
                        $countPos++;
                        echo('<div class="position" id="position'.$countPos.'">');
                        echo('<p>Year: <input type="text" name="year'.$countPos.'" value="'.$position['year'].'" /><input type="button" value="-" onclick="$(\'#position'.$countPos.'\').remove(); return false"/></p>');
                        echo('<textarea name="desc'.$countPos.'" rows="8" cols="80">'."\n");
                        echo(htmlentities($position['description'])."\n");
                        echo("\n</textarea>\n</div>\n");
                    }
            }

            echo("</div></p>\n");
        ?>

        <p>
        <input type="submit" name="save" value="Save">
        <input type="submit" name="cancel" value="Cancel">
        </p>
        </form>

        <script type="text/javascript">
            var countPos = <?=$countPos ?>;
            var countEdu = <?=$countEdu ?>

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
                            onclick="$(\'#position'+countPos+'\').remove(); return false;"></p> \
                        <textarea name="desc'+countPos+'" rows="8" cols="80"></textarea>\
                        </div>');
                });

                $('#addEdu').click(function(event){
                    // http://api.jquery.com/event.preventdefault/
                    event.preventDefault();
                    if ( countEdu >= 9 ) {
                        alert("Maximum of nine education entries exceeded");
                        return;
                    }
                    countEdu++;
                    window.console && console.log("Adding education "+countEdu);

                    // grab some html with hot spots and insert into the dom
                    var source = $("#edu-template").html();
                    $("#edu_fields").append(source.replace(/@COUNT@/g, countEdu));

                    // add the event handler to the new ones
                    $('.school').autocomplete({source: "school.php"});

                    // $('#edu_fields').append(
                    //     '<div id="position'+countPos+'"> \
                    //     <p>Year: <input type="text" name="year'+countPos+'" value="" /> \
                    //     <input type="button" value="-" \
                    //         onclick="$(\'#position'+countPos+'\').remove(); return false;"></p> \
                    //     <textarea name="desc'+countPos+'" rows="8" cols="80"></textarea>\
                    //     </div>');
                });

                $('.school').autocomplete({source: "school.php"});
            });

        </script>

        <script type="text" id="edu-template">
            <div id="edu@COUNT@">
                <p>Year: <input type="text" name="edu_year@COUNT@" value="" />
                <input type="button" value="-" onclick="$('#edu@COUNT@').remove(); return false;"></p><br>
                <p>School: <input type="text" size="80" name="edu_school@COUNT@" class="school" value="" /></p>
            </div>
        </script>

    </div>





    <!-- <script src="http://cdn.static.runoob.com/libs/jquery/1.10.2/jquery.min.js"></script> -->
    <!-- <script src="http://www.wa4e.com/solutions/res-education/js/jquery-ui-1.11.4.js"></script> -->


</body>
</html>
