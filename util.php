<?php
    function flashMessages(){
        if(isset($_SESSION['error']) ){
            echo('<p style="color: red;">'.htmlentities($_SESSION['error'])."</p>\n");
            unset($_SESSION['error']);
        }
        if ( isset($_SESSION['success']) ) {
            echo('<p style="color: green;">'.htmlentities($_SESSION['success'])."</p>\n");
            unset($_SESSION['success']);
        }
    }

    function validateProfile(){
        if ( strlen($_POST['first_name'])==0 || strlen($_POST['last_name'])==0 || strlen($_POST['email'])==0 || strlen($_POST['headline'])==0 || strlen($_POST['summary'])==0 ){
            return "All fields are required";
        }
        if (strpos($_POST['email'], '@') === false){
            return "Email address must contain @";
        }
        return true;
    }

    // look through the post data and return true or false msg
    function validatePos(){
        for ( $i=1; $i<9; $i++ ) {
            if ( !isset($_POST['year'.$i]) ) continue;
            if ( !isset($_POST['desc'.$i]) ) continue;
            $year = $_POST['year'.$i];
            $desc = $_POST['desc'.$i];
            if ( strlen($year) == 0 || strlen($desc) == 0 ) {
                return "All fields are required";
            }
            if ( !is_numeric($year) ) {
                return "Position year must be numeric";
            }
        }
    }

    // look through the post data and return true or error msg
    function validateEdu(){
        for ( $i=1; $i<9; $i++ ) {
            if ( !isset($_POST['edu_year'.$i]) ) continue;
            if ( !isset($_POST['edu_school'.$i]) ) continue;
            $year = $_POST['edu_year'.$i];
            $school = $_POST['edu_school'.$i];
            if ( strlen($year) == 0 || strlen($school) == 0 ) {
                return "All fields are required";
            }
            if ( !is_numeric($year) ) {
                return "Education year must be numeric";
            }
        }
    }

    function loadPos( $pdo, $profile_id ) {
        $stmt = $pdo->prepare('SELECT * FROM Position WHERE profile_id = :prof ORDER BY rank');
        $stmt->execute(array(':prof' => $profile_id));
        $positions = array();
        while($row = $stmt->fetch(PDO::FETCH_ASSOC)){
            $positions[] = $row;
        }
        return $positions;
    }

    function loadEdu( $pdo, $profile_id ) {
        $stmt = $pdo->prepare('SELECT year, name FROM Education JOIN Institution ON Education.institution_id = Institution.institution_id WHERE profile_id = :prof ORDER BY rank');
        $stmt->execute(array(':prof' => $profile_id));
        $educations = array();
        while($row = $stmt->fetch(PDO::FETCH_ASSOC)){
            $educations[] = $row;
        }
        return $educations;
    }
