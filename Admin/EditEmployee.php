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
            ?><script>define('EditEmployeeData', [], function() { return undefined; })</script><?php
        } else {
?>
<script>
    define(
        'EditEmployeeData',
        ['models/RankCollection', 'models/DepartmentCollection'],
        function(RankCollection, DepartmentCollection) {
            return {
                departments : new DepartmentCollection(<?= json_encode($db->readDepartments()) ?>),
                ranks : new RankCollection(<?= json_encode($db->readRanks()) ?>)
            };
        });
</script>
<?php
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
		var taxid = requiredField($(form.elements.taxid), "You must enter employee's tax id");
		var username = requiredField($(form.elements.username), "You must enter employee's username.");

<?php
    // Verify fields that are only asked for new employees
    if ($emp == null) {
?>
		var password1 = requiredField($(form.elements.password1), "You must enter employee's password.");
		var password2 = requiredField($(form.elements.password2), "You must verify employee's password.");

        var startDate = requiredField($(form.elements.startDate), "You must provide a starting date");
        startDate = (startDate != "" ? new Date(startDate) : "");

        var departments = views.departmentSelector.getSelectedValues();
        var elem = views.departmentSelector.$('div.form-control');
        if (departments.length == 0) {
            elem.tooltip("destroy")
            .addClass("error")
            .data("title", "You must select at least one department for employee")
            .tooltip();
        } else {
            elem.tooltip("destroy")
            .removeClass("error")
            .data("title", "");
        }

        var rankInput = form.elements.rank;
        var selRank = rankInput.options[rankInput.selectedIndex];
        var baseSalary = Number(selRank.getAttribute('rank-base-salary'));
        var rank = requiredField($(rankInput), "You must enter employee's rank");
        var numDeductions = requiredField($(form.elements.numDeductions), "You must enter employee's number of deductions");
        var salary = requiredField($(form.elements.salary), "You must enter employee's salary");

        if ((password1 == "") || (password2 == "")) {
			showError("You must enter all form information.");
            return false;
        }

        if (password1 != password2) {
            showError("The employee's password and verify password do not match.");
            return false;
        }

        if (password1.length < 8) {
            showError("The employee's password must be at least 8 characters long");
            return false;
        }

        if ((startDate == "") || (numDeductions == "") || (salary == "") || (rank == null) || (departments.length == 0)) {
            showError("You must enter all form information.");
            return false;
        }

        if (isNaN(numDeductions) || (numDeductions < 0)) {
            showError("Invalid value specified for number of deductions");
            return false;
        }
        numDeductions = Number(numDeductions);

        if (isNaN(salary) || (salary < 0)) {
            showError("Invalid value specified for salary");
            return false;
        }
        salary = Number(salary);

        if (salary < baseSalary) {
            showError("The salary cannot be less than the base salary assigned to the selected rank: "+ $(selRank).text());
            return false;
        }
<?php } ?>

		if ((name == "") || (address == "") || (taxid == "") || (username == "")) {
			showError("You must enter all form information.");
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
    <div id="spinner" class="col-md-2 col-md-offset-5" style="padding-bottom:10px;text-align:center">
        <div style="color:black;padding-bottom:32px;display:none"><?php
            if ($emp != null)
                echo 'Updating Employee...';
            else
                echo 'Adding Employee...';
        ?></div>
        <img src="spinner.gif">
    </div>
    <div id="successDiv" style="padding:10px; outline:10px solid black; display:none">
        Employee has been successfully <?php echo ($emp == null) ? 'added' : 'updated'; ?>.
    </div>
	<div id="employeeDiv" class="row" style="display:none">
		<legend><?php
            if ($emp != null)
                echo 'Update Employee';
            else
                echo 'Add Employee';
        ?></legend>
		<form role="form" onsubmit="return editEmployee(this);">
            <input type="hidden" name="id" value="<?php echo htmlentities($employeeId); ?>"/>

            <div class="form-group">
                <label class="control-label">Name</label>
                <input type="text" class="form-control" name="name" id="name" placeholder="Enter name" value="<?php empProperty($emp, 'name'); ?>"/>
            </div>
            <div class="form-group">
                <label class="control-label">Address</label></br>
                <textarea class="form-control" rows="5" name="address" id="address" placeholder="Enter Address"><?php empProperty($emp, 'address'); ?></textarea>
            </div>
            <div class="form-group">
                <label class="control-label">Tax ID</label>
                <input type="text" class="form-control" name="taxid" id="taxid" placeholder="Enter Soc Sec #" value="<?php empProperty($emp, 'taxId'); ?>"/>
            </div>
            <hr/>
            <div class="form-group">
                <label class="control-label">Username</label>
                <input type="text" class="form-control" name="username" id="username" placeholder="Enter Username" value="<?php empProperty($emp, 'username'); ?>"/>
            </div>
<?php if ($emp == null) { ?>
            <div class="form-group">
                <label class="control-label">Password</label>
                <input type="password" class="form-control" name="password1" id="password1" placeholder="Enter password"/>
            </div>
            <div class="form-group">
                <label class="control-label">Verify Password</label>
                <input type="password" class="form-control" name="password2" id="password2" placeholder="Verify password"/>
            </div>
            <hr/>
            <div class="form-group">
                <label class="control-label">Start Date</label>
                <div class="input-group">
                    <input data-provide="datepicker" data-date-autoclose="true" class="form-control" type="text" name="startDate" id="startDate" placeholder="Enter employment start date" />
                    <span class="input-group-addon" glyphicon glyphicon-calendar><span class="glyphicon glyphicon-calendar"></span></span>
                </div>
            </div>
            <div class="form-group">
                <label class="control-label">Departments</label>
                <div class="departmentSelector"></div>
            </div>
            <div class="form-group">
                <label class="control-label">Number of Deductions</label>
                <input type="text" class="form-control" name="numDeductions" id="numDeductions" placeholder="Enter a number" />
            </div>
            <div class="form-group">
                <label class="control-label">Rank</label>
                <div class="rankSelector"></div>
            </div>
            <div class="form-group">
                <label class="control-label">Yearly Salary</label>
                <input type="text" class="form-control" name="salary" id="salary" placeholder="Enter a salary" />
            </div>
<?php } ?>
            <button type="submit" class="btn btn-default"><?php
                if ($emp != null)
                    echo 'Update Employee';
                else
                    echo 'Add Employee';
            ?></button>
            <br></br>
		</form>
	</div>
</div>
<script>

var views = {};

require(["main"], function() {
    require([
        "views/DepartmentSelectorView",
        "views/RankSelectorView",
        "EditEmployeeData",
        "bootstrap-datepicker"
    ], function(DepartmentSelectorView, RankSelectorView, data) {
        registerBuildUI(function($) {
            // Create UI elements if needed
            var $depts = $(".departmentSelector");
            if ($depts.length) {
                views.departmentSelector = new DepartmentSelectorView({
                                el : $depts,
                                collection : data.departments,
                                name : "departments"
                            }).render();

                views.rankSelector = new RankSelectorView({
                        el : $(".rankSelector"),
                        name : "rank",
                        collection : data.ranks
                    }).render();
            }

            // Init date picker and display UI
            $('[data-provide="datepicker"]').datepicker();

            var spinner = $("#spinner");
            spinner.hide();
            $("div", spinner).css({ display : "block" });

            $("#employeeDiv").show();
        });
    });
});

</script>
