<?php
    require_once(dirname(__FILE__)."/../common.php");
    if (!isset($loginSession))
        doUnauthenticatedRedirect();

    try {
        $departments = $db->readDepartmentsForEmployee( $loginSession->authenticatedEmployee->id );

        for ($i = count($departments) - 1; $i >= 0; --$i) {
            $managers = $db->readEmployeesForDepartment($departments[$i]->id, EmployeeType::Manager());
            $managers = array_map(function($mgr) { return $mgr->name; }, $managers);
            $departments[$i] = (Object)[ 'name' => $departments[$i]->name, 'managers' => $managers ];
        } // for
    } catch (Exception $ex) {
        handleDBException($ex);
        return;
    }

    $status = htmlentities($loginSession->authenticatedEmployee->activeFlag);
    if($status == null) {
    	$status = "Inactive";
    }
    else {
    	$status = "Active";
    }
?>
<div class="container">
	<legend>Employee information for <?php echo htmlentities($loginSession->authenticatedEmployee->name); ?></legend>
	<table class="table table-striped table-bordered table-condensed">
		<tr>
		  <th>Employee ID</th>
		  <td><?php echo htmlentities($loginSession->authenticatedEmployee->id); ?></td>
		</tr>
		<tr>
		  <th>Username</th>
		  <td><?php echo htmlentities($loginSession->authenticatedEmployee->username); ?></td>
		</tr>
		<tr>
		  <th>Status</th>
		  <td><?php echo $status; ?></td>
		</tr>
		<tr>
		  <th>Address</th>
		  <td><?php echo htmlentities($loginSession->authenticatedEmployee->address); ?></td>
		</tr>
		<tr>
		  <th>Tax Id</th>
		  <td><?php echo htmlentities($loginSession->authenticatedEmployee->taxId); ?></td>
		</tr>
		<tr>
		  <th>Number of Deductions</th>
		  <td><?php echo htmlentities($loginSession->authenticatedEmployee->numDeductions); ?></td>
		</tr>
		<tr>
		  <th>Rank</th>
		  <td><?php echo htmlentities($loginSession->authenticatedEmployee->rank->name); ?></td>
		</tr>
		<tr>
		  <th>Departments</th>
		  <td><?php
            echo <<<EOT
            <table style="width:100%">
                <thead>
                    <tr style="border-bottom:1px solid black">
                        <th>Name</th>
                        <th>Manager</th>
                    </tr>
                </thead>
                <tbody>
EOT;

            foreach ($departments as $dept) {
                if (count($dept->managers) > 0)
                    $managers = implode(", ", $dept->managers);
                else
                    $managers = 'No Manager Assigned';

                echo '<tr>'.
                        '<td>'. htmlentities($dept->name) .'</td>'.
                        '<td>'. htmlentities($managers) .'</td>'.
                     '</tr>';
            } // foreach

            echo '</tbody></table>';
          ?></td>
		</tr>
		<tr>
		  <th>Yearly Salary</th>
		  <td><?php echo htmlentities(sprintf("\$ %.2f", $loginSession->authenticatedEmployee->salary)); ?></td>
		</tr>
	</table>
</div>