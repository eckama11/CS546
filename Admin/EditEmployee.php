<?php
    require_once(dirname(__FILE__)."/../common.php");
    if (!isset($loginSession))
        doUnauthenticatedRedirect();
    if (!$loginSession->isAdministrator)
        doUnauthorizedRedirect();

    $employeeId = @$_GET['id'];
    $emp = null;

    try {
        if ($employeeId != null) {
            $employeeId = (int) $employeeId;
            $emp = $db->readEmployee($employeeId);
        }
    } catch (Exception $ex) {
        handleDBException($ex);
        return;
    }

    function empProperty($emp, $propName) {
        if ($emp != null)
            echo htmlentities($emp->{$propName});
    } // empProperty($emp, $propName)
?>

<script>
	function editEmployee(form) {
		var name = requiredField($(form.elements.name), "You must enter employee's name.");
		var address = requiredField($(form.elements.address), "You must enter employee's address.");
		var rank = requiredField($(form.elements.rank), "You must enter employee's rank");
		var departments = requiredField($(form.elements["departments[]"]), "You must select at least one department for employee");
		var taxid = requiredField($(form.elements.taxid), "You must enter employee's tax id");
		var numDeductions = requiredField($(form.elements.numDeductions), "You must enter employee's number of deductions");
		var salary = requiredField($(form.elements.salary), "You must enter employee's salary");
<?php if ($emp == null) { ?>
		var username = requiredField($(form.elements.username), "You must enter employee's username.");
		var password1 = requiredField($(form.elements.password1), "You must enter employee's password.");
		var password2 = requiredField($(form.elements.password2), "You must verify employee's password.");
        if (password1 != password2) {
            showError("The employee's password and verify password do not match.");
            return false;
        }

        if (password1.length < 8) {
            showError("The employee's password must be at least 8 characters long");
            return false;
        }
<?php } ?>

		if ((name == "") || (address == "") || (rank == "") || (departments == "") ||
		    (taxid == "") || (numDeductions == "") || (salary == ""))
        {
			showError("You must enter all form information to add employee.");
			return false;
		}

		$("#employeeDiv").hide();
		$("#spinner").show();

		$.ajax({
			"type" : "POST",
			"url" : "Admin/doEditEmployee.php",
			"data" : $(form).serialize(),
			"dataType" : "json"
			})
			.done(function(data) {
				$("#spinner").hide();

				if (data.error != null) {
					showError(data.error);
					$("#employeeDiv").show();
				} else
					$("#successDiv").show();
			})
			.fail(function( jqXHR, textStatus, errorThrown ) {
				console.log("Error: "+ textStatus +" (errorThrown="+ errorThrown +")");
				console.log(jqXHR.textContent);

                $("#spinner").hide();
                $("#employeeDiv").show();
                showError("Request failed, unable to update employee: "+ errorThrown);
			})

		return false;
	}
</script>  
         
<div class="container col-md-6 col-md-offset-3">
    <div id="spinner" class="col-md-2 col-md-offset-5" style="padding-bottom:10px;text-align:center;display:none">
        <div style="color:black;padding-bottom:32px;"><?php
            if ($emp != null)
                echo 'Updating Employee...';
            else
                echo 'Adding Employee...';
        ?></div>
        <img src="spinner.gif">
    </div>
    <div id="successDiv" class="col-md-3 col-md-offset-5" style="padding-bottom:10px; outline: 10px solid black;display:none">
        Employee has been successfully <?php echo ($emp == null) ? 'added' : 'updated'; ?>.
    </div>
	<div id="employeeDiv" class="row" >
		<legend><?php
            if ($emp != null)
                echo 'Update Employee';
            else
                echo 'Add Employee';
        ?></legend>
		<form role="form" onsubmit="return editEmployee(this);">
            <input type="hidden" name="id" value="<?php echo htmlentities($employeeId); ?>"/>

			<div id="my-tab-content" class="tab-content">
<?php if ($emp == null) { ?>
				<div class="form-group">
					<label>Username</label>
					<input type="text" class="form-control" name="username" id="username" placeholder="Enter Username"/>
                </div>
				<div class="form-group">
					<label>Password</label>
					<input type="password" class="form-control" name="password1" id="password1" placeholder="Enter password"/>
                </div>
				<div class="form-group">
					<label>Verify Password</label>
					<input type="password" class="form-control" name="password2" id="password2" placeholder="Verify password"/>
                </div>
<?php } ?>
				<div class="form-group">
					<label>Name</label>
					<input type="text" class="form-control" name="name" id="name" placeholder="Enter name" value="<?php empProperty($emp, 'name'); ?>"/>
				</div>
				<div class="form-group">
					<label>Address</label></br>
					<textarea class="form-control" rows="5" name="address" id="address" placeholder="Enter Address"><?php empProperty($emp, 'address'); ?></textarea>
				</div>
				<div class="form-group">
					<label>Rank</label>
					<select class="form-control" name="rank" id="rank">
						<?php
                            try {
                                $ranks = $db->readRanks();
                            } catch (Exception $ex) {
                                handleDBException();
                                exit;
                            }

                            echo '<option disabled '. ($emp == null ? ' selected' : '') .'>Select One</option>';

                            $empRankId = ($emp != null) ? $emp->rank->id : null;
							foreach ($ranks as $rank) {
                                echo '<option value="'. htmlentities($rank->id) .'"';
                                if ($rank->id == $empRankId)
                                    echo 'selected';
                                echo '>'. htmlentities($rank->name) .'</option>';
                            }
                        ?>
					</select>
				</div>
				<div class="form-group">
					<label>Departments</label>
					<select multiple class="form-control" name="departments[]" id="departments">
						<?php
                            try {
                                if ($emp != null) {
                                    $empDepts = $db->readDepartmentsForEmployee($emp->id);
                                    $empDepts = array_flip(array_map(function($dept) { return $dept->id; }, $empDepts));
                                } else
                                    $empDepts = [];

                                $depts = $db->readDepartments();
                            } catch (Exception $ex) {
                                handleDBException();
                                exit;
                            }

							foreach ($depts as $dept) {
								echo '<option value="'. htmlentities($dept->id) .'"';
                                if (array_key_exists($dept->id, $empDepts))
                                    echo 'selected';
                                echo '>'. htmlentities($dept->name) .'</option>';
							}
                        ?>
					</select>
				</div>
				<div class="form-group">
					<label>Tax ID</label>
					<input type="text" class="form-control" name="taxid" id="taxid" placeholder="Enter Soc Sec #" value="<?php empProperty($emp, 'taxId'); ?>"/>
				</div>
				<div class="form-group">
					<label>Number of Deductions</label>
					<input type="text" class="form-control" name="numDeductions" id="numDeductions" placeholder="Enter a number" value="<?php empProperty($emp, 'numDeductions'); ?>">
				</div>
				<div class="form-group">
					<label>Yearly Salary</label>
					<input type="text" class="form-control" name="salary" id="salary" placeholder="Enter a salary" value="<?php empProperty($emp, 'salary'); ?>">
				</div>
				<button type="submit" class="btn btn-default"><?php
                    if ($emp != null)
                        echo 'Update Employee';
                    else
                        echo 'Add Employee';
                ?></button>
				<br></br>
			</div>
		</form>
	</div>
</div> 
