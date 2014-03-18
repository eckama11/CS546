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
    //var employeeId = requiredField($(form.elements.employeeId), "You must enter an employee ID.");
    //if ((employeeId == "")) {
        //showError("You must enter an employee ID to change employee status.");
        //return false;
    //}
    
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

	<div id="content">
    	<legend>Change password for <?php echo htmlentities($emp->name); ?></legend>
        <div id="spinner" class="col-md-2 col-md-offset-5" style="padding-bottom:10px;text-align:center;display:none">
            <div style="color:black;padding-bottom:32px;">Updating employee status...</div>
            <img src="spinner.gif">
        </div>
        <div id="successDiv" class="col-md-3 col-md-offset-5" style="padding-bottom:10px; outline: 10px solid black;display:none">
        Employee has been updated
        </div>
        <legend>Change password for <?php echo htmlentities($emp->name); ?></legend>
        <form role="form" class="form-horizontal" onsubmit="return activate(this);">
            <div class="form-group">
                <label class="col-sm-2 control-label">Employee ID</label>
                <div class="col-sm-10">
                	<input type="text" class="form-control" name="employeeId" id="employeeId" placeholder="Enter employee ID"/>
                </div>
            </div>
            <div class="form-group">
                <label class="col-sm-2 control-label" for="currentPassword">Status</label>
                <div class="col-sm-10">
                    <input type="checkbox" class="form-control" name="status" id="active" value="active">Activate</input>
                    <input type="checkbox" class="form-control" name="status" id="deactive" value="deactive">Deactivate</input>
                </div>
            </div>
            <button type="submit" class="btn btn-default">Submit</button>
        </form>
    </div>
</div>