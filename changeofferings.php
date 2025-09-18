<style type="text/css">
label {
   display: inline;
}
</style>


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
    $courseeditmsg = "";
	$offeringsupdated = false;

	$result = $mysqli->query("SELECT * FROM 2yearschedule.course order by subject asc, (0 + number) asc, number asc;");

	$selectmenu = "<select id='courseselect' name='courseselect'><option value='error' selected='selected'>Select a Course</option>";

    while ($row = $result->fetch_assoc()) {
      $selectmenu .= "<option value='" . $row["courseID"] . "' " . 
         (@$_POST["courseselect"] != "error" &&  @$_POST["courseselect"] == $row["courseID"] ? "selected='selected'" : "") .
            ">" . $row["subject"] . " " . $row["number"] . "</option>";
    }
    $result->free();
    $selectmenu .= "</select>";

	//process offering changes
	if (count($_POST) && $_POST["numrecords"])
	{
		for ($i = 0; $i < $_POST["numrecords"]; $i++)
		{
			//removing offering
			if (isset($_POST["remove_".$i]))	
			{	
				//get courseID before record is removed
				$result = $mysqli->query("select courseID from offering where offeringID = " . $_POST["offeringID_".$i]);
				$record = mysqli_fetch_assoc($result);
				$courseID = $record["courseID"];
				
				$mysqli->query("delete from offering where offeringID = " . $_POST["offeringID_".$i]);
				
				//set date and time to show course offering was removed for course
				$mysqli->query("update course set user = '" . $_SESSION["email"] . "', activityDate = '" . 
					date("Y-m-d H:i:s") . "' where courseID = " . $courseID ); 
				
			}
			//else update record with data
			else
			{
				$mysqli->query("update offering set yearID = " . $_POST["yearID_".$i] .", numsections = ". $_POST["numsections_".$i] .", time = '" . $_POST["time_".$i] . "', location = '" . $_POST["location_".$i] . "', user = '" . $_SESSION["email"] . "', activityDate = '" . date("Y-m-d H:i:s") . 
				"' where offeringID = " . $_POST["offeringID_".$i]);	
			}
		}
		
		$courseeditmsg = "<p>The course records have been updated</p>";
		$offeringsupdated = true;
	}





	//process course edit selection- shows courseID form
	if (count($_POST) && $_POST["courseselect"] != "error")
	{	
		$yearinfo = array(); 
		$result = $mysqli->query("select * from academicyr");
		
		while ($row = $result->fetch_array(MYSQLI_ASSOC))
		{
			array_push($yearinfo, array($row["yearID"], $row["year"] ." ". ucwords($row["semester"])));
		}
	
		$result = $mysqli->query("select * from offering inner join academicyr on offering.yearID = academicyr.yearID".
			" where courseID = " . $_POST["courseselect"] . " order by offering.yearID asc");
		  
		if ($result->num_rows > 0)
		{
		  	$recordcount = 0;
		  
			while ($row = $result->fetch_array(MYSQLI_ASSOC))
			{				
				$yearselect = "<select id='yearID_". $recordcount ."' name='yearID_". $recordcount ."'>";
				
				foreach ($yearinfo as $term)
				{
					$yearselect .= "<option value='" . $term[0] . "' ". 
						($row["yearID"] == $term[0] ? "selected='selected'" : "") .
						">". $term[1] ."</option>";
				}
				
				$yearselect .= "</select>";
				
				$courseeditmsg .= "<p>" . 
					"<label for='numsections_" . $recordcount . "'>Number of Sections: </label>".
					"<input type='text' id='numsections_" . $recordcount . "' name='numsections_" . $recordcount . "' value='" 
					. $row["numsections"] . "'/> ".
					"<select id='time_". $recordcount . "' name='time_" . $recordcount . "'><option value='M' ". 
					($row["time"] == "M" ? "selected='selected'" : "") . ">Morning</option>".
					"<option value='A' ". ($row["time"] == "A" ? "selected='selected'" : "") . ">Afternoon</option>".
					"<option value='E' ". ($row["time"] == "E" ? "selected='selected'" : "") . ">Evening</option>".
					"<option value='DE' ". ($row["time"] == "DE" ? "selected='selected'" : "") . ">Distance Education</option></select>".
					
					" <select id='location_". $recordcount . "' name='location_" . $recordcount . "'><option value='lancaster' ". 
					($row["location"] == "lancaster" ? "selected='selected'" : "") . ">Lancaster</option>".
					"<option value='palmdale' ". ($row["location"] == "palmdale" ? "selected='selected'" : "") . ">Palmdale</option>".
					"<option value='fox field' ". ($row["location"] == "fox field" ? "selected='selected'" : "") . ">Fox Field</option>".
					"<option value='RHS' ". ($row["location"] == "RHS" ? "selected='selected'" : "") . ">RHS</option>".
					"<option value='online' ". ($row["location"] == "online" ? "selected='selected'" : "") . ">Online</option></select> ".
					$yearselect .					
					" Remove? <input type='checkbox' id='remove_". $recordcount ."' name='remove_". $recordcount ."' />".
					"<input type='hidden' name='offeringID_" . $recordcount . "' id='offeringID_" . $recordcount . "' value='".
					$row["offeringID"] . "' />".
					"</p>";
					
				$recordcount++;
			}

			$courseeditmsg .= "<input type='hidden' id='numrecords' name='numrecords' value='{$recordcount}' />".
				"<input type='submit' name='offeringsubmit' id='offeringsubmit' value='Submit Changes' />";
			
			$result->free();
		}
		else
		{
			//show error message only if not returning from updating offerings
			if (!$offeringsupdated)
			{
				$courseeditmsg = "<p>There were no offerings found for this course.</p>";
			}
		}

	}


?>



<script language="JavaScript" type="text/javascript">
<!--

function checkform()
{
	var errorcount = 0;
	
	for (i = 0; i <	jQuery('#numrecords').val(); i++)
	{
		if (!isNum(jQuery("#numsections_"+i).val()))
		{
			errorcount++;
		}
	}
		  
	if (errorcount > 0)
	{
		alert("All values must be completely filled out before submitting");
		return false;
	}
	else
	{
		return true;
	}
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

<div id="breadcrumbs">
<a href="dashboard.php">2 Year Schedule System</a> > Modify/Delete Offerings </div>

<h1>2 Year Schedule Entry System: Modify/Delete Offerings</h1>

<div id="courseeditmsg"></div>

<form id="courseselection" name="courseselection" method="post" action="changeofferings.php">
	<?php

    	echo $selectmenu;

	?>

<input type="submit" name="getcourse" value="Get Course" />
</form>

<form id="offeringmodify" name="offeringmodify" method="post" action="changeofferings.php" onSubmit="return checkform();">

	<?php if ($courseeditmsg != "") echo $courseeditmsg;	?>

</form>

<?php

	include('../includes/footer.shtml');
?>