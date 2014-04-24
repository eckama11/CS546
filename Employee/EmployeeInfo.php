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

        foreach ($history as $entry) {
            foreach ($entry->departments as $dept) {
                $effDate = $entry->endDate;
                if ($effDate == null) $effDate = new DateTime();
                $dept->managers = $db->readEmployeesForDepartment($dept->id, EmployeeType::Manager(), $effDate);
                $dept->managers = array_map(function($mgr) { return $mgr->name; }, $dept->managers);
            } // foreach
        } // foreach

?>
<script>
    define(
        'EmployeeInfoData',
        ['models/EmployeeHistoryCollection', 'models/RankCollection', 'models/DepartmentCollection'],
        function(EmployeeHistoryCollection, RankCollection, DepartmentCollection) {
            return {
                //employeeId : <?= $employee->id ?>,
                history : new EmployeeHistoryCollection(<?= json_encode($history) ?>),
                //ranks : new RankCollection(<?= json_encode($db->readRanks()) ?>),
                //departments : new DepartmentCollection(<?= json_encode($db->readDepartments()) ?>)
            };
        });
</script>
<?php
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
		  <th>Current Status</th>
		  <td><?php
            echo ($employee->isActive
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
    <div id="spinner" class="container" style="padding-bottom:10px;text-align:center">
        <img src="spinner.gif">
    </div>
    <table id="EmployeeSalaryHistory" class="table table-striped table-bordered table-condensed"></table>

<script>

var salaryHistoryView;

require([
    "views/EmployeeSalaryHistoryView",
    "EmployeeInfoData"
], function(EmployeeSalaryHistoryView, data) {
    registerBuildUI(function($) {
        salaryHistoryView = new EmployeeSalaryHistoryView({
                                el : $("#EmployeeSalaryHistory"),
                                collection : data.history
                            }).render();
        $("#spinner").hide();
    });
});

</script>

<?php
} // showEmployeeInfo
