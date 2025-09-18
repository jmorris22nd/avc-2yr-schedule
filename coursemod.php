<?php 
if (!$_SESSION)
{
	session_start();
}

include('../includes/header.shtml');

//someone hasn't logged in yet- startover- but make sure not from post
if (@$_SESSION["service"] != "2yearsched")
{		
	$URL = "/?logout&error=true";
	header('Location: ' . $URL);
}

	//error_reporting(0);
	require('../../conf/2yrschedconfig.php');



  //page variables and patterns
    $isDigit = "/[0-9]\\d*(\\.\\d+)?$/";

    $courseeditMSG = "";
    $courseinsertMSG = "";
    $courseofferingMSG = "";

    $numsectionsERR= false;
    $timesERR= false;
    $locationsERR= false;
    $yearERR = false;
    $courseselectERR = false;

    $subjectERR = false;
    $numberERR = false;
    $titleERR = false;

    $editsubjectERR = false;
    $editnumberERR = false;
    $edittitleERR = false;

	$result = $mysqli->query("SELECT * FROM 2yearschedule.course order by subject asc, (0 + number) asc, number asc;");

	$selectmenu = "<select id='courseselect' name='courseselect'><option value='error' " .
		(@$_POST["courseselect"] == "error" ? "selected='selected'" : "") .
		">Select a Course</option>";

    while ($row = $result->fetch_assoc()) {
      $selectmenu .= "<option value='" . $row["courseID"] . "'".
         (@$_SESSION["courseID"] == $row["courseID"] ? "selected='selected'" : "") .
            ">" . $row["subject"] . " " . $row["number"] ."</option>";
    }
    $result->free();
    $selectmenu .= "</select>";
	
     //process course edit selection- shows courseID form
     if (count($_POST) && $_POST["courseselect"] != "error")
     {
          $result = $mysqli->query("select * from course where courseID = '" . $_POST["courseselect"] . "'");
          $row = $result->fetch_assoc();

		  $_SESSION["subject"] = $row["subject"];
          $_SESSION["number"] = $row["number"];
          $_SESSION["title"] = $row["title"];
          $_SESSION["AVCGE"] = $row["AVCGE"];
          $_SESSION["CSUGE"] = $row["CSUGE"];
          $_SESSION["IGETC"] = $row["IGETC"];
          $_SESSION["AI"] = $row["AI"];
          $_SESSION["courseID"] = $_POST["courseselect"];

          $result->free();

      }
	

	//process an edited course
	if (count($_POST) && !empty($_POST["editsubject"]) && !empty($_POST["editnumber"]) && !empty($_POST["edittitle"]))
	{
		$_SESSION["subject"] = "";
    	$_SESSION["number"] = "";
		$_SESSION["title"] = "";
		$_SESSION["AVCGE"] = "";
		$_SESSION["CSUGE"] = "";
		$_SESSION["IGETC"] = "";
		$_SESSION["AI"] = "";
		$_SESSION["courseID"] = "";
			

	    $editsubject = $mysqli->real_escape_string($_POST["editsubject"]);
	    $editnumber= trim($_POST["editnumber"]);
	    $edittitle= $mysqli->real_escape_string($_POST["edittitle"]);
						
        $result = $mysqli->query("update course set subject = '" . $editsubject .
			"', number = '" . $editnumber . "', ".
			"title = '" . $edittitle . "', ".
            "AVCGE = '" . ($_POST["editavcge"] ? $_POST["editavcge"] . "', " : "', ").
            "CSUGE = '" . ($_POST["editcsuge"] ? $_POST["editcsuge"] . "', " : "', ").
            "IGETC = '" . ($_POST["editigetc"] ? $_POST["editigetc"] . "', " : "', ").
            "AI = " . ($_POST["editai"] == "yes" ? "1 " : "0 ").
            ", user = '" . $_SESSION["email"] . "', activityDate = '" . date("Y-m-d H:i:s") 
			. "' where courseID = " . $_POST["editcourseID"] ); 

        if ($mysqli->affected_rows > 0)
        {
        	$courseeditMSG = "<p><strong>Your course was updated.</strong></p>";
        }
		// unset($_POST);
        else
        {
           if (!$_POST["editsubject"]) $editsubjectERR = true;
           if (!$_POST["editnumber"]) $editnumberERR = true;
           if (!$_POST["edittitle"]) $edittitleERR = true;
        }
      }


      //process new course entry
      if (count($_POST) && $_POST["newcoursesubmit"] && !empty($_POST["subject"]) && !empty($_POST["number"]) && !empty($_POST["title"]))
      {
	      $subject = $mysqli->real_escape_string(strtoupper($_POST["subject"]));
	      $number= trim($_POST["number"]);
	      $title= $mysqli->real_escape_string($_POST["title"]);	
		  
             $result = $mysqli->query("insert into course values (NULL, '" . $subject . "', '" . $number . "', '" . $title . "', ".
		      ($_POST["avcge"] ? "'" . $_POST["avcge"] . "', " : "'', ").
		      ($_POST["csuge"] ? "'" . $_POST["csuge"] . "', " : "'', ").
		      ($_POST["igetc"] ? "'" . $_POST["igetc"] . "', " : "'', ").
              ($_POST["ai"] == "yes" ? "1" : "0").
			   ", '" . $_SESSION["email"] . "', '" . date("Y-m-d H:i:s") . "' )"); 

           if ($mysqli->affected_rows > 0)
           {
              $courseinsertMSG = "<p><strong>Your course was added to the database.</strong></p>";
           }

           unset($_POST);
	 }
  	 if ($_POST["newcoursesubmit"])
	 {
	 	if (!$_POST["subject"]) $subjectERR = true;
		if (!$_POST["number"]) $numberERR = true;
		if (!$_POST["title"]) $titleERR = true;
	 }

?>



<script language="JavaScript" type="text/javascript">
<!--

jQuery(document).ready(function(){
	
	if (jQuery('#courseselect').val() == "error")
	{
		jQuery('#editcourse').addClass("hidden");
	}

	jQuery('form#courseselection').submit(function(e){
		e.preventDefault();
		
		jQuery.ajax({
			type: 'post',
			url: 'coursemod.php',
			data: jQuery('form#courseselection').serialize(),
			cache: false,
			success:  function() {
            	window.location.reload();
				//jQuery('#coursenewmsg').html("<p>The course has been added</p>");
				//jQuery('#editcourse').removeClass("hidden");
       		},		
     	});
	});
	
	jQuery('form#newcourse').submit(function(e){
		e.preventDefault();
		
		if (validatecourse())
		{
			jQuery.ajax({
				type: 'post',
				url: 'coursemod.php',
				data: jQuery('form#newcourse').serialize(),
				cache: false,
				success:  function() {
            		//window.location.reload();
					jQuery('#coursenewmsg').html("<p>The course has been added</p>");
       			},
				error: function() {
					jQuery('#coursenewmsg').html("<p>There was an error with the submitted information</p>");
       			}		
     		});
		}
		else
		{
			jQuery('#coursenewmsg').html("<p>There was an error with the information submitted.</p>");	
		}
	});
	
	jQuery('form#editcourse').submit(function(e){
		e.preventDefault();
		
		if (validateeditcourse())
		{
			jQuery.ajax({
				type: 'post',
				url: 'coursemod.php',
				data: jQuery('form#editcourse').serialize(),
				cache: false,
				success: function() {
					jQuery('#editcourse').addClass("hidden");
					jQuery('#courseeditmsg').html("<p>The course information has been updated.</p>");
       			},
				error: function() {
					jQuery('#courseeditmsg').html("<p>There was an error with the information submitted.</p>");
       			}	
     		});
		}
		else
		{
			jQuery('#courseeditmsg').html("<p>There was an error with the information submitted.</p>");	
		}
	});	
});

function validatecourse()
{
   errMsg = "There was a problem with your submission:\n";

   formErrs = "";
	  
   if (jQuery('#subject').val() == "")
      formErrs += "Subject:\n";

   if (jQuery('#number').val() == "")
      formErrs += "Number:\n";

   if (jQuery('#title').val() == "")
      formErrs += "Title:\n";

		  
   if (formErrs.length > 0)
   {
     //alert(errMsg + formErrs);
     return false;
   }
   else
   {
      return true;
   }
}

function validateeditcourse()
{
   errMsg = "There was a problem with your submission:\n";

   formErrs = "";
	  
   if (jQuery('#editsubject').val() == "")
      formErrs += "Subject:\n";

   if (jQuery('#editnumber').val() == "")
      formErrs += "Number:\n";

   if (jQuery('#edittitle').val() == "")
      formErrs += "Title:\n";

		  
   if (formErrs.length > 0)
   {
     //alert(errMsg + formErrs);
     return false;
   }
   else
   {
      return true;
   }
}

</script>

<div id="breadcrumbs">
<a href="dashboard.php">2 Year Schedule System</a> > Change Courses </div>

<div id="rightnav">

<?php include("nav.php"); ?>

</div>
<br>

<div id="maincontent">

<h1>2 Year Schedule Entry System: Change Courses</h1>


<h2 class="topborderpurple" style="margin-left: 0px;">Edit Course</h2>

<div id="courseeditmsg"></div>

<form id="courseselection" name="courseselection">
<?php

    echo $selectmenu;

?>

<input type="submit" name="getcourse" value="Get Course" />
</form>

<form id="editcourse" name="editcourse">

          <p>
            <label for="editsubject">*Subject:</label>
                <input type="text" name="editsubject" id="editsubject" maxlength="10" style="width: 50px;" onBlur="checkfield('editsubjectalert', this.value, 'notnull', 'Please enter the course subject.')" value="<?php if ($_SESSION["subject"] != "") echo $_SESSION["subject"]; ?>"/>
            
            <label for="editnumber">*Number:</label>
                <input type="text" name="editnumber" id="editnumber" maxlength="5" style="width: 50px;" onBlur="checkfield('editnumberalert', this.value, 'notnull', 'Please enter the course number.')" value="<?php if ($_SESSION["number"] != "") echo $_SESSION["number"]; ?>"/>

            <label for="edittitle">*Title:</label>
                <input type="text" name="edittitle" id="edittitle" maxlength="45" style="width: 300px;" onBlur="checkfield('edittitlealert', this.value, 'notnull', 'Please enter the course title.')" value="<?php if ($_SESSION["title"] != "") echo $_SESSION["title"]; ?>"/>

             <br /><span id="editsubjectalert" name="editsubjectalert" class="warning"><?php if (@$editsubjectERR) echo "***"; ?></span>
                <span id="editnumberalert" name="editnumberalert" class="warning"><?php if (@$editnumberERR) echo "***"; ?></span>
                <span id="edittitlealert" name="edittitlealert" class="warning"><?php if (@$edittitleERR) echo "***"; ?></span>
  </p>

<h3>General Education Information</h3>
          <p class="purpleborder">
            <label for="editavcge">AVCGE:</label>
                <input type="text" name="editavcge" id="editavcge" maxlength="10" style="width: 50px;" value="<?php if ($_SESSION["AVCGE"] != "") echo $_SESSION["AVCGE"]; ?>"/>

            <label for="editcsuge">CSUGE:</label>
                <input type="text" name="editcsuge" id="editcsuge" maxlength="10" style="width: 50px;" value="<?php if ($_SESSION["CSUGE"] != "") echo $_SESSION["CSUGE"]; ?>"/>

            <label for="editigetc">IGETC:</label>
                <input type="text" name="editigetc" id="editigetc" maxlength="10" style="width: 50px;" value="<?php if ($_SESSION["IGETC"] != "") echo $_SESSION["IGETC"]; ?>"/>


American Institutions: Yes <input type="radio" name="editai" id="editai" value="yes" <?php if ($_SESSION["AI"] == 1) echo "checked='checked'"; ?> />
                No <input type="radio" name="editai" value="no" <?php if ($_SESSION["AI"] == 0) echo "checked='checked'"; ?> />

</p>
<input type="hidden" name="editcourseID" id="editcourseID" value="<?php if ($_SESSION["courseID"] != "") echo $_SESSION["courseID"];  ?>" />
<input type="submit" name="editcoursesubmit" value="Submit Course Changes" />

</form>


<h2 class="topborderpurple" style="margin-left: 0px;">Add New Course</h2>

<h3>Course Information</h3>

<div id="coursenewmsg"></div>
<form id="newcourse" name="newcourse">
          <p>  <label for="subject">*Subject:</label>
                <input type="text" name="subject" id="subject" maxlength="10" style="width: 50px;" onBlur="checkfield('subjectalert', this.value, 'notnull', 'Please enter the course subject.')" value="<?php if (@$_POST['subject']) echo $_POST['subject']; ?>"/>
            
            <label for="number">*Number:</label>
                <input type="text" name="number" id="number" maxlength="5" style="width: 50px;" onBlur="checkfield('numberalert', this.value, 'notnull', 'Please enter the course number.')" value="<?php if (@$_POST['number']) echo $_POST['number']; ?>"/>

            <label for="title">*Title:</label>
                <input type="text" name="title" id="title" maxlength="45" style="width: 300px;" onBlur="checkfield('titlealert', this.value, 'notnull', 'Please enter the course title.')" value="<?php if (@$_POST['title']) echo $_POST['title']; ?>"/>

             <br /><span id="subjectalert" name="subjectalert" class="warning"><?php if (@$subjectERR) echo "***"; ?></span>
                <span id="numberalert" name="numberalert" class="warning"><?php if (@$numberERR) echo "***"; ?></span>
                <span id="titlealert" name="titlealert" class="warning"><?php if (@$titleERR) echo "***"; ?></span>
            </p>

<h3>General Education Information</h3>
          <p class="purpleborder">
            <label for="avcge">AVCGE:</label>
                <input type="text" name="avcge" id="avcge" maxlength="10" style="width: 50px;" value="<?php if (@$_POST['avcge']) echo $_POST['avcge']; ?>"/>

            <label for="csuge">CSUGE:</label>
                <input type="text" name="csuge" id="csuge" maxlength="10" style="width: 50px;" value="<?php if (@$_POST['csuge']) echo $_POST['csuge']; ?>"/>

            <label for="igetc">IGETC:</label>
                <input type="text" name="igetc" id="igetc" maxlength="10" style="width: 50px;" value="<?php if (@$_POST['igetc']) echo $_POST['igetc']; ?>"/>

American Institutions: Yes <input type="radio" name="ai" id="ai" value="yes" <?php if (@$_POST['ai']== 'yes') echo "checked='checked'"; ?> />
                No <input type="radio" name="ai" value="no" <?php if (@$_POST['ai']== 'no') echo "checked='checked'"; ?> />

</p>

<input type="hidden" id="newcoursesubmit" name="newcoursesubmit" value="true" />

<input type="submit" name="submitcourse" value="Submit Course" />

</form>

</div>

<?php

	include('../includes/footer.shtml');
?>