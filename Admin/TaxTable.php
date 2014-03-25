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
    <table class="table">
    <tr>
        <th>Standard Deduction</th>
        <td style="text-align:right">$ 5,000.00</td>
    </tr>
    <tr>
        <th>Per Deduction Allowance</th>
        <td style="text-align:right">$ 1,000.00</td>
    </tr>
    </table>
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
    
    <p>For example, an employee who is paid a yearly salary of $35,000 and claims 2 deductions would be taxed as follows:</p>
    
    <table class="table">
    <tr>
        <th>1) Gross Income</th>
        <td style="text-align:right">$ 35,000.00</td>
    </tr>
    <tr>
        <th>2) Standard Deduction</th>
        <td style="text-align:right">$ 5,000.00</td>
    </tr>
    <tr>
        <th>3) Deductions Claimed</th>
        <td style="text-align:right">2</td>
    </tr>
    <tr>
        <th>4) Deduction Allowance<br/>($1,000.00 times line 3)</th>
        <td style="text-align:right">$ 2,000.00</td>
    </tr>
    <tr>
        <th>5) Total Deductions<br/>(line 2 plus line 4)</th>
        <td style="text-align:right">$ 7,000.00</td>
    </tr>
    <tr>
        <th>5) Taxable Income<br/>(line 1 minus line 5)</th>
        <td style="text-align:right">$ 28,000.00</td>
    </tr>
    <tr>
        <th>6) Tax Rate Applied<br/>(from tax table above)</th>
        <td style="text-align:right">15.00 %</td>
    </tr>
    <tr>
        <th>7) Tax<br/>(line 5 times line 6)</th>
        <td style="text-align:right">$ 4,200.00</td>
    </tr>
    <tr>
        <th>8) After Tax Income<br/>(line 1 minus line 7)</th>
        <td style="text-align:right">$ 30,800.00</td>
    </tr>
    </table>
</div>