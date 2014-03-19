<?php
    require_once(dirname(__FILE__)."/../common.php");

    if (!isset($loginSession))
        doUnauthenticatedRedirect();

    if (!$loginSession->isAdministrator)
        doUnauthorizedRedirect();
        
// Admin/SelectEmployee?for=
//  activate
//  deactivate
//  modify
//  password
//  paystubs

    // Array of [ activeFlag, targetPage, ???? ]
    $forMap = [
        'activation'	=> [ true, 'Admin/Activation',    'Deactivate/Activate' ],
        'modify'     	=> [ true, 'Admin/EditEmployee',  'Modify' ],
        'password'   	=> [ true, 'Admin/ChangeEmpPass', 'Change Password' ],
        'paystubs'  	=> [ true, 'Employee/MyPay',      'View Pay Stubs' ]
    ];

    $for = @$forMap[@$_GET['for']];
    if (!$for)
        doUnauthorizedRedirect();

    $activeFlag = $for[0];
    $targetPage = "page.php/". $for[1];
    $title = $for[2];

    try {
        $employees = $db->readEmployees("1");
        $employeesDe = $db->readEmployees("0");
    } catch (Exception $ex) {
        handleDBException($ex);
        return;
    }

?>
<script>
    function selectEmployee(row) {
        var id = row.getAttribute('emp-id');
        window.location.href = '<?php echo addcslashes(htmlentities($targetPage), "\0..\37!@\\\177..\377"); ?>?id=' + id;
    } // selectEmployee(row)
</script>
<div class="container col-md-6 col-md-offset-3">
    <legend>Select Employee to <?php echo htmlentities($title); ?></legend>
    <table class="table table-striped table-hover table-bordered table-condensed">
    <thead><tr>
      <th>Name</th>
      <th>Address</th>
      <th>Tax ID</th>
      <?php 
      	if ($title == "Deactivate/Activate") {
      		echo '<th>Status</th>';
      	}
      ?>
    </tr></thead>
    <tbody>
<?php
    foreach ($employees as $emp) {
        echo '<tr onclick="selectEmployee(this)" emp-id="'. $emp->id .'">';
        echo   '<td>'. htmlentities($emp->name) .'</td>';
        echo   '<td>'. htmlentities($emp->address) .'</td>';
        echo   '<td>'. htmlentities($emp->taxId) .'</td>';
        if ($title == "Deactivate/Activate") {
        	echo   '<td>Active</td>';
        }
        echo '</tr>';
    } // foreach
    
    if ($title == "Deactivate/Activate") {
    	foreach ($employeesDe as $emp) {
			if (htmlentities($emp->activeFlag) == null) {
				$status = "0";
			} else {
				$status = "1";
			}
			echo '<tr onclick="selectEmployee(this)" emp-id="'. $emp->id .'">';
			echo   '<td>'. htmlentities($emp->name) .'</td>';
			echo   '<td>'. htmlentities($emp->address) .'</td>';
			echo   '<td>'. htmlentities($emp->taxId) .'</td>';
			echo   '<td>Inactive</td>';
			echo '</tr>';
    	}
    }
?>
    </tbody>
    </table>
</div>
