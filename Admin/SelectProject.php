<?php
    require_once(dirname(__FILE__)."/../common.php");

    if (!isset($loginSession))
        doUnauthenticatedRedirect();

    if (!$loginSession->isAdministrator)
        doUnauthorizedRedirect();
        
// Admin/SelectProject?for=
//  info
//  modify

    // Array of [ targetPage, Displayed ]
    $forMap = [
        'info'     	        => [ 'Admin/ProjectInfo',  'View Info' ],
        'modifyInfo'    	=> [ 'Admin/EditProject',  'Modify Info' ],
        'modifyEmployees'   => [ 'Admin/EditProjectEmployees', 'Modify Employees' ],
    ];

    $for = @$_GET['for'];
    $item = @$forMap[$for];
    if (!$item)
        doUnauthorizedRedirect();

    $targetPage = "page.php/". $item[0];
    $title = $item[1];

    try {
        $projects = ($db->readProjects(true));
    } catch (Exception $ex) {
        handleDBException($ex);
        return;
    }

?>
<script>
    function selectProject(row) {
        var id = row.getAttribute('proj-id');
        window.location.href = '<?php echo addcslashes(htmlentities($targetPage), "\0..\37!@\\\177..\377"); ?>?id=' + id;
    } // selectProject(row)
</script>
<div class="container col-md-6 col-md-offset-3">
<?php if (count($projects) == 0) { ?>
    <div>Sorry, there are currently no projects to display.</div>
    <div>Please check back later.</div>
<?php } else { ?>
    <legend>Select Project to <?php echo htmlentities($title); ?></legend>
    <table class="table table-striped table-hover table-bordered table-condensed">
    <thead><tr>
      <th>Name</th>
      <th>Start Date</th>
      <th>End Date</th>
    </tr></thead>
    <tbody>
<?php
    foreach ($projects as $proj) {
			echo '<tr onclick="selectProject(this)" proj-id="'. $proj->id .'">';
			echo   '<td>'. htmlentities($proj->name) .'</td>';
			echo   '<td>'. htmlentities($proj->startDate->format("Y-m-d")) .'</td>';
			echo   '<td>'. ($proj->endDate ? htmlentities($proj->endDate->format("Y-m-d")) : '') .'</td>';
			echo '</tr>';
    } 

	if ($title == 'View Info') {
		echo '<tr onclick="selectProject(this)" proj-id="0">';
		echo '<td>All Projects</td>';
		echo '<td>Beginning of Time</td>';
		echo '<td>End of Time</td>';
		echo '</tr>';
	}
?>
    </tbody>
    </table>
<?php } ?>
</div>
