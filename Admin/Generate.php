<?php
    require_once(dirname(__FILE__)."/../common.php");
    if (!isset($loginSession))
        doUnauthenticatedRedirect();
    if (!$loginSession->isAdministrator)
        doUnauthorizedRedirect();

    $currentDate = new DateTime();
    $currentDate->setTimezone(new DateTimeZone('GMT'));

    $payPeriodStartDate = new DateTime( $currentDate->format("Y-m-01T00:00:00P") );
    $payPeriodEndDate = (clone $payPeriodStartDate);
    $payPeriodEndDate->add(new DateInterval('P1M'))->sub(new DateInterval('P1D'));

    $payPeriodDuration = $payPeriodEndDate->diff($payPeriodStartDate)->format("%a") + 1;

    $payPeriodStartDate = htmlentities($payPeriodStartDate->format("Y-m-d"));
    $payPeriodEndDate = htmlentities($payPeriodEndDate->format("Y-m-d"));
?>
<script>
function generatePaystubs() {
    $("#spinner").show();
    $("#content").hide();

    $.ajax({
        "type" : "POST",
        "url" : "Admin/doGeneratePayStubs.php",
        "data" : {},
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
            showError("Request failed, unable to generate paystubs: "+ errorThrown);
        })
} // generatePaystubs
</script>

<div class="container col-md-6 col-md-offset-3">
    <div id="spinner" style="padding-bottom:10px;text-align:center;display:none">
        <div style="color:black;padding-bottom:32px;">Generating Pay Stubs for <?php echo $payPeriodStartDate .' to '. $payPeriodEndDate; ?>...</div>
        <img src="spinner.gif">
    </div>

    <div id="successDiv" class="col-md-6 col-md-offset-3" style="padding-bottom:10px; outline: 10px solid black;display:none">
        Pay stubs successfully generated for <?php echo $payPeriodStartDate .' to '. $payPeriodEndDate; ?>
    </div>

    <div id="content">
        <legend>Generate Pay Stubs</legend>

        <table class="table">
            <tr>
                <th>Pay Period Start Date</th>
                <td><?php echo $payPeriodStartDate; ?></td>
            </tr>
            <tr>
                <th>Pay Period End Date</th>
                <td><?php echo $payPeriodEndDate; ?></td>
            </tr>
            <tr>
                <th>Pay Period Duration</th>
                <td><?php echo $payPeriodDuration; ?></td>
            </tr>
        </table>

        <button style="margin-top: 10px" type="submit" class="btn btn-default" onclick="generatePaystubs()">Generate Paystubs</button>
    </div>
</div>