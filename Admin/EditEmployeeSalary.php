<?php
    require_once(dirname(__FILE__)."/../common.php");
    if (!isset($loginSession))
        doUnauthenticatedRedirect();

    if (!$loginSession->isAdministrator)
        doUnauthorizedRedirect();

    $employeeId = @$_GET['id'];
    $emp = null;

    try {
        $employeeId = (int) $employeeId;
        $emp = $db->readEmployee($employeeId);

        $history = $db->readEmployeeHistory($employeeId, null, null);

/*
// Retrieves managers for each department in the history
// TODO: For this to work well, will probably need to retrieve the assigned managers on save/update
        foreach ($history as $entry) {
            foreach ($entry->departments as $dept) {
                $effDate = $entry->endDate;
                if ($effDate == null) $effDate = new DateTime();
                $dept->managers = $db->readEmployeesForDepartment($dept->id, EmployeeType::Manager(), $effDate);
                $dept->managers = array_map(function($mgr) { return $mgr->name; }, $dept->managers);
            } // foreach
        } // foreach
*/

?>
<script>
    define(
        'EditEmployeeSalaryData',
        ['models/EmployeeHistoryCollection', 'models/RankCollection', 'models/DepartmentCollection'],
        function(EmployeeHistoryCollection, RankCollection, DepartmentCollection) {
            return {
                employeeId : <?= $employeeId ?>,
                history : new EmployeeHistoryCollection(<?= json_encode($history) ?>),
                ranks : new RankCollection(<?= json_encode($db->readRanks()) ?>),
                departments : new DepartmentCollection(<?= json_encode($db->readDepartments()) ?>)
            };
        });
</script>
<?php
    } catch (Exception $ex) {
        handleDBException($ex);
        return;
    }
?>
<div class="container">
    <div id="spinner" class="container" style="padding-bottom:10px;text-align:center">
        <div style="color:black;padding-bottom:32px;display:none">Updating Employee Salary...</div>
        <img src="spinner.gif">
    </div>
    <div id="successDiv" style="padding:10px; outline:10px solid black; display:none">
        Employee has been successfully updated.
    </div>
	<div id="employeeDiv" class="row" style="display:none">
		<legend>Update Employee Salary for <?php echo htmlentities($emp->name); ?></legend>

        <table id="EmployeeSalaryHistory" class="table table-striped table-bordered table-condensed table-hover"></table>
        <script>
            var views = {}; // In global scope for debugging/console access to the views

            function buildEmployeeSalaryHistory() {
                var EmployeeSalaryHistoryView = require("views/EmployeeSalaryHistoryView");
                var ModalDialogView = require("views/ModalDialogView");
                var EditEmployeeHistoryView = require("views/EditEmployeeHistoryView");
                var data = require("EditEmployeeSalaryData");

                views.editHistoryView = new EditEmployeeHistoryView({
                        employeeId : data.employeeId,
                        model : null,
                        departments : data.departments,
                        ranks : data.ranks
                    });

                    views.editHistoryView.on({
                            request : function(model, data, options) {
                                    // Don't allow user to dismiss the dialog during the request
                                    views.salaryHistoryModal.$(".btn-primary,.btn-default").attr("disabled", "disabled");
                                    views.salaryHistoryModal.$(".close").hide();
                                },
                            error : function(model, data, options) {
                                    console.log("Error!", model, data, options);

                                    showError(data.status +" "+ data.errorThrown);

                                    // Re-enable the dialog
                                    views.salaryHistoryModal.$(".btn-primary,.btn-default").removeAttr("disabled");
                                    views.salaryHistoryModal.$(".close").show();
                                },
                            sync : function(model, data, options) {
                                    console.log("Sync OK?", model, data, options);
                                    views.salaryHistoryModal.close();
                                    // Re-enable the dialog
                                    views.salaryHistoryModal.$(".btn-primary,.btn-default").removeAttr("disabled");
                                    views.salaryHistoryModal.$(".close").show();
                                },
                            change : function(view) {
                                    views.salaryHistoryModal.$(".btn-primary").removeAttr("disabled");
                                }
                        });

                views.salaryHistoryModal = new ModalDialogView({
                        title : "Edit Salary",
                        contentView : views.editHistoryView.render(),
                        events : {
                            "click .btn-primary" : function(e) {
                                    // TODO: Need to validate the form, save & close the dialog on success
                                    console.log("The primary button was clicked!");
                                    views.editHistoryView.save();
                                },
                            "hide.bs.modal" : function(e) {
                                    if (e.namespace != "bs.modal")
                                        return;

                                    if (views.editHistoryView.model.hasChanged()) {
                                        if (!confirm("Cancel changes and close dialog?"))
                                            e.preventDefault();
                                    }
                                },
                            "show.bs.modal" : function(e) {
                                    if (e.namespace != "bs.modal")
                                        return;
                                    views.salaryHistoryModal.$(".btn-primary").attr("disabled", "disabled");
                                }
                        }
                    });

                views.salaryHistory = new EmployeeSalaryHistoryView({
                        el : $("#EmployeeSalaryHistory"),
                        collection : data.history,
                        events : {
                            "click tr" : function(e) {
                                    var row = e.currentTarget;
                                    if (row.rowIndex == 0)
                                        return; // Ignore header row

                                    var id = row.getAttribute("history-id");
                                    var entry = data.history.get(id).clone();

                                    views.editHistoryView.setModel(entry);
                                    views.salaryHistoryModal.show();
                                }
                        }
                    }).render();
            }
        </script>

        <br></br>
	</div>
</div>
<script>

require([
    "views/DepartmentListView",
    "views/EmployeeSalaryHistoryView",
    "views/ModalDialogView",
    "views/EditEmployeeHistoryView",
    "EditEmployeeSalaryData"
], function() {
    registerBuildUI(function($) {
        buildEmployeeSalaryHistory();

        var spinner = $("#spinner");
        spinner.hide();
        $("div", spinner).css({ display : "block" });

        $("#employeeDiv").show();
    });
});

</script>
