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

    $payPeriod = htmlentities($payPeriodStartDate->format("F, Y"));
    $payPeriodStartDate = htmlentities($payPeriodStartDate->format("Y-m-d"));
    $payPeriodEndDate = htmlentities($payPeriodEndDate->format("Y-m-d"));
?>
<style type="text/css">
  .navigateButton {
    padding:5px 5px;
    font-weight: bold;
    cursor: pointer;
    border: 1px solid #ccc;
    border-radius: 5px;
  }

  .navigateButton:hover {
    color: red;
    background-color: #ccc;
    border: 1px solid black;
  }
</style>

<script>
function generatePaystubs(form) {
    $("#spinner").show();
    $("#content").hide();

    $.ajax({
        "type" : "POST",
        "url" : "Admin/doGeneratePayStubs.php",
        "data" : $(form).serialize(),
        "dataType" : "json"
        })
        .done(function(data) {
            $("#spinner").hide();

            if (data.error != null) {
                showError(data.error);
                $("#content").show();
            } else {
                $("#successDiv .message").text(data.message);
                $("#successDiv").show();
            }
        })
        .fail(function( jqXHR, textStatus, errorThrown ) {
            console.log("Error: "+ textStatus +" (errorThrown="+ errorThrown +")");
            console.log(jqXHR.textContent);

            $("#spinner").hide();
            $("#content").show();
            showError("Request failed, unable to generate paystubs: "+ errorThrown);
        })

    return false;
} // generatePaystubs

function previousMonth() {
    updatePayPeriodDisplay(-1);
} // previousMonth

function nextMonth() {
    updatePayPeriodDisplay(1);
} // nextMonth

function updatePayPeriodDisplay(addMonths) {
    var formElem = $('#generateForm input[name="payPeriodStartDate"]');

    var date = new Date(formElem.val());
    date = new Date(date.getUTCFullYear(), date.getUTCMonth() + addMonths, date.getUTCDate());

    var endDate = new Date(date.getUTCFullYear(), date.getUTCMonth() + 1, 0);

    $("#payPeriod").text(formatDate(date, "F, Y"));
    $("#payPeriodStartDate").text(formatDate(date, "Y-m-d"));
    $("#payPeriodEndDate").text(formatDate(endDate, "Y-m-d"));

    var numDays = endDate.getUTCDate() - date.getUTCDate() + 1;
    $("#payPeriodDuration").text(numDays);

    formElem.val(formatDate(date, "Y-m-d"));
} // updatePayPeriodDisplay
</script>

<div class="container col-md-6 col-md-offset-3">
    <div id="spinner" style="padding-bottom:10px;text-align:center;display:none">
        <div style="color:black;padding-bottom:32px;">Generating Pay Stubs for <?php echo $payPeriodStartDate .' to '. $payPeriodEndDate; ?>...</div>
        <img src="spinner.gif">
    </div>

    <div id="successDiv" style="padding:10px; outline:10px solid black; display:none">
        <span class="message"></span>
    </div>

    <div id="content">
        <form id="generateForm" class="form" onsubmit="return generatePaystubs(this)">
            <legend>Generate Pay Stubs</legend>

            <table class="table">
                <tr>
                    <th>Pay Period</th>
                    <td style="width:50%">
                        <span id="payPeriod"><?php echo $payPeriod; ?></span>
                        <span style="float:right">
                        <span class="navigateButton" onclick="previousMonth(event)">&lt;</span>
                        <span class="navigateButton" onclick="nextMonth(event)">&gt;</span>
                        </span>
                    </td>
                </tr>
                <tr>
                    <th>Pay Period Start Date</th>
                    <td><span id="payPeriodStartDate"><?php echo $payPeriodStartDate; ?></span></td>
                </tr>
                <tr>
                    <th>Pay Period End Date</th>
                    <td><span id="payPeriodEndDate"><?php echo $payPeriodEndDate; ?></span></td>
                </tr>
                <tr>
                    <th>Pay Period Duration</th>
                    <td><span id="payPeriodDuration"><?php echo $payPeriodDuration; ?></span></td>
                </tr>
            </table>

            <input type="hidden" name="payPeriodStartDate" value="<?php echo htmlentities($payPeriodStartDate); ?>"/>
            <button style="margin-top: 10px" type="submit" class="btn btn-default">Generate Paystubs</button>
        </form>
    </div>
</div>