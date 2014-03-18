<?php
    require_once(dirname(__FILE__)."/../common.php");
    if (!isset($loginSession))
        doUnauthenticatedRedirect();
?>
<script>
function changePassword(form) {
    var currentPassword = requiredField($(form.elements.currentPassword), "You must enter your current password");
    var newPassword1 = requiredField($(form.elements.newPassword1), "You must enter a new password");
    var newPassword2 = requiredField($(form.elements.newPassword2), "You must verify your new password");
    if ((currentPassword == "") || (newPassword1 == "") || (newPassword2 == "")) {
        showError("You must enter your current password and the new password you wish to use.");
        return false;
    }

    if (newPassword1 != newPassword2) {
        showError("The new password and verify password do not match.");
        return false;
    }

    if (newPassword1.length < 8) {
        showError("The new password must be at least 8 characters long");
        return false;
    }

    $("#content").hide();
    $("#spinner").show();

    $.ajax({
        "type" : "POST",
        "url" : "Employee/doChangePassword.php",
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
}
</script>
<div class="container col-md-6 col-md-offset-3">
    <div id="spinner" style="padding-bottom:10px;text-align:center;display:none">
        <div style="color:black;padding-bottom:32px;">Updating your password...</div>
        <img src="spinner.gif">
    </div>

    <div id="successDiv" class="col-md-6 col-md-offset-3" style="padding-bottom:10px; outline: 10px solid black;display:none">
    Your password has been successfully updated
    </div>

	<div id="content">
        <legend>Change password for <?php echo htmlentities($loginSession->authenticatedEmployee->name); ?></legend>
        <form role="form" class="form-horizontal" onsubmit="return changePassword(this)">
            <div class="form-group">
                <label class="col-sm-2 control-label">Username</label>
                <div class="col-sm-10">
                    <p class="form-control-static"><?php echo htmlentities($loginSession->authenticatedEmployee->username); ?></p>
                </div>
            </div>

            <div class="form-group">
                <label class="col-sm-2 control-label" for="currentPassword">Current Password</label>
                <div class="col-sm-10">
                    <input type="password" class="form-control" name="currentPassword" id="currentPassword" placeholder="Enter current password"/>
                </div>
            </div>

            <div class="form-group">
                <label class="col-sm-2 control-label" for="newPassword1">New Password</label>
                <div class="col-sm-10">
                    <input type="password" class="form-control" name="newPassword1" id="newPassword1" placeholder="Enter new password"/>
                </div>
            </div>

            <div class="form-group">
                <label class="col-sm-2 control-label" for="newPassword1">Verify Password</label>
                <div class="col-sm-10">
                    <input type="password" class="form-control" name="newPassword2" id="newPassword2" placeholder="Verify new password"/>
                </div>
            </div>

            <button type="submit" class="btn btn-default">Submit</button>
        </form>
    </div>
</div>