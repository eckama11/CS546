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
            $projectDepts = array_flip(array_map(function($dept) { return $dept->id; }, $projectDepts));
        } else
            $projectDepts = [];

        $depts = $db->readDepartments();
    } catch (Exception $ex) {
        handleDBException($ex);
        return;
    }
?>

<script>
	function editProject(form) {
		var name = requiredField($(form.elements.name), "You must enter project's name.");

// TODO: Additional validation here...

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
    <div id="spinner" class="col-md-2 col-md-offset-5" style="padding-bottom:10px;text-align:center;display:none">
        <div style="color:black;padding-bottom:32px;"><?php
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
	<div id="projectDiv" class="row" >
		<legend><?php
            if ($project != null)
                echo 'Update Project';
            else
                echo 'Add Project';
        ?></legend>
		<form role="form" onsubmit="return editEmployee(this);">
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
                    <input data-provide="datepicker" class="form-control" type="text" name="startDate" id="startDate" placeholder="Enter project start date" value="<?php echo htmlentities($project ? $project->startDate->format("Y-m-d") : null); ?>"/>
                    <span class="input-group-addon">to</span>
                    <input data-provide="datepicker" class="form-control" type="text" name="endDate" id="endDate" placeholder="Enter project end date" value="<?php echo htmlentities($project ? $project->endDate->format("Y-m-d") : null); ?>"/>
                </div>
            </div>

<!--
            <div class="form-group">
                <label class="control-label">End Date</label>
                <div class="input-group">
                    <input data-provide="datepicker" class="form-control" type="text" name="endDate" id="endDate" placeholder="Enter project end date" value="<?php echo htmlentities($project ? $project->endDate->format("Y-m-d") : null); ?>"/>
                    <span class="input-group-addon" glyphicon glyphicon-calendar><span class="glyphicon glyphicon-calendar"></span></span>
                </div>
            </div>
-->

            <div class="form-group">
                <label class="control-label">Other Monthly Costs</label>
                <input type="text" class="form-control" name="otherCosts" id="otherCosts" placeholder="Enter other costs" value="<?php echo htmlentities(@$project->otherCosts); ?>"/>
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