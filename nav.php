       
        
    	<p><a href="coursemod.php?=<?php echo session_id(); ?>">Change Courses</a></p>
        <p><a href="coursereport.php?=<?php echo session_id(); ?>">Course Report</a></p>
        <p><a href="changeofferings.php?=<?php echo session_id(); ?>">Modify/Delete Offerings</a></p>

        
		
        <?php
		echo "<div class='topborderpurple topfifty bottomtwenty' style='margin-left: 0px;'>".
			"<form action='/?logout=true' method='post'>".
			"<input type='hidden' name='exit' id='exit' value='true'/>".
            "<input type='submit' value='Exit the system' /></form></div>";
?>