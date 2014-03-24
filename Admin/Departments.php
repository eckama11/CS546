<?php
    require_once(dirname(__FILE__)."/../common.php");

    if (!isset($loginSession))
        doUnauthenticatedRedirect();

    if (!$loginSession->isAdministrator)
        doUnauthorizedRedirect();

    try {
        $departments = $db->readDepartments();
    } catch (Exception $ex) {
        handleDBException($ex);
        return;
    }
?>
<div class="container">
    <legend>Defined Departments</legend>
    <table class="table table-striped table-bordered table-condensed">
    <thead><tr>
      <th>ID</th>
      <th>Name</th>
      <th>Managers</th>
      <th>Developers</th>
      <th>Administrators</th>
    </tr></thead>
    <tbody>
<?php
    function getEmployees(Department $dept, EmployeeType $type) {
        $emps = $GLOBALS['db']->readEmployeesForDepartment($dept->id, $type);
        return implode(", ", array_map(function($emp) { return $emp->name; }, $emps));
    }

    foreach ($departments as $dept) {
        $managers = getEmployees($dept, EmployeeType::Manager());
        $devs = getEmployees($dept, EmployeeType::SoftwareDeveloper());
        $admins = getEmployees($dept, EmployeeType::Administrator());

        echo '<tr>';
        echo   '<td style="text-align:right;">'. htmlentities($dept->id) .'</td>';
        echo   '<td>'. htmlentities($dept->name) .'</td>';
        echo   '<td>'. htmlentities($managers) .'</td>';
        echo   '<td>'. htmlentities($devs) .'</td>';
        echo   '<td>'. htmlentities($admins) .'</td>';
        echo '</tr>';
    } 
?>
    </tbody>
    </table>
</div>