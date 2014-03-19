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
    
    $status = ($emp->activeFlag);
    if($status == true) {
    	$status = "Active";
    }
    else {
    	$status = "Inactive";
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

<div class="container col-md-4 col-md-offset-4">
	<div id="spinner" style="padding-bottom:10px;text-align:center;display:none">
        <div style="color:black;padding-bottom:32px;">Updating Employee Status...</div>
        <img src="spinner.gif">
    </div>

    <div id="successDiv" class="col-md-6 col-md-offset-3" style="padding-bottom:10px; outline: 10px solid black;display:none">
        Employee status has been successfully updated.
    </div>

	<div id="activeDiv">
        <legend>Change status for <?php echo htmlentities($emp->name); ?></legend>
        <form role="form" class="form-horizontal" onsubmit="return activate(this);">
            <input type="hidden" name="id" value="<?php echo htmlentities($emp->id); ?>"/>
			<div class="form-group">
                <label class="col-sm-2 control-label">Username</label>
                <div class="col-sm-10">
                    <p class="form-control-static"><?php echo htmlentities($emp->username); ?></p>
                </div>
                <label class="col-sm-2 control-label">Status</label>
                <div class="col-sm-10">
                    <p class="form-control-static"><?php echo $status; ?></p>
                </div>
            </div>
            <div class="form-group">
                <label class="col-sm-2 control-label" for="curStatus">Change</label>
                <div class="col-sm-10">
                	<form action="">
						<input type="radio"  name="status" id="1" value="1">	Activate</input>
						<br></br>
						<input type="radio"  name="status" id="0" value="0">	Deactivate</input>
					</from>				
				</div>
            </div>
            <button type="submit" class="btn btn-default">Submit</button>
        </form>
    </div>
</div>