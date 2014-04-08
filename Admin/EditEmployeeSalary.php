<?php
    require_once(dirname(__FILE__)."/../common.php");
    if (!isset($loginSession))
        doUnauthenticatedRedirect();

    if (!$loginSession->isAdministrator)
        doUnauthorizedRedirect();

    $employeeId = @$_GET['id'];
    $emp = null;

    try {
        $employeeId = (int) $employeeId;
        $emp = $db->readEmployee($employeeId);

        if (!$emp->activeFlag)
            throw new Exception("Inactive employees cannot be updated.");

        $empDepts = $db->readDepartmentsForEmployeeHistory($emp->current->id);
        $empDepts = array_flip(array_map(function($dept) { return $dept->id; }, $empDepts));

        $ranks = $db->readRanks();
        $depts = $db->readDepartments();
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
	function editEmployeeSalary(form) {
        var startDate = requiredField($(form.elements.startDate), dateMessage);
        startDate = (startDate != "" ? new Date(startDate) : "");

        var departments = requiredField($(form.elements["departments[]"]), "You must select at least one department for employee");

        var rankInput = form.elements.rank;
        var selRank = rankInput.options[rankInput.selectedIndex];
        var baseSalary = Number(selRank.getAttribute('rank-base-salary'));
        var rank = requiredField($(rankInput), "You must enter employee's rank");
        var numDeductions = requiredField($(form.elements.numDeductions), "You must enter employee's number of deductions");
        var salary = Number(requiredField($(form.elements.salary), "You must enter employee's salary"));

        if ((startDate == "") || (numDeductions == "") || (salary == "") || (rank == null) || (departments == null)) {
            showError("You must enter all form information.");
            return false;
        }

        if (salary < baseSalary) {
            showError("The salary cannot be less than the base salary assigned to the selected rank: "+ $(selRank).text());
            return false;
        }

		$("#employeeDiv").hide();
		$("#spinner").show();

		$.ajax({
			"type" : "POST",
			"url" : "Admin/doEditEmployeeSalary.php",
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
        <div style="color:black;padding-bottom:32px;">Updating Employee Salary...</div>
        <img src="spinner.gif">
    </div>
    <div id="successDiv" style="padding:10px; outline:10px solid black; display:none">
        Employee has been successfully updated.
    </div>
	<div id="employeeDiv" class="row" >
		<legend>Update Employee Salary for <?php echo htmlentities($emp->name); ?></legend>
		<form role="form" onsubmit="return editEmployeeSalary(this);">
            <input type="hidden" name="id" value="<?php echo htmlentities($employeeId); ?>"/>

<!--
            <div id="currentPayDiv">
                <div class="form-group">
                    <label class="control-label">Departments</label>
                    <p class="form-control" disabled><?php
                        $d = array_map(function ($dept) { return $dept->name; }, $depts);
                        echo htmlentities(implode(", ", $d));
                    ?></p>
                </div>
                <div class="form-group">
                    <label class="control-label">Number of Deductions</label>
                    <p class="form-control" disabled><?php
                        echo htmlentities($emp->current->numDeductions);
                    ?></p>
                </div>
                <div class="form-group">
                    <label class="control-label">Rank</label>
                    <p class="form-control" disabled><?php
                        $rank = $emp->current->rank;
                        echo htmlentities($rank->name) . ' ($'. number_format($rank->baseSalary, 2) .')';
                    ?></p>
                </div>
                <div class="form-group">
                    <label class="control-label">Yearly Salary</label>
                    <p class="form-control" disabled><?php
                        echo htmlentities($emp->current->salary);
                    ?></p>
                </div>
            </div>
-->
            <div id="updatePayDiv">
                <div class="form-group">
                    <label class="control-label">Salary Update Effective Date</label>
                    <div class="input-group">
                        <input data-provide="datepicker" class="form-control" type="text" name="startDate" id="startDate" placeholder="Enter effective date"/>

                        <span class="input-group-addon" glyphicon glyphicon-calendar><span class="glyphicon glyphicon-calendar"></span></span>
                    </div>
                </div>

                <div class="form-group">
                    <label class="control-label">Departments</label>
                    <select multiple class="form-control" name="departments[]" id="departments">
                        <?php
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
                    <label class="control-label">Number of Deductions</label>
                    <input type="text" class="form-control" name="numDeductions" id="numDeductions" placeholder="Enter a number" value="<?php echo htmlentities($emp->current->numDeductions); ?>">
                </div>
                <div class="form-group">
                    <label class="control-label">Rank</label>
                    <select class="form-control" name="rank" id="rank">
                        <?php
                            echo '<option disabled>Select One</option>';

                            $empRankId = $emp->current->rank->id;
                            foreach ($ranks as $rank) {
                                echo '<option value="'. htmlentities($rank->id) .'"';

                                if ($rank->id == $empRankId)
                                    echo 'selected';

                                echo ' rank-base-salary="'. htmlentities($rank->baseSalary) .'">'.
                                    htmlentities($rank->name) . ' ($'. number_format($rank->baseSalary, 2) .')'.
                                    '</option>';
                            } // foreach
                        ?>
                    </select>
                </div>
                <div class="form-group">
                    <label class="control-label">Yearly Salary</label>
                    <input type="text" class="form-control" name="salary" id="salary" placeholder="Enter a salary" value="<?php echo htmlentities($emp->current->salary); ?>">
                </div>
            </div>
            <button type="submit" class="btn btn-default">
                Update Salary
            </button>
			<br></br>
		</form>
	</div>
</div> 
