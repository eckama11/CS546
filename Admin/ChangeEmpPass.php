<?php

// TODO: Don't allow the currently logged in user to change their own password with this page...
//       They should use the Change Own Password instead.

    require_once(dirname(__FILE__)."/../common.php");

    if (!isset($loginSession))
        doUnauthenticatedRedirect();

    $employeeId = @$_GET['id'];

    if (!$loginSession->isAdministrator || ($loginSession->authenticatedEmployee->id == $employeeId))
        doUnauthorizedRedirect();

    try {
        $emp = $db->readEmployee($employeeId);

        if (!$emp->activeFlag)
            throw new Exception("Inactive employees cannot be updated.");
    } catch (Exception $ex) {
        handleDBException($ex);
        return;
    }
?>
<script>
function updatePassword(form) {
    var newPassword1 = requiredField($(form.elements.newPassword1), "You must enter a new password");
    var newPassword2 = requiredField($(form.elements.newPassword2), "You must verify your new password");

    if (newPassword1 != newPassword2) {
        showError("The new password and verify password do not match.");
        return false;
    }

    if (newPassword1.length < 8) {
        showError("The new password must be at least 8 characters long");
        return false;
    }

    $("#spinner").show();
    $("#content").hide();

    $.ajax({
        "type" : "POST",
        "url" : "Admin/doChangeEmployeePassword.php",
        "data" : $(form).serialize(),
        "dataType" : "json"
        })
        .done(function(data) {
            $("#spinner").hide();

            if (data.error != null) {
                showError(data.error);
                $("#content").show();
            } else
                $("#successDiv").show();
        })
        .fail(function( jqXHR, textStatus, errorThrown ) {
            console.log("Error: "+ textStatus +" (errorThrown="+ errorThrown +")");
            console.log(jqXHR.textContent);

            $("#spinner").hide();
            $("#content").show();
            showError("Request failed, unable to change password: "+ errorThrown);
        })

    return false;
} // updatePassword(form)
</script>

<div class="container col-md-6 col-md-offset-3">
    <div id="spinner" style="padding-bottom:10px;text-align:center;display:none">
        <div style="color:black;padding-bottom:32px;">Updating Employee Password...</div>
        <img src="spinner.gif">
    </div>

    <div id="successDiv" style="padding:10px; outline:10px solid black; display:none">
        Employee password has been successfully updated.
    </div>

	<div id="content">
        <legend>Change password for <?php echo htmlentities($emp->name); ?></legend>
        <form role="form" class="form-horizontal" onsubmit="return updatePassword(this);">
            <input type="hidden" name="id" value="<?php echo htmlentities($emp->id); ?>"/>

            <div class="form-group">
                <label class="col-sm-2 control-label">Username</label>
                <div class="col-sm-10">
                    <p class="form-control-static"><?php echo htmlentities($emp->username); ?></p>
                </div>
            </div>

            <div class="form-group">
                <label class="col-sm-2 control-label" for="newPassword1">New Password</label>
                <div class="col-sm-10">
                    <input type="password" class="form-control" name="newPassword1" id="newPassword1" placeholder="Enter new password"/>
                </div>
            </div>

            <div class="form-group">
                <label class="col-sm-2 control-label" for="newPassword2">Verify Password</label>
                <div class="col-sm-10">
                    <input type="password" class="form-control" name="newPassword2" id="newPassword2" placeholder="Verify new password"/>
                </div>
            </div>

            <button style="margin-top: 10px" type="submit" class="btn btn-default">Submit</button>
        </form>
    </div>
</div>