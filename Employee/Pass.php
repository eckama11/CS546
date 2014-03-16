<?php
    require_once(dirname(__FILE__)."/../common.php");
    if (!isset($loginSession))
        doUnauthenticatedRedirect();

    // If the form was posted, verify the old password and update the password if the 2 new passwords match and are acceptable
    if (array_key_exists('currentPassword', $_POST)) {
        $currentPassword = $_POST['currentPassword'];
        $newPassword1 = @$_POST['newPassword1'];
        $newPassword2 = @$_POST['newPassword2'];

        $rv = (Object)[];
        try {
            // Verify the current password is a match
            if ($loginSession->employee->password != $currentPassword)
                throw new Exception("The current password was not correct");

            if ($newPassword1 != $newPassword2)
                throw new Exception("The new password and verify password do not match");

//            $loginSession->employee->password 

            $rv->success = true;
        } catch (Exception $ex) {
            $rv->error = $ex->getMessage();
        } // try/catch

        json_encode($rv);
        exit;
    }
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

    $("#passwordDiv").hide();
    $("#spinner").show();

    $.ajax({
        "type" : "POST",
        "url" : "Pass.php",
        "data" : $(form).serialize(),
        "dataType" : "json"
        })
        .done(function(data) {
            $("#spinner").hide();
            $("#passwordDiv").show();

            if (data.error != null)
                showError(data.error);
            else
                form.reset();
        })
        .fail(function( jqXHR, textStatus, errorThrown ) {
            console.log("Error: "+ textStatus +" (errorThrown="+ errorThrown +")");
            console.log(jqXHR);
        })

    return false;
}
</script>
<div class="container padded">
    <div class="row" >
        <div id="spinner" class="col-md-2 col-md-offset-5" style="padding-bottom:10px;text-align:center;display:none">
            <div style="color:black;padding-bottom:32px;">Updating your password...</div>
            <img src="spinner.gif">
        </div>
        <div id="passwordDiv" class="col-md-3 col-md-offset-5" style="padding-bottom:10px; outline: 10px solid black;">
            <form role="form" onsubmit="return changePassword(this)">
                <div class="form-group">
                    <label for="currentPassword">Current Password</label>
                    <input type="password" class="form-control" name="currentPassword" id="currentPassword" placeholder="Enter current password"/>
                </div>

                <div class="form-group">
                    <label for="newPassword1">New Password</label>
                    <input type="password" class="form-control" name="newPassword1" id="newPassword1" placeholder="Enter new password"/>
                </div>

                <div class="form-group">
                    <label for="newPassword1">Verify New Password</label>
                    <input type="password" class="form-control" name="newPassword2" id="newPassword2" placeholder="Enter new password"/>
                </div>

                <button type="submit" class="btn btn-default">Submit</button>
            </form>
        </div>
    </div>
</div>