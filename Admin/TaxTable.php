<?php
    require_once(dirname(__FILE__)."/../common.php");

    if (!isset($loginSession))
        doUnauthenticatedRedirect();

    if (!$loginSession->isAdministrator)
        doUnauthorizedRedirect();

    try {
        $taxRates = $db->readTaxRates();
    } catch (Exception $ex) {
        handleDBException($ex);
        return;
    }
?>
<div class="container col-md-6 col-md-offset-3">
    <legend>Defined Tax Rates</legend>
    <table class="table table-striped table-bordered table-condensed">
    <thead><tr>
      <th>ID</th>
      <th>Minimum Salary</th>
      <th>Tax Rate %</th>
    </tr></thead>
    <tbody>
<?php
    foreach ($taxRates as $rate) {
        echo '<tr>';
        echo   '<td style="text-align:right;">'. htmlentities($rate->id) .'</td>';
        echo   '<td style="text-align:right;">'. htmlentities('$ '. number_format($rate->minimumSalary, 2)) .'</td>';
        echo   '<td style="text-align:right;">'. htmlentities(number_format(($rate->taxRate * 100), 2) .' %') .'</td>';
        echo '</tr>';
    } 
?>
    </tbody>
    </table>
</div>