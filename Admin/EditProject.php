<?php
    require_once(dirname(__FILE__)."/../common.php");
    if (!isset($loginSession))
        doUnauthenticatedRedirect();

    if (!$loginSession->isAdministrator)
        doUnauthorizedRedirect();

    $projectId = @$_GET['id'];
    $project = null;

    try {
        if ($projectId != null) {
            $projectId = (int) $projectId;
            $project = $db->readProject($projectId);

            $projectDepts = $db->readDepartmentsForProject($project->id);
            $projectDepts = array_map(function($dept) { return $dept->id; }, $projectDepts);
        } else
            $projectDepts = [];
?>
<script>
    define(
        'EditProjectData',
        ['models/DepartmentCollection'],
        function(DepartmentCollection) {
            return {
                departments : new DepartmentCollection(<?= json_encode($db->readDepartments()) ?>),
                projectDepartments : <?= json_encode($projectDepts) ?>
            };
        });
</script>
<?php
    } catch (Exception $ex) {
        handleDBException($ex);
        return;
    }
?>

<script>
	function editProject(form) {
		var name = requiredField($(form.elements.name), "You must enter project's name");
        var description = requiredField($(form.elements.description), "You must enter a description for the project");
		var startDate = requiredField($(form.elements.startDate), "You must enter a starting date");
		var endDate = requiredField($(form.elements.endDate), "You must enter an end date");
		var otherCosts = requiredField($(form.elements.otherCosts), "You must enter the other montly costs");

        var departments = views.departmentSelector.getSelectedValues();
        var elem = views.departmentSelector.$('div.form-control');
        if (departments.length == 0) {
            elem.tooltip("destroy")
            .addClass("error")
            .data("title", "You must select at least one department for project")
            .tooltip();
        } else {
            elem.tooltip("destroy")
            .removeClass("error")
            .data("title", "");
        }

        if ((name == "") || (startDate == "") || (endDate == "") || (otherCosts == "") || (departments.length == 0)) {
            showError("You must enter all form information.");
            return false;
        }

        if (isNaN(otherCosts) || (otherCosts < 0)) {
            showError("Invalid value specified for other monthly costs");
            return false;
        }

        startDate = new Date(startDate);
        endDate = new Date(endDate);
        if (endDate && (startDate > endDate)) {
            showError("The start date cannot be after the end date");
            return false;
        }

		$("#projectDiv").hide();
		$("#spinner").show();

		$.ajax({
			"type" : "POST",
			"url" : "Admin/doEditProject.php",
			"data" : $(form).serialize(),
			"dataType" : "json"
			})
			.done(function(data) {
				$("#spinner").hide();

				if (data.error != null) {
					showError(data.error);
					$("#projectDiv").show();
				} else
					$("#successDiv").show();
			})
			.fail(function( jqXHR, textStatus, errorThrown ) {
				console.log("Error: "+ textStatus +" (errorThrown="+ errorThrown +")");
				console.log(jqXHR.textContent);

                $("#spinner").hide();
                $("#projectDiv").show();
                showError("Request failed, unable to update project: "+ errorThrown);
			})

		return false;
    } // editProject
</script>

<div class="container col-md-6 col-md-offset-3">
    <div id="spinner" class="col-md-2 col-md-offset-5" style="padding-bottom:10px;text-align:center">
        <div style="color:black;padding-bottom:32px;;display:none"><?php
            if ($project != null)
                echo 'Updating Project...';
            else
                echo 'Adding Project...';
        ?></div>
        <img src="spinner.gif">
    </div>
    <div id="successDiv" style="padding:10px; outline:10px solid black; display:none">
        Project has been successfully <?php echo ($project == null) ? 'added' : 'updated'; ?>.
    </div>
	<div id="projectDiv" class="row" style="display:none">
		<legend><?php
            if ($project != null)
                echo 'Update Project';
            else
                echo 'Add Project';
        ?></legend>
		<form role="form" onsubmit="return editProject(this);">
            <input type="hidden" name="id" value="<?php echo htmlentities($projectId); ?>"/>

            <div class="form-group">
                <label class="control-label">Name</label>
                <input type="text" class="form-control" name="name" id="name" placeholder="Enter project name" value="<?php echo htmlentities(@$project->name); ?>"/>
            </div>

            <div class="form-group">
                <label class="control-label">Description</label>
                <input type="text" class="form-control" name="description" id="description" placeholder="Enter project description" value="<?php echo htmlentities(@$project->description); ?>"/>
            </div>

            <div class="form-group">
                <label class="control-label">Project Duration</label>
                <div class="input-group">
                    <input data-provide="datepicker" class="form-control" type="text" name="startDate" id="startDate" placeholder="Enter project start date" value="<?php echo htmlentities($project ? $project->startDate->format("m/d/Y") : null); ?>"/>
                    <span class="input-group-addon">to</span>
                    <input data-provide="datepicker" class="form-control" type="text" name="endDate" id="endDate" placeholder="Enter project end date" value="<?php echo htmlentities($project ? $project->endDate->format("m/d/Y") : null); ?>"/>
                </div>
            </div>

            <div class="form-group">
                <label class="control-label">Other Monthly Costs</label>
                <input type="text" class="form-control" name="otherCosts" id="otherCosts" placeholder="Enter other costs" value="<?php echo htmlentities(@$project->otherCosts); ?>"/>
            </div>

            <div class="form-group">
                <label class="control-label">Departments</label>
                <div class="departmentSelector"></div>
            </div>

            <button type="submit" class="btn btn-default"><?php
                if ($project != null)
                    echo 'Update Project';
                else
                    echo 'Add Project';
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
        "EditProjectData",
        "bootstrap-datepicker"
    ], function(DepartmentSelectorView, data) {
        registerBuildUI(function($) {
            views.departmentSelector = new DepartmentSelectorView({
                            el : $(".departmentSelector"),
                            collection : data.departments,
                            selectedValues : data.projectDepartments,
                            name : "departments"
                        }).render();

            // Init date pickers
            $('[data-provide="datepicker"]').datepicker();

            var spinner = $("#spinner");
            spinner.hide();
            $("div", spinner).css({ display : "block" });

            $("#projectDiv").show();
        });
    });
});

</script>