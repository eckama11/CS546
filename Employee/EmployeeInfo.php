<?php
require_once(dirname(__FILE__)."/../common.php");

function showEmployeeInfo( $employee, $historyLimit = null ) {
    global $loginSession;

    $today = (new DateTime())->setTime(0, 0, 0);
    $forAuthenticatedEmployee = ($loginSession->authenticatedEmployee->id == $employee->id);

    try {
        $db = $GLOBALS['db'];

        // Don't show entire history if displaying info for the currently authenticated employee.
        // The idea is that the employee is not updating their own pay, and future entries
        // should probably not be exposed to them.
        $endDate = ($forAuthenticatedEmployee
            ? new DateTime()
            : null);

        $history = $db->readEmployeeHistory($employee->id, null, $endDate, $historyLimit);

        $result = [];
        foreach ($history as $entry) {
            $departments = $entry->departments;

            for ($i = count($departments) - 1; $i >= 0; --$i) {
                $managers = $db->readEmployeesForDepartment($departments[$i]->id, EmployeeType::Manager());
                $managers = array_map(function($mgr) { return $mgr->name; }, $managers);
                $departments[$i] = (Object)[ 'name' => $departments[$i]->name, 'managers' => $managers ];
            } // for

            $result[] = [
                    $entry,
                    $departments
                ];
        } // foreach
        $history = $result;
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
                : '<span class="upayInactive">Inactive</span>'
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
	</table>
<?php
    // Display the requested amount of history entries
?>
    <h4>Salary History</h4>
	<table class="table table-striped table-bordered table-condensed">
        <tr>
            <th>Start Date</th>
            <th>End Date</th>
            <th>Number of<br/>Deductions</th>
            <th>Rank</th>
            <th>Yearly<br/>Salary</th>
            <th>Departments</th>
        </tr>
<?php
    foreach ($history as $entry) {
        $departments = $entry[1];
        $entry = $entry[0];
?>
		<tr<?php
            if (($today >= $entry->startDate) &&
                (($entry->endDate == null) || ($today <= $entry->endDate)))
            {
                echo ' class="upayActive"';
            }
          ?>>
          <td><?php echo htmlentities($entry->startDate->format("Y-m-d")); ?></td>
          <td><?php
            if ($entry->endDate && (!$forAuthenticatedEmployee || ($today > $entry->endDate)))
                echo htmlentities($entry->endDate->format("Y-m-d"));
          ?></td>
		  <td class="numeric"><?php echo htmlentities($entry->numDeductions); ?></td>
		  <td><?php echo htmlentities($entry->rank->name); ?></td>
		  <td class="numeric"><?php echo htmlentities(sprintf("\$ %.2f", $entry->salary)); ?></td>
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
<?php
    }
    echo "</table>\n";
} // showEmployeeInfo
