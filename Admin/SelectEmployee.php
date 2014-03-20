<?php
    require_once(dirname(__FILE__)."/../common.php");

    if (!isset($loginSession))
        doUnauthenticatedRedirect();

    if (!$loginSession->isAdministrator)
        doUnauthorizedRedirect();
        
// Admin/SelectEmployee?for=
//  activation
//  modify
//  password
//  paystubs

    // Array of [ targetPage, Displayed ]
    $forMap = [
        'activation'	=> [ 'Admin/Activation',    'Deactivate/Activate' ],
        'modify'     	=> [ 'Admin/EditEmployee',  'Modify' ],
        'password'   	=> [ 'Admin/ChangeEmpPass', 'Change Password' ],
        'paystubs'  	=> [ 'Employee/MyPay',      'View Pay Stubs' ]
    ];

    $for = @$_GET['for'];
    $item = @$forMap[$for];
    if (!$item)
        doUnauthorizedRedirect();

    $targetPage = "page.php/". $item[0];
    $title = $item[1];

    try {
        $employees = ($db->readEmployees(true));
        if ($for == "activation")
        	$employees = array_Merge($employees, $db->readEmployees(false));
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
      	if ($for== "activation") {
      		echo '<th>Status</th>';
      	}
      ?>
    </tr></thead>
    <tbody>
<?php
    foreach ($employees as $emp) {
    	if ($for != "activation" || $loginSession->authenticatedEmployee->id != $emp->id) {
			echo '<tr onclick="selectEmployee(this)" emp-id="'. $emp->id .'">';
			echo   '<td>'. htmlentities($emp->name) .'</td>';
			echo   '<td>'. htmlentities($emp->address) .'</td>';
			echo   '<td>'. htmlentities($emp->taxId) .'</td>';
			if ($for == "activation") {
				echo   '<td>'.($emp->activeFlag?"Active":"Inactive").'</td>';	
			}
			echo '</tr>';
		}
    } 
?>
    </tbody>
    </table>
</div>
