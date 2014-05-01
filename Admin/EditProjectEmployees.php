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
        $project->departments = $db->readDepartmentsForProject($projectId);

        $employees = $db->readProjectEmployeeAssociations($project);

        $deptEmployees = [];
        foreach ($project->departments as $dept) {
            $emps = $db->readEmployeesForDepartment($dept->id, null, $project->startDate, $project->endDate);
            if (count($emps))
                $deptEmployees[$dept->id] = $emps;
        } // foreach

?>
<script>
    define(
        'EditProjectEmployeeData',
        ['models/Project', 'models/ProjectEmployeeCollection', 'models/EmployeeCollection'],
        function(Project, ProjectEmployeeCollection, EmployeeCollection) {
            var rv = {
                projectId : <?= $projectId ?>,
                project : new Project(<?= json_encode($project) ?>),
                employees : new ProjectEmployeeCollection(<?= json_encode($employees) ?>),
                deptEmployees : { <?php
                    $sep = '';
                    foreach ($deptEmployees as $deptId => $emps) {
                        echo $sep .'"'. $deptId .'" : new EmployeeCollection('. json_encode($emps) .')';
                        $sep = ', ';
                    }
                ?> }
            };
            for (var i = 0; i < rv.employees.length; ++i) {
                rv.employees.at(i).set('project', rv.project);
            }
            return rv;
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

        <table id="ProjectEmployees" class="table table-striped table-bordered table-condensed table-hover"></table>
        
        <button type="button" class="btn btn-default" onclick="addEmployeeEntry()">Add</button>
        
        <script>
            var views = {}; // In global scope for debugging/console access to the views

            function buildEditProjectEmployeeView() {
                var ProjectEmployeeView = require("views/ProjectEmployeeView");
                var ModalDialogView = require("views/ModalDialogView");
                var EditProjectEmployeeView = require("views/EditProjectEmployeeView");
                var data = require("EditProjectEmployeeData");

                views.editProjectEmployeeView = new EditProjectEmployeeView({
                        project : data.project,
                        deptEmployees : data.deptEmployees,
                        model : null
                    });

                    views.editProjectEmployeeView.on({
                            request : function(model, data, options) {
                                    // Don't allow user to dismiss the dialog during the request
                                    views.editProjectEmployeeViewModal.$(".btn-primary,.btn-default").attr("disabled", "disabled");
                                    views.editProjectEmployeeViewModal.$(".close").hide();
                                },
                            error : function(model, data, options) {
                                    console.log("Error!", model, data, options);

                                    showError(data.error);

                                    // Re-enable the dialog
                                    views.editProjectEmployeeViewModal.$(".btn-primary,.btn-default").removeAttr("disabled");
                                    views.editProjectEmployeeViewModal.$(".close").show();
                                },
                            sync : function(model, data, options) {
                                    // Re-enable the dialog
                                    views.editProjectEmployeeViewModal.$(".btn-primary,.btn-default").removeAttr("disabled");
                                    views.editProjectEmployeeViewModal.$(".close").show();

                                    // Close the dialog and refresh the list of employees
                                    views.editProjectEmployeeViewModal.close();

                                    views.projectEmployees.collection.add(model, {merge:true});
                                    views.projectEmployees.collection.sort();
                                    views.projectEmployees.render();
                                },
                            change : function(view) {
                                    views.editProjectEmployeeViewModal.$(".btn-primary").removeAttr("disabled");
                                }
                        });

                views.editProjectEmployeeViewModal = new ModalDialogView({
                        title : "Edit Employee Assignment",
                        contentView : views.editProjectEmployeeView.render(),
                        events : {
                            "click .btn-primary" : function(e) {
                                    views.editProjectEmployeeView.save();
                                },
                            "hide.bs.modal" : function(e) {
                                    if (e.namespace != "bs.modal")
                                        return;

                                    if (views.editProjectEmployeeView.model.hasChanged()) {
                                        if (!confirm("Cancel changes and close dialog?"))
                                            e.preventDefault();
                                    }
                                },
                            "show.bs.modal" : function(e) {
                                    if (e.namespace != "bs.modal")
                                        return;
                                    views.editProjectEmployeeViewModal.$(".btn-primary").attr("disabled", "disabled");
                                }
                        }
                    });

                views.editProjectEmployeeViewModal.on("invalid", function() { console.log("invalid stuff!"); });

                views.projectEmployees = new ProjectEmployeeView({
                        el : $("#ProjectEmployees"),
                        collection : data.employees,
                        events : {
                            "click tr" : function(e) {
                                    var row = e.currentTarget;
                                    if (row.rowIndex == 0)
                                        return; // Ignore header row

                                    var id = row.getAttribute("employee-id");
                                    var entry = data.employees.get(id).clone();

                                    views.editProjectEmployeeView.setModel(entry);
                                    views.editProjectEmployeeViewModal.show();
                                }
                        }
                    }).render();
            }

            function addEmployeeEntry() {
                var ProjectEmployee = require("models/ProjectEmployee");
                var data = require("EditProjectEmployeeData");

                var entry = new ProjectEmployee({ project : data.project });
                views.editProjectEmployeeView.setModel(entry);
                views.editProjectEmployeeViewModal.show();
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
        buildEditProjectEmployeeView();

        var spinner = $("#spinner");
        spinner.hide();
        $("div", spinner).css({ display : "block" });

        $("#employeeDiv").show();
    });
});

</script>
