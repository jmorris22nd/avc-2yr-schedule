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

<script type="text/javascript" src="https://cdn.datatables.net/1.10.13/js/jquery.dataTables.min.js"></script>

<script type="text/javascript">
var linkElement = document.createElement("link");
linkElement.rel = "stylesheet";
linkElement.href = "/css/jquery.dataTables_themeroller.css";

document.head.appendChild(linkElement);
</script>


<div id="breadcrumbs">
<a href="dashboard.php">2 Year Schedule System</a> > Course Report </div>

<h1>2 Year Schedule Entry System: Course Report</h1>

<p>The Two-Year Schedule is a schedule of what classes will be offered during a two-year period so students may plan when to take which of their courses.</p>

<p>Enter a course or location (e.g., ENGL 101, Fox Field, etc.) in the Search box.  To navigate to additional pages, click Next/page numbers at the bottom right.  Use the horizontal scroll bar to see entries out of view.</p>

<p><strong>M</strong> = Morning 7:30am - 1pm, <strong>A</strong> = Afternoon 1pm - 5pm, <strong>E</strong> = Evening 5pm - 10pm, <strong>DE</strong> = Online, <strong>RHS</strong> = Rosamond HS.  Number in parentheses () indicates the number of sections being offered at that time and location.</p>


<table id="2yrschedule" name="2yrschedule" summary="List of all course offerings 2 year window AVC" width="100%">
	<thead>
		<tr><th>Subject</th><th>#</th><th>Course Title</th>

<?php

	$term_start = "";
	$term_end = "";
	$count = 0;

	$month = date("m");  // current month
	$year = date("Y");	 // current year
		//$windowyr = $year;

	// Determine which semester and year to start the sliding window
	// As of February 2021...
	if ($month >= 1 && $month <= 4)
	{
	  $term_start = $year . "30";
	  $term_end = $year + 1 . "70";
	  $count = 6;  // number of terms in this window
	}
	elseif ($month >= 5 & $month <= 11)
	{
	  $term_start = $year . "50";
	  $term_end = $year + 2 . "50";
	  $count = 7;  // number of terms in this window
	}
	elseif ($month = 12)
	{
	  $term_start = $year + 1 . "30";
	  $term_end = $year + 2 . "70";
	  $count = 6;  // number of terms in this window
	}

	//get sliding window
	$result = $connection->query("select * from academicyr where termcode between '$term_start' and '$term_end'");

	while ($row = $result->fetch_array(MYSQLI_ASSOC))
	{
		echo "<th>".ucwords($row["semester"]) . " " . $row["year"] . "</th>";
	}

?>
		<th>AVC GE</th><th>CSU GE</th><th>IGETC</th><th>AI</th></tr>
    </thead>
    <tbody>
<?php

	$result = $mysqli->query("SELECT * FROM 2yearschedule.course order by subject asc, (0 + number) asc, number asc;");

	while ($row = $result->fetch_array(MYSQLI_ASSOC))
	{
		echo "<tr><td>". $row["subject"] . "<td>". $row["number"] ."<td>" . $row["title"] . "</td>";

		$coursedata = $mysqli->query("select count(*) as count from offering " .
                                     " where termcode between '$term_start' and '$term_end'" .
                                     "   and courseID = " . $row["courseID"]);

		//no offerings for a course, print empty row
		if ($coursedata->num_rows == 0)
		{
			for ($i = 0; $i < $count; $i++)
			{
				echo "<td>&nbsp;</td>";
			}
		}
		//else offerings found- place in table by semester
		else
		{
			$semestercount = $floor+1;
			$pointer = 0;
			$rowvals = array();

			while ($innerrow = $coursedata->fetch_array(MYSQLI_ASSOC))
			{
				//advance array and semester account to where we have data
				while ($innerrow["yearID"] != $semestercount)
				{
					$semestercount++;
					$pointer++;
				}
				if (empty($rowvals[$pointer]))
				{
					$rowvals[$pointer] = $innerrow["time"] . "(". $innerrow["numsections"] . ") " .
						ucwords($innerrow["location"]) ."<br />";
				}
				else
				{
					$rowvals[$pointer] = $rowvals[$pointer] . $innerrow["time"] . "(". $innerrow["numsections"] . ") " .
						ucwords($innerrow["location"]) ."<br />";
				}
			}
			for ($i = 0; $i < $count; $i++)
			{
				if (empty($rowvals[$i]))
				{
					echo "<td>&nbsp;</td>";
				}
				else
				{
					echo "<td>" . $rowvals[$i] . "</td>";
				}
			}
		}

		echo "<td>" . $row["AVCGE"] . "</td><td>". $row["CSUGE"] . "</td><td>" . $row["IGETC"] . "</td><td>" . $row["AI"] . "</td></tr>";
	}



?>
    </tbody>

</table>






<script type="text/javascript">
jQuery(document).ready(function() {
    jQuery('#2yrschedule').DataTable({
        "scrollX": true,
		"lengthMenu": [ [10, 25, 50, 100, -1], [10, 25, 50, 100, "All"] ]
});
} );


</script>


<?php

	include('../includes/footer.shtml');
?>
