<?php
    require_once(dirname(__FILE__)."/../common.php");
    if (!isset($loginSession))
        doUnauthenticatedRedirect();

    if (!$loginSession->isAdministrator)
        doUnauthorizedRedirect();
	
	$currentDate = new DateTime();
    $currentDate->setTimezone(new DateTimeZone('GMT'));

    $projectPeriodStartDate = new DateTime( $currentDate->format("Y-m-01T00:00:00P") );
    $projectPeriodEndDate = (clone $projectPeriodStartDate);
    $projectPeriodEndDate->add(new DateInterval('P1M'))->sub(new DateInterval('P1D'));

    $projectPeriodDuration = $projectPeriodEndDate->diff($projectPeriodStartDate)->format("%a") + 1;

    $projectPeriod = htmlentities($projectPeriodStartDate->format("F, Y"));
    $projectPeriodStartDate = htmlentities($projectPeriodStartDate->format("Y-m-d"));
    $projectPeriodEndDate = htmlentities($projectPeriodEndDate->format("Y-m-d"));
	
	$projectId = @$_GET['id'];
	$projectAll = 0;
	try {
        $projectName = $db->readProject($projectId)->name;
		$projectArray = ($db->readProjectChartEmployees($projectId));
		$projectDepartments = ($db->readProjectChartProjects($projectId));
		$allProjects = ($db->readProjectChartAllDepartments($projectAll));
		$allProjectsOther = ($db->readProjectChartAll($projectAll));
	} catch (Exception $ex) {
        handleDBException($ex);
        return;
	}

/*
 *	An administrator should be able to generate a report on each 
 *	project which should display the 
 *	people associated with the project and the total cost.
*/

?>
<style type="text/css">
  .navigateButton {
    padding:5px 5px;
    font-weight: bold;
    cursor: pointer;
    border: 1px solid #ccc;
    border-radius: 5px;
  }

  .navigateButton:hover {
    color: red;
    background-color: #ccc;
    border: 1px solid black;
  }
</style>
<!--Load the AJAX API-->
<script type="text/javascript" src="https://www.google.com/jsapi"></script>
<script type="text/javascript">

  // Load the Visualization API and the piechart package.
  google.load('visualization', '1.0', {'packages':['corechart']});

  // Set a callback to run when the Google Visualization API is loaded.
  google.setOnLoadCallback(drawChart);
  google.setOnLoadCallback(drawChart2);
  google.setOnLoadCallback(drawChart3);

  // Callback that creates and populates a data table,
  // instantiates the pie chart, passes in the data and
  // draws it.
  
  function drawChart() {

	// Create the data table.
	var data = new google.visualization.DataTable();
	data.addColumn('string', 'Costs');
	data.addColumn('number', 'Dollars');
	data.addRows(<?= json_encode($projectArray) ?>);

	// Set chart options
	var options = {'title':<?= json_encode($projectName) ?> ,
				   'width':800,
				   'height':500};

	// Instantiate and draw our chart, passing in some options.
	var chart = new google.visualization.PieChart(document.getElementById('chart_div'));
	chart.draw(data, options);
  }
  
  function drawChart2() {

	// Create the data table.
	var data = new google.visualization.DataTable();
	data.addColumn('string', 'Costs');
	data.addColumn('number', 'Dollars');
	data.addRows(<?= json_encode($projectDepartments) ?>);

	// Set chart options
	var options = {'title':<?= json_encode($projectName) ?>,
				   'width':800,
				   'height':500};

	// Instantiate and draw our chart, passing in some options.
	var chart = new google.visualization.PieChart(document.getElementById('chart_div2'));
	chart.draw(data, options);
  }
  
  function drawChart3() {

	// Create the data table.
	var data = new google.visualization.DataTable();
	data.addColumn('string', 'Costs');
	data.addColumn('number', 'Dollars');
	data.addRows(<?= json_encode($allProjects) ?>);
	data.addRows(<?= json_encode($allProjectsOther) ?>);
	// Set chart options
	var options = {'title': 'All Projects',
				   'width':800,
				   'height':500};

	// Instantiate and draw our chart, passing in some options.
	var chart = new google.visualization.PieChart(document.getElementById('chart_div3'));
	chart.draw(data, options);
  }
</script>

<script>
function generateReports(form) {
    $("#spinner").show();
    $("#content").hide();

    $.ajax({
        "type" : "POST",
        "url" : "Admin/doGenerateReports.php",
        "data" : $(form).serialize(),
        "dataType" : "json"
        })
        .done(function(data) {
            $("#spinner").hide();

            if (data.error != null) {
                showError(data.error);
                $("#content").show();
            } else {
                $("#successDiv .message").text(data.message);
                $("#successDiv").show();
            }
        })
        .fail(function( jqXHR, textStatus, errorThrown ) {
            console.log("Error: "+ textStatus +" (errorThrown="+ errorThrown +")");
            console.log(jqXHR.textContent);

            $("#spinner").hide();
            $("#content").show();
            showError("Request failed, unable to generate reports: "+ errorThrown);
        })

    return false;
} // generateProjectReports

function previousMonth() {
    updateProjectPeriodDisplay(-1);
} // previousMonth

function nextMonth() {
    updateProjectPeriodDisplay(1);
} // nextMonth

function updateProjectPeriodDisplay(addMonths) {
    var formElem = $('#generateForm input[name="projectPeriodStartDate"]');

    var date = new Date(formElem.val());
    date = new Date(date.getUTCFullYear(), date.getUTCMonth() + addMonths, date.getUTCDate());

    var endDate = new Date(date.getUTCFullYear(), date.getUTCMonth() + 1, 0);

    $("#projectPeriod").text(formatDate(date, "F, Y"));
    $("#projectPeriodStartDate").text(formatDate(date, "Y-m-d"));
    $("#projectPeriodEndDate").text(formatDate(endDate, "Y-m-d"));

    var numDays = endDate.getUTCDate() - date.getUTCDate() + 1;
    $("#projectPeriodDuration").text(numDays);

    formElem.val(formatDate(date, "Y-m-d"));
} // updateProjectPeriodDisplay
</script>
<div class="container col-md-6 col-md-offset-3">
	<div id="spinner" style="padding-bottom:10px;text-align:center;display:none">
        <div style="color:black;padding-bottom:32px;">Generating Reports for <?php echo $projectPeriodStartDate .' to '. $projectPeriodEndDate; ?>...</div>
        <img src="spinner.gif">
    </div>
    <div id="content">
        <form id="generateForm" class="form" onsubmit="return generateReports(this)">
            <legend>Generate Reports</legend>

            <table class="table">
                <tr>
                    <th>Report Period</th>
                    <td style="width:50%">
                        <span id="projectPeriod"><?php echo $projectPeriod; ?></span>
                        </span>
                    </td>
                </tr>
                <tr>
                    <th>Project Period Start Date</th>
                    <td>
                    	<span id="projectPeriodStartDate"><?php echo $projectPeriodStartDate; ?></span>
                    	<span style="float:right">
                    	<span class="navigateButton" onclick="previousMonth(event)">&lt;</span>
                        <span class="navigateButton" onclick="nextMonth(event)">&gt;</span>
                    </td>
                </tr>
                <tr>
                    <th>Project Period End Date</th>
                    <td>
                    	<span id="projectPeriodEndDate"><?php echo $projectPeriodEndDate; ?></span>
                    	<span style="float:right">
                    	<span class="navigateButton" onclick="previousMonth(event)">&lt;</span>
                        <span class="navigateButton" onclick="nextMonth(event)">&gt;</span>
                    </td>
                </tr>
                <tr>
                    <th>Project Period Duration</th>
                    <td><span id="projectPeriodDuration"><?php echo $projectPeriodDuration; ?></span></td>
                </tr>
            </table>

            <input type="hidden" name="projectPeriodStartDate" value="<?php echo htmlentities($projectPeriodStartDate); ?>"/>
            <button style="margin-top: 10px" type="submit" class="btn btn-default">Generate Reports</button>
        </form>
    </div>
    <legend>Employee Report</legend>
	<div id="chart_div"></div>
	<br>
	<legend>Department Report</legend>
	<div id="chart_div2"></div>
	<br>
	<legend>All Projects Department Report</legend>
	<div id="chart_div3"></div>
	<br>
<div>