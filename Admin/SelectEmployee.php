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
    'activate'   => [ false, 'Admin/Activation',    'Reactivate' ],
    'deactivate' => [ true,  'Admin/Activation',    'Deactivate' ],
    'modify'     => [ true,  'Admin/Modify',        'Modify' ],
    'password'   => [ true,  'Admin/ChangeEmpPass', 'Change Password' ],
    'paystubs'   => [ true,  'Admin/ViewEmpStub',   'View Pay Stubs' ]
  ];

  $for = @$forMap[@$_GET['for']];
  if (!$for)
    doUnauthorizedRedirect();

  $activeFlag = $for[0];
  $targetPage = $for[1];
  $title = $for[2]

?>
<div class="container col-md-6 col-md-offset-3">
    <div>Select Employee to <?php echo htmlentities($title); ?></div>
    <table class="table table-striped table-hover table-bordered table-condensed">
    <thead><tr>
      <th>Name</th>
      <th>Address</th>
      <th>Tax ID</th>
      <th></th>
    </tr></thead>
    <tbody>
<?php

    $emps = $db->readEmployees($activeFlag);
    foreach ($emps as $emp) {
        echo '<tr>';
        echo   '<td>'. htmlentities($emp->name) .'</td>';
        echo   '<td>'. htmlentities($emp->address) .'</td>';
        echo   '<td>'. htmlentities($emp->taxId) .'</td>';
        echo   '<td><span class="glyphicon glyphicon-ok"></span></td>';
        echo '</tr>';
    } // foreach
?>
    </tbody>
    </table>
</div>
