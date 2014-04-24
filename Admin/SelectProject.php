<?php
    require_once(dirname(__FILE__)."/../common.php");

    if (!isset($loginSession))
        doUnauthenticatedRedirect();

    if (!$loginSession->isAdministrator)
        doUnauthorizedRedirect();
        
// Admin/SelectProject?for=
//  modify

    // Array of [ targetPage, Displayed ]
    $forMap = [
        'modify'     	=> [ 'Admin/EditProject',  'Modify' ],
//        'password'   	=> [ 'Admin/ChangeEmpPass', 'Change Password' ],
//        'paystubs'  	=> [ 'Employee/MyPay',      'View Pay Stubs' ]
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
      <th></th>
    </tr></thead>
    <tbody>
<?php
    foreach ($projects as $proj) {
			echo '<tr onclick="selectEmployee(this)" proj-id="'. $proj->id .'">';
			echo   '<td>'. htmlentities($proj->name) .'</td>';
//			echo   '<td>'. htmlentities($proj->) .'</td>';
//			echo   '<td>'. htmlentities($proj->) .'</td>';
			echo '</tr>';
    } 
?>
    </tbody>
    </table>
<?php } ?>
</div>