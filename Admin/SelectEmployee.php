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

  // Array of [ activeFlag, targetPage ]
  $forMap = [
    'activate'   => [ false, 'Reactivate',    'Reactivate' ],
    'deactivate' => [ true,  'Deactivate',    'Deactivate' ],
    'modify'     => [ true,  'Modify',        'Modify' ],
    'password'   => [ true,  'ChangeEmpPass', 'Change Password' ],
    'paystubs'   => [ true,  'ViewEmpStub',   'View Pay Stubs' ]
  ];

  $for = @$forMap[@$_GET['for']];
  if (!$for)
    doUnauthorizedRedirect();

  $activeFlag = $for[0];
  $targetPage = "page.php/Admin/". $for[1];
  $title = $for[2]

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
    </tr></thead>
    <tbody>
<?php

    $emps = $db->readEmployees($activeFlag);
    foreach ($emps as $emp) {
        echo '<tr onclick="selectEmployee(this)" emp-id="'. $emp->id .'">';
        echo   '<td>'. htmlentities($emp->name) .'</td>';
        echo   '<td>'. htmlentities($emp->address) .'</td>';
        echo   '<td>'. htmlentities($emp->taxId) .'</td>';
        echo '</tr>';
    } // foreach
?>
    </tbody>
    </table>
</div>
