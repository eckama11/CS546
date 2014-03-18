<?php
    require_once(dirname(__FILE__)."/../common.php");
    if (!isset($loginSession))
        doUnauthenticatedRedirect();
    if (!$loginSession->isAdministrator)
        doUnauthorizedRedirect();
    $employeeId = @$_GET['id'];
    try {
        $emp = $db->readEmployee($employeeId);
    } catch (Exception $ex) {
        handleDBException($ex);
        return;
    }
?>

<script>
function activate(form) {
    var employeeId = requiredField($(form.elements.employeeId), "You must enter an employee ID.");
    if ((employeeId == "")) {
        showError("You must enter an employee ID to change employee status.");
        return false;
    }
    
    $("#activeDiv").hide();
    $("#spinner").show();

    $.ajax({
        "type" : "POST",
        "url" : "Admin/doActivation.php",
        "data" : $(form).serialize(),
        "dataType" : "json"
        })
        .done(function(data) {
            $("#spinner").hide();

            if (data.error != null) {
                showError(data.error);
                $("#activeDiv").show();
            } else
                $("#successDiv").show();
        })
        .fail(function( jqXHR, textStatus, errorThrown ) {
            console.log("Error: "+ textStatus +" (errorThrown="+ errorThrown +")");
            console.log(jqXHR.textContent);
        })

    return false;
}
</script>

<div class="container col-md-6 col-md-offset-3">
	<div id="spinner" style="padding-bottom:10px;text-align:center;display:none">
        <div style="color:black;padding-bottom:32px;">Updating Employee Status...</div>
        <img src="spinner.gif">
    </div>

    <div id="successDiv" class="col-md-6 col-md-offset-3" style="padding-bottom:10px; outline: 10px solid black;display:none">
        Employee status has been successfully updated.
    </div>

	<div id="content">
        <legend>Change password for <?php echo htmlentities($emp->name); ?></legend>
        <form role="form" class="form-horizontal" onsubmit="return activate(this);">
            <input type="hidden" name="id" value="<?php echo htmlentities($emp->id); ?>"/>
			<div class="form-group">
                <label class="col-sm-2 control-label">Username</label>
                <div class="col-sm-10">
                    <p class="form-control-static"><?php echo htmlentities($loginSession->authenticatedEmployee->username); ?></p>
                </div>
            </div>
            <div class="form-group">
                <label class="col-sm-2 control-label" for="curStatus">Status</label>
                <div class="col-sm-10">
                    <input type="checkbox" class="form-control" name="active" id="active" value="active">Activate</input>
                    <input type="checkbox" class="form-control" name="deactive" id="deactive" value="deactive">Deactivate</input>
                </div>
            </div>
            <button type="submit" class="btn btn-default">Submit</button>
        </form>
    </div>
</div>