<?php
    require_once(dirname(__FILE__)."/../common.php");
    if (!isset($loginSession))
        doUnauthenticatedRedirect();

    if (!$loginSession->isAdministrator)
        doUnauthorizedRedirect();

    $projectId = @$_GET['id'];
    $project = null;

    try {
        $projectId = (int) $projectId;
        $project = $db->readProject($projectId);

        $departments = $db->readDepartmentsForProject($projectId);
        $employees = $db->readProjectEmployeeAssociations($project);

?>
<script>
    define(
        'EditeditEmployeeViewData',
        ['models/EmployeeCollection', 'models/DepartmentCollection'],
        function(EmployeeCollection, DepartmentCollection) {
            return {
                projectId : <?= $projectId ?>,
                employees : new EmployeeCollection(<?= json_encode($employees) ?>),
                departments : new DepartmentCollection(<?= json_encode($departments) ?>)
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
        <div style="color:black;padding-bottom:32px;display:none">Updating Project Employees...</div>
        <img src="spinner.gif">
    </div>
    <div id="successDiv" style="padding:10px; outline:10px solid black; display:none">
        Project has been successfully updated.
    </div>
	<div id="employeeDiv" class="row" style="display:none">
		<legend>Update Employees for <?php echo htmlentities($project->name); ?></legend>

        <table id="editEmployeeView" class="table table-striped table-bordered table-condensed table-hover"></table>
        
        <button type="button" class="btn btn-default" onclick="addEmployeeEntry()">Add</button>
        
        <script>
            var views = {}; // In global scope for debugging/console access to the views

            function buildEditEmployeeView() {
                var ProjectEmployeeView = require("views/ProjectEmployeeView");
                var ModalDialogView = require("views/ModalDialogView");
                var EditProjectEmployeeView = require("views/EditProjectEmployeeView");
                var data = require("EditeditEmployeeViewData");

                views.editEmployeeView = new EditProjectEmployeeView({
                        projectId : data.projectId,
                        model : null,
                        departments : data.departments
                    });

                    views.editEmployeeView.on({
                            request : function(model, data, options) {
                                    // Don't allow user to dismiss the dialog during the request
                                    views.editEmployeeViewModal.$(".btn-primary,.btn-default").attr("disabled", "disabled");
                                    views.editEmployeeViewModal.$(".close").hide();
                                },
                            error : function(model, data, options) {
                                    console.log("Error!", model, data, options);

                                    showError(data.error);

                                    // Re-enable the dialog
                                    views.editEmployeeViewModal.$(".btn-primary,.btn-default").removeAttr("disabled");
                                    views.editEmployeeViewModal.$(".close").show();
                                },
                            sync : function(model, data, options) {
                                    // Re-enable the dialog
                                    views.editEmployeeViewModal.$(".btn-primary,.btn-default").removeAttr("disabled");
                                    views.editEmployeeViewModal.$(".close").show();

                                    // Close the dialog and refresh the list of employees
                                    views.editEmployeeViewModal.close();

                                    views.salaryHistory.collection.add(model, {merge:true});
                                    views.salaryHistory.collection.sort();
                                    views.salaryHistory.render();
                                },
                            change : function(view) {
                                    views.editEmployeeViewModal.$(".btn-primary").removeAttr("disabled");
                                }
                        });

                views.editEmployeeViewModal = new ModalDialogView({
                        title : "Edit Employee Assignment",
                        contentView : views.editEmployeeView.render(),
                        events : {
                            "click .btn-primary" : function(e) {
                                    views.editEmployeeView.save();
                                },
                            "hide.bs.modal" : function(e) {
                                    if (e.namespace != "bs.modal")
                                        return;

                                    if (views.editEmployeeView.model.hasChanged()) {
                                        if (!confirm("Cancel changes and close dialog?"))
                                            e.preventDefault();
                                    }
                                },
                            "show.bs.modal" : function(e) {
                                    if (e.namespace != "bs.modal")
                                        return;
                                    views.editEmployeeViewModal.$(".btn-primary").attr("disabled", "disabled");
                                }
                        }
                    });

                views.editEmployeeViewModal.on("invalid", function() { console.log("invalid stuff!"); });

                views.salaryHistory = new EmployeeSalaryHistoryView({
                        el : $("#ProjectEmployees"),
                        collection : data.employees,
                        events : {
                            "click tr" : function(e) {
                                    var row = e.currentTarget;
                                    if (row.rowIndex == 0)
                                        return; // Ignore header row

                                    var id = row.getAttribute("employee-id");
                                    var entry = data.employees.get(id).clone();

                                    views.editEmployeeView.setModel(entry);
                                    views.editEmployeeViewModal.show();
                                }
                        }
                    }).render();
            }

            function addHistoryEntry() {
                var ProjectEmployee = require("models/ProjectEmployee");
                var entry = new ProjectEmployee({ startDate : new Date() });
                views.editEmployeeView.setModel(entry);
                views.editEmployeeViewModal.show();
            }
        </script>

        <br></br>
	</div>
</div>
<script>

require([
    "views/DepartmentListView",
    "views/ProjectEmployeeView",
    "views/ModalDialogView",
    "views/EditProjectEmployeeView",
    "EditProjectEmployeeData"
], function() {
    registerBuildUI(function($) {
        buildEditEmployeeView();

        var spinner = $("#spinner");
        spinner.hide();
        $("div", spinner).css({ display : "block" });

        $("#employeeDiv").show();
    });
});

</script>
