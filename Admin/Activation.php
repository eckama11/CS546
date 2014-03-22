<?php
    require_once(dirname(__FILE__)."/../common.php");
    require_once(dirname(__FILE__)."/../Employee/EmployeeInfo.php");

    if (!isset($loginSession))
        doUnauthenticatedRedirect();

	$employeeId = @$_GET['id'];

    if (!$loginSession->isAdministrator || ($loginSession->authenticatedEmployee->id == $employeeId))
        doUnauthorizedRedirect();

    try {
        $emp = $db->readEmployee($employeeId);
    } catch (Exception $ex) {
        handleDBException($ex);
        return;
    }

    $newStatus = ($emp->activeFlag ? "inactive" : "active");
?>

<script>
function activate(form) {
    if (!form.elements.confirm.checked) {
        showError("You must confirm the status change");
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

            $("#spinner").hide();
            $("#activeDiv").show();
            showError("Request failed, unable to change employee status: "+ errorThrown);
        })

    return false;
}

function confirmChanged(checkbox) {
    var submit = document.getElementById("submit");
    submit.disabled = !checkbox.checked;
}
</script>

<div class="container col-md-6 col-md-offset-3">
	<div id="spinner" style="padding-bottom:10px;text-align:center;display:none">
        <div style="color:black;padding-bottom:32px;">Updating Employee Status...</div>
        <img src="spinner.gif">
    </div>

    <div id="successDiv" style="padding:10px; outline:10px solid black; display:none">
        Employee status has been successfully updated.
    </div>

	<div id="activeDiv">
        <legend>Change status for <?php echo htmlentities($emp->name); ?></legend>

        <?php showEmployeeInfo( $emp ); ?>

        <form role="form" class="form-horizontal" onsubmit="return activate(this);">
            <input type="hidden" name="id" value="<?php echo htmlentities($emp->id); ?>"/>
            <input type="hidden" name="status" value="<?php echo ($emp->activeFlag ? 0 : 1); ?>"/>
            <div>
                <label><input type="checkbox" name="confirm" onchange="confirmChanged(this)"> I confirm that I wish to make this employee <?php echo $newStatus; ?></label>
            </div>
            <button type="submit" class="btn btn-default" id="submit" disabled>Make <?php echo ucfirst($newStatus); ?></button>
        </form>
    </div>
</div>