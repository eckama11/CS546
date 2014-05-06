<?php
    require_once(dirname(__FILE__)."/../common.php");
    if (!isset($loginSession))
        doUnauthenticatedRedirect();

    if (!$loginSession->isAdministrator)
        doUnauthorizedRedirect();
	
    $projectPeriodStartDate = @$_POST['projectPeriodStartDate'];
    $projectPeriodStartDate = ($projectPeriodStartDate
                ? new DateTime($projectPeriodStartDate)
                : new DateTime());
    $projectPeriodStartDate = new DateTime( $projectPeriodStartDate->format("Y-m-01T00:00:00P") );

    $projectPeriodEndDate = @$_POST['projectPeriodEndDate'];
    if ($projectPeriodEndDate) {
        $projectPeriodEndDate = new DateTime($projectPeriodEndDate);
        $projectPeriodEndDate = new DateTime( $projectPeriodEndDate->format("Y-m-01T00:00:00P") );
    } else {
        $projectPeriodEndDate = (clone $projectPeriodStartDate);
    }
    $projectPeriodEndDate->add(new DateInterval('P1M'))->sub(new DateInterval('P1D'));

    $projectPeriodDuration = $projectPeriodEndDate->diff($projectPeriodStartDate)->format("%a") + 1;

	$projectId = @$_GET['id'];
    if ($projectId == null)
        $projectId = @$_POST['id'];

	$projectAll = 0;
	try {
        $projectName = $db->readProject($projectId)->name;

		$projectArray = ($db->readProjectChartEmployees($projectId, $projectPeriodStartDate, $projectPeriodEndDate));
		$projectDepartments = ($db->readProjectChartProjects($projectId, $projectPeriodStartDate, $projectPeriodEndDate));
		$allProjects = ($db->readProjectChartAllDepartments($projectAll, $projectPeriodStartDate, $projectPeriodEndDate));
		$allProjectsOther = ($db->readProjectChartAll($projectAll, $projectPeriodStartDate, $projectPeriodEndDate));
	} catch (Exception $ex) {
        handleDBException($ex);
        return;
	}

    $projectPeriod = htmlentities($projectPeriodStartDate->format("F, Y"));
    $projectPeriodStartDate = htmlentities($projectPeriodStartDate->format("m/d/Y"));
    $projectPeriodEndDate = htmlentities($projectPeriodEndDate->format("m/d/Y"));

/*
 *	An administrator should be able to generate a report on each 
 *	project which should display the 
 *	people associated with the project and the total cost.
*/

?>
<!--Load the AJAX API-->
<script type="text/javascript" src="https://www.google.com/jsapi"></script>
<script type="text/javascript">

  // Load the Visualization API and the piechart package.
  google.load('visualization', '1.0', {'packages':['corechart']});

  // Set a callback to run when the Google Visualization API is loaded.
  google.setOnLoadCallback(drawChart);
  google.setOnLoadCallback(drawChart2);
  google.setOnLoadCallback(drawChart3);

  var chartWidth = 800;
  var chartHeight = 500;

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
				   'width':chartWidth,
				   'height':chartHeight};

	// Instantiate and draw our chart, passing in some options.
	var chart = new google.visualization.PieChart(document.getElementById('chart_byEmployee'));
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
				   'width':chartWidth,
				   'height':chartHeight};

	// Instantiate and draw our chart, passing in some options.
	var chart = new google.visualization.PieChart(document.getElementById('chart_byDepartment'));
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
				   'width':chartWidth,
				   'height':chartHeight};

	// Instantiate and draw our chart, passing in some options.
	var chart = new google.visualization.PieChart(document.getElementById('chart_div3'));
	chart.draw(data, options);
  }

  function validateDates(form) {
    var startDate = new Date($(form.elements.projectPeriodStartDate).val());
    var endDate = new Date($(form.elements.projectPeriodEndDate).val());

    if (endDate < startDate) {
        showError("The end date must come after the start date.");
        return false;
    }

    return true;
  }
</script>

<div class="container">
    <div id="content">
        <form class="form" method="post" onsubmit="return validateDates(this)">
            <legend>Project Report</legend>

            <div class="form-group">
                <label class="control-label">Report Period</label>
                <div class="input-group">
                    <input data-provide="datepicker" data-date-autoclose="true" data-date-min-view-mode="months" class="form-control" type="text" name="projectPeriodStartDate" id="projectPeriodStartDate" placeholder="Enter project report start date" value="<?php echo $projectPeriodStartDate; ?>"/>
                    <span class="input-group-addon">to</span>
                    <input data-provide="datepicker" data-date-autoclose="true" data-date-min-view-mode="months" class="form-control" type="text" name="projectPeriodEndDate" id="projectPeriodEndDate" placeholder="Enter project report end date" value="<?php echo $projectPeriodEndDate; ?>"/>
                </div>
            </div>

            <input type="hidden" name="id" value="<?php echo htmlentities($projectId); ?>"/>
            <button style="margin-top: 10px" type="submit" class="btn btn-default">Apply</button>
        </form>
    </div>

    <br/>
    <table class="table">
        <tr>
            <th>Report Period</th>
            <td style="width:50%">
                <span><?php echo $projectPeriod; ?></span>
                </span>
            </td>
        </tr>
        <tr>
            <th>Report Period Start Date</th>
            <td>
                <span><?php echo $projectPeriodStartDate; ?></span>
            </td>
        </tr>
        <tr>
            <th>Report Period End Date</th>
            <td>
                <span><?php echo $projectPeriodEndDate; ?></span>
            </td>
        </tr>
        <tr>
            <th>Report Period Duration</th>
            <td><span><?php echo $projectPeriodDuration; ?></span></td>
        </tr>
    </table>

    <!-- Nav tabs -->
    <ul class="nav nav-tabs">
        <li class="active"><a href="#byDepartment" data-toggle="tab">By Department</a></li>
        <li><a href="#byEmployee" data-toggle="tab">By Employee</a></li>
        <li><a href="#byProject" data-toggle="tab">By Project</a></li>
    </ul>

    <!-- Tab panes -->
    <div class="tab-content">
        <div class="tab-pane active" id="byDepartment">
            <legend>Department Report</legend>
            <div id="chart_byDepartment"></div>
            <br>
            <legend>Department Report Table</legend>
            <?php if (count($projectDepartments) == 0) { ?>
    		<div>Sorry, there are currently no departments to display.</div>
    		<div>Please check back later.</div>
			<?php } else { ?>
				<table class="table table-striped table-bordered">
					<thead>
						<tr>
							<th>Department</th>
							<th>Costs</th>
						</tr>
					</thead>
					<tbody>
			<?php
                    $total = 0;
    				foreach ($projectDepartments as $pro) {
                        $total += $pro[1];
						echo '<tr>';
						echo   '<td>'. htmlentities($pro[0]) .'</td>';
						echo   '<td class="numeric">$&nbsp;'. htmlentities(number_format($pro[1], 2)) .'</td>';
						echo '</tr>';
                    } 
                    echo '<tr>';
                    echo   '<th>Total</th>';
                    echo   '<th class="numeric">$&nbsp;'. htmlentities(number_format($total, 2)) .'</th>';
                    echo '</tr>';
			?>
				</tbody>
				</table>
			<?php } ?>
        </div>
        <div class="tab-pane" id="byEmployee">
            <legend>Employee Report</legend>
            <div id="chart_byEmployee"></div>
            <br>
            <legend>Employee Report Table</legend>
            <?php if (count($projectArray) == 0) { ?>
    		<div>Sorry, there are currently no employees to display.</div>
    		<div>Please check back later.</div>
			<?php } else { ?>
				<table class="table table-striped table-bordered">
					<thead>
						<tr>
							<th>Employee</th>
							<th>Costs</th>
						</tr>
					</thead>
					<tbody>
			<?php
                    $total = 0;
    				foreach ($projectArray as $pArr) {
                        $total += $pArr[1];
						echo '<tr>';
						echo   '<td>'. htmlentities($pArr[0]) .'</td>';
						echo   '<td class="numeric">$&nbsp;'. htmlentities(number_format($pArr[1], 2)) .'</td>';
						echo '</tr>';
                    } 
                    echo '<tr>';
                    echo   '<th>Total</th>';
                    echo   '<th class="numeric">$&nbsp;'. htmlentities(number_format($total, 2)) .'</th>';
                    echo '</tr>';
			?>
					</tbody>
				</table>
			<?php } ?>
        </div>
        <div class="tab-pane" id="byProject">
            <legend>All Projects Department Report</legend>
            <div id="chart_div3"></div>
            <br>
            <legend>All Projects Department Report Table</legend>
            <?php if (count($allProjects) == 0 && count($allProjectsOther) == 0) { ?>
    		<div>Sorry, there are currently no projects to display.</div>
    		<div>Please check back later.</div>
			<?php } else { ?>
				<table class="table table-striped table-bordered">
					<thead>
						<tr>
							<th>Department</th>
							<th>Costs</th>
						</tr>
					</thead>
					<tbody>
			<?php
                    $total = 0;
    				foreach ($allProjects as $all) {
                        $total += $all[1];
						echo '<tr>';
						echo   '<td>'. htmlentities($all[0]) .'</td>';
						echo   '<td class="numeric">'. htmlentities(number_format($all[1], 2)) .'</td>';
						echo '</tr>';
                    } 

    				foreach ($allProjectsOther as $allOther) {
                        $total += $allOther[1];
						echo '<tr>';
						echo   '<td>'. htmlentities($allOther[0]) .'</td>';
						echo   '<td class="numeric">'. htmlentities(number_format($allOther[1], 2)) .'</td>';
						echo '</tr>';
                    }

                    echo '<tr>';
                    echo   '<th>Total</th>';
                    echo   '<th class="numeric">$&nbsp;'. htmlentities(number_format($total, 2)) .'</th>';
                    echo '</tr>';
			?>
					</tbody>
				</table>
			<?php } ?>
        </div>
    </div>
<div>
<br/>
<script>
require(["main"], function() {
    require([
        "bootstrap-datepicker"
    ], function(DepartmentSelectorView, data) {
        registerBuildUI(function($) {
            // Init date pickers
            $('[data-provide="datepicker"]').datepicker();
            
            $('#projectPeriodEndDate').on(
                "change",
                function(e) {
                    var $el = $(e.target);

                    var val = $el.val();

                    var desired = new Date(val);
                    desired = new Date(
                                desired.getFullYear(), desired.getMonth() + 1, 0,
                                0, 0, 0
                              );
                    desired = formatDate(desired, "m/d/Y");

                    if (val != desired)
                        $el.datepicker('update', desired);
                }
            );
        });
    });
});
</script>