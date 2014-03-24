<?php
    require_once(dirname(__FILE__)."/../common.php");

    if (!isset($loginSession))
        doUnauthenticatedRedirect();

    if (!$loginSession->isAdministrator)
        doUnauthorizedRedirect();

    try {
        $ranks = $db->readRanks();
    } catch (Exception $ex) {
        handleDBException($ex);
        return;
    }
?>
<div class="container">
    <legend>Defined Employee Ranks</legend>
    <table class="table table-striped table-bordered table-condensed">
    <thead><tr>
      <th>ID</th>
      <th>Name</th>
      <th>Base Salary</th>
      <th>Employee Type</th>
    </tr></thead>
    <tbody>
<?php
    foreach ($ranks as $rank) {
        echo '<tr>';
        echo   '<td style="text-align:right;">'. htmlentities($rank->id) .'</td>';
        echo   '<td>'. htmlentities($rank->name) .'</td>';
        echo   '<td style="text-align:right;">'. htmlentities('$ '. number_format($rank->baseSalary, 2)) .'</td>';
        echo   '<td>'. htmlentities($rank->employeeType) .'</td>';
        echo '</tr>';
    } 
?>
    </tbody>
    </table>
</div>