<?php
require_once(dirname(__FILE__)."/../common.php");

function showEmployeeInfo( $employee ) {
    try {
        $db = $GLOBALS['db'];
        $departments = $db->readDepartmentsForEmployee( $employee->id );

        for ($i = count($departments) - 1; $i >= 0; --$i) {
            $managers = $db->readEmployeesForDepartment($departments[$i]->id, EmployeeType::Manager());
            $managers = array_map(function($mgr) { return $mgr->name; }, $managers);
            $departments[$i] = (Object)[ 'name' => $departments[$i]->name, 'managers' => $managers ];
        } // for
    } catch (Exception $ex) {
        handleDBException($ex);
        return;
    }

?>
	<table class="table table-striped table-bordered table-condensed">
		<tr>
		  <th>Employee ID</th>
		  <td><?php echo htmlentities($employee->id); ?></td>
		</tr>
		<tr>
		  <th>Username</th>
		  <td><?php echo htmlentities($employee->username); ?></td>
		</tr>
		<tr>
		  <th>Status</th>
		  <td><?php
            echo ($employee->activeFlag
                ? 'Active'
                : '<span class="inactive">Inactive</span>'
              );
          ?></td>
		</tr>
		<tr>
		  <th>Address</th>
		  <td><?php echo htmlentities($employee->address); ?></td>
		</tr>
		<tr>
		  <th>Tax Id</th>
		  <td><?php echo htmlentities($employee->taxId); ?></td>
		</tr>
		<tr>
		  <th>Number of Deductions</th>
		  <td><?php echo htmlentities($employee->numDeductions); ?></td>
		</tr>
		<tr>
		  <th>Rank</th>
		  <td><?php echo htmlentities($employee->rank->name); ?></td>
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
		  <td><?php echo htmlentities(sprintf("\$ %.2f", $employee->salary)); ?></td>
		</tr>
	</table>
<?php
} // showEmployeeInfo
