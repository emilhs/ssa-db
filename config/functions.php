<?php
function get_agecat($dob, $season) {
    if (is_null($dob)){
        return(NULL);
    }
    else {
    // Convert the date of birth to a DateTime object
    $dob = strtotime($dob);

    // Get the current year
    $currentYear = $season-1;
    // Determine the most recent June 30
    $june30 = strtotime("$currentYear-06-30");
    $dobDate = new DateTime(date("Y-m-d", $dob));
    $june30Date = new DateTime(date("Y-m-d", $june30));

    // Calculate the age on the most recent June 30
    $age = $june30Date->diff($dobDate)->y;

    if ($age <= 5){
        $age = "Active Start";
    }
    // Determine the age category
    return($age);
    }
}

?>