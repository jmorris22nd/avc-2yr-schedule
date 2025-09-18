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

	?>

<style type="text/css">\r\nlabel {
   display: inline;
}

.topborderpurple {
                    border-top: 2px solid #302035;
					margin-left: 0px;
					padding-top: 15px;
                 }

</style>

<div id="breadcrumbs">
	2 Year Schedule System
</div>

<div id="rightnav">

<?php include("nav.php"); ?>

</div>
<br>

<div id="maincontent">

<h1>2 Year Schedule Entry System</h1>

<?php

	$courseinsertMSG = "";

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

      $subject = "";
      $number = "";
      $title = "";
      $AVCGE = "";
      $CSUGE = "";
      $IGETC = "";
      $courseID = "";

?>


<script language="JavaScript" type="text/javascript">
<!--

var counter = 1;

jQuery(document).ready(function(){

	jQuery('form#addcourseoffering').submit(function(e){
		e.preventDefault();

		var numerrors = checkeverything(jQuery('#numofferings').val());
		var err = false;

		if (numerrors == 0)
		{
			err = true;
		}

		if (numerrors > 0)
		{
			err = confirm("You have " + numerrors + " section(s) with errors. Do you want to ignore and save completed or cancel and fix them?");
		}

		if (err)
		{
			jQuery.ajax({
				type: 'post',
				//url: '/academics/2yearschedulesystem/dashboard',
				url: '../scripts/2yrsys.php',
				data: jQuery('form#addcourseoffering').serialize(),
				cache: false,
				success:  function(data) {

					if (data == "ERROR")
					{
						jQuery('#sysmessage').html("<p>There was an error with the submission. Please try again.</p>");
					}
					else
					{
						//reset form and data
            			jQuery('#courseofferings').html("");
						jQuery('#numofferings').val();
						counter = 1;

						//echo php message
						jQuery('#sysmessage').html(data);
					}
       			}
     		});
		}
	});


	jQuery('#addoffering').click(function(e){

		jQuery('#courseofferings').append(
			"<div style='margin-top: 5px; padding: 5px; background-color: #ccc;' id='offering_" + counter + "'><p><select id='courseselect_" + counter + "' name='courseselect_" + counter + "' onchange='checkoffering(this)'><?php
   $result = $mysqli->query("SELECT * FROM 2yearschedule.course order by subject asc, (0 + number) asc, number asc;");

   $selectmenu = "<option value='error' selected='selected'>Select a Course</option>";

    while ($row = $result->fetch_assoc()) {
      $selectmenu .= "<option value='" . $row["courseID"] .
         (@$_POST["courseselect"] != "error" &&  @$_POST["courseselect"] == $row["courseID"] ? "selected='selected'" : "") .
            "' >" . $row["subject"] . " " . $row["number"] . "</option>";
    }
    $result->free();

    echo $selectmenu;
    if ($courseselectERR) echo "***";
   ?></select> <select id='yearselect_" + counter + "' name='yearselect_" + counter + "' onchange='checkoffering(this)'><option value='error' selected='selected'>Select a Academic Semester and Year</option><?php

//   $result = $mysqli->query("SELECT * FROM 2yearschedule.academicyr");
			
  //	10/7/19 UPDATED CODE TO REMOVE OLDER DATES AND EXTEND AVAILABLE TERMS. RICH & SCOTT	
  // $result = $mysqli->query("SELECT * FROM 2yearschedule.academicyr WHERE YEAR < (YEAR(CURDATE()) + 4) ORDER BY yearid desc");
				
	$result = $mysqli->query("SELECT * FROM 2yearschedule.academicyr WHERE YEAR BETWEEN YEAR(CURDATE()) AND (YEAR(CURDATE()) + 3) ORDER BY yearid desc");		
			
			
   $yearmenu = "";

    while ($row = $result->fetch_assoc()) {
      $yearmenu .= "<option value='" . $row["yearID"] .
         (@$_POST["yearselect"] != "error" &&  @$_POST["yearselect"] == $row["yearID"] ? "selected='selected'" : "") .
            "' >" . $row["year"] . " " . ucwords($row["semester"]) . "</option>";
    }
    $result->free();

    echo $yearmenu;
    if ($yearERR) echo "***";

?></select> <select id='times_" + counter + "' name='times_" + counter + "' onchange='checkoffering(this)'><option value='error' selected='selected'>Select a Time for the Section(s)</option><option value='M'>Morning</option><option value='A'>Afternoon</option><option value='E'>Evening</option>"+
	"<option value='DE'>Distance Ed</option></select> <select id='locations_" + counter + "' name='locations_" + counter +
	"' onchange='checkoffering(this)'><option value='error' selected='selected'>Select a Location</option>"+
    "<option value='lancaster'>Lancaster</option><option value='palmdale'>Palmdale</option><option value='RHS'>RHS</option><option value='fox field'>Fox Field</option><option value='online'>Online</option></select><br /><label for='numsections_" + counter +
	"' style='display:inline; margin-top: 5px;'>*Number of Sections:</label> "+
	"<input type='text' name='numsections_" + counter + "' id='numsections_" + counter +
	"' maxlength='11' style='width: 50px;' onkeyup='checkoffering(this)' /></p></div>"

		);

		jQuery('#numofferings').val(counter);
		counter++;
	});
});


function checkoffering(formobj)
{
	var id = jQuery(formobj).attr('id').split("_")[1];

	if (jQuery("#courseselect_"+id).val() != "error" && jQuery("#yearselect_"+id).val() != "error" &&
		jQuery("#times_"+id).val() != "error" && jQuery("#locations_"+id).val() != "error" && isNum(jQuery("#numsections_"+id).val()))
	{
		jQuery("#offering_"+id).css("background-color", "#6F9760");
	}
	else
	{
		jQuery("#offering_"+id).css("background-color", "#985253");
	}
}

function checkeverything(count)
{
	var errorcount = 0;
	var objcount = 1;

	for (i = 0; i < count; i++)
	{
		if (jQuery("#courseselect_"+objcount).val() == "error" || jQuery("#yearselect_"+objcount).val() == "error" ||
		jQuery("#times_"+objcount).val() == "error" || jQuery("#locations_"+objcount).val() == "error" ||
		!isNum(jQuery("#numsections_"+objcount).val()))
		{
			errorcount++;
		}
		objcount++;
	}
	return errorcount;
}


function isNum(num)
{
   var i = 0;
   while (i < num.length)
   {
      if(isNaN(parseInt(num.charAt(i))))
      {
		 return false;
      }

      else
      {
         i++;
      }
   }

   if (num.length == 0)
   {
		return false;
   }

   return true;
}

</script>


<h2 class="topborderpurple">Add a Course Offering</h2>
<div id="sysmessage" name="sysmessage"><?php if ($sysmessage) echo $sysmessage; ?></div>

<form id="addcourseoffering" name="addcourseoffering">

	<div id="courseofferings" name="courseofferings">

    </div>

    <div style="margin-top: 20px;">
		<input type="button" id="addoffering" name="addoffering" value="Add Course Offering"/>
        <input type="submit" name="submitcourseoffering" value="Save All to Database" />
    </div>

    <input type="hidden" id="numofferings" name="numofferings" value="" />
</form>
</div>

<div class="clear"></div>



<?php

	include('../includes/footer.shtml');
?>