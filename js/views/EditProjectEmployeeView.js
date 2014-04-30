define([
    "backbone",
    "bootstrap-datepicker",
    "text!./EditProjectEmployeeView.txt",
    "views/SingleDepartmentSelectorView",
    "views/EmployeeSelectorView",
    "models/ProjectEmployee",
    "models/Project"
], function(
    Backbone,
    bsDatepicker,
    templateText,
    SingleDepartmentSelectorView,
    EmployeeSelectorView,
    ProjectEmployee,
    Project
) {
    var defaultEvents = {
            "change" : "_handleChangeEvent"
        };

    return Backbone.View.extend({
        tagName : "div",

        className : "EditProjectEmployeeView",

        template : _.template(templateText),

        project : null,
        unassignedEmployees : null,
        model : null,

        departmentSelector : null,
        employeeSelector : null,

        initialize : function(options) {
            options = options || {};

            this.events = _.extend(defaultEvents, this.events || {});

            this.setProject(options.project);
            this.setUnassignedEmployees(options.unassignedEmployees);
            this.setModel(options.model);

            this.on("invalid", this._handleInvalidInput, this);
        },

        setProject : function(project) {
            if (!(project instanceof Project))
                throw new Error("The project must be specified");

            this.project = project;
        },

        setModel : function(model) {
            if (model == null)
                model = new ProjectEmployee({ project : this.project });
            else if (!(model instanceof ProjectEmployee))
                throw new Error("The model must be a ProjectEmployee");

            if (this.model)
                this.model.off("change", this.render, this);

            this.model = model;
            this.model.on("change", this.render, this);

            this.render();
        },

        setUnassignedEmployees : function(unassignedEmployees) {
            this.unassignedEmployees = unassignedEmployees;
        },

        render : function() {
            if (this.className)
                this.$el.addClass(this.className);

            this.$el.html(
                this.template({
                    model : this.model
                })
            );

            this.$('[data-provide="datepicker"]').datepicker();

            var $deptSel = this.$(".departmentSelector");
            var dept = this.model.get("department");
            dept = (dept ? dept.get("id") : null);

            if (!this.departmentSelector) {
                this.departmentSelector = new SingleDepartmentSelectorView({
                        el : $deptSel,
                        name : "department",
                        collection : this.model.get("project").get("departments"),
                        selectedValue : dept
                    });
            } else {
                $deptSel.replaceWith(this.departmentSelector.el);
                this.departmentSelector.delegateEvents();
                this.departmentSelector.setSelectedValue(dept);
            }

            var $employeeSel = this.$(".employeeSelector");
            var employee = this.model.get("employee");
            employee = (employee ? employee.get("id") : null);

            if (!this.employeeSelector) {
                this.employeeSelector = new EmployeeSelectorView({
                        el : $employeeSel,
                        name : "employee",
                        collection : (
                                this.departmentSelector.selectedValue
                                    ? this.unassignedEmployees[this.departmentSelector.selectedValue]
                                    : null
                            ),
                        selectedValue : employee
                    }).render();
            } else {
                $employeeSel.replaceWith(this.employeeSelector.el);
                this.employeeSelector.delegateEvents();
                this.employeeSelector.setSelectedValue(employee);
            }

            this._resetTooltips();

            return this;
        },

        showSpinner : function() {
            this.$("> .spinner").show();
        },

        hideSpinner : function() {
            this.$("> .spinner").hide();
        },

        _resetTooltips : function() {
            var form = this.$('form').get(0);
            function removeTooltip(elem) {
                elem.tooltip("destroy")
                    .removeClass("error")
                    .data("title", "");
            }
            for (var i = form.elements.length - 1; i >= 0; --i) {
                var elem = form.elements[i];
                removeTooltip($(elem));
            }
        },

        _handleInvalidInput : function(validationError, model) {
            var form = this.$('form').get(0);
            for (var i = validationError.length - 1; i >= 0; --i) {
                var err = validationError[i];
                console.log(err);
                
                var elem = $(form.elements[err.attribute]);

                elem.tooltip("destroy")
                    .addClass("error")
                    .data("title", err.message)
                    .tooltip();
            }
        },

/*
        save : function(options) {
            this.model.set("employeeId", this.employeeId);
            this._resetTooltips();
            if (!this.model.isValid()) {
                // Errors occurred
                this.trigger("invalid", this.model.validationError, this.model);
                return false;
            }

            this.showSpinner();

            var self = this;
            var xhr = $.ajax({
                "type" : "POST",
                "url" : "Admin/doEditEmployeeSalary.php",
                "data" : this.model.toJSON(),
                "dataType" : "json"
                })
                .done(function(data) {
                    self.hideSpinner();
                    if (data.error != null) {
                        self.trigger('error', self.model, data, options);
                    } else {
                        self.model.set('id', data.id);
                        self.model.changed = {};
                        self.trigger("sync", self.model, data, options);
                    }
                })
                .fail(function( jqXHR, textStatus, errorThrown ) {
                    console.log("Error: "+ textStatus +" errorThrown=", errorThrown);
                    console.log(jqXHR.textContent);
                    self.trigger(
                        'error',
                        self.model,
                        {
                            error : jqXHR.status +" "+ textStatus +"\n"+ jqXHR.textContent,
                            status : jqXHR.status,
                            textStatus : textStatus,
                            errorThrown : errorThrown,
                            xhr : jqXHR,
                        },
                        options
                    );
                    self.hideSpinner();
                });

            this.trigger('request', this.model, xhr, options);

            return false;
        },
*/

        _handleChangeEvent : function(e) {
            if (e.target.form == this.$("form").get(0)) {
                var name = e.target.name;
                name = name.replace(/\[\]$/, "");

                var val;

                val = $(e.target).val();
                val = $.trim(val);
                if (val == "") val = null;

                if (val != null) {
                    if ((name == 'startDate') || (name == 'endDate')) {
                        val = new Date(val);
                    } else if (name == 'department') {
                        val = this.departmentSelector.collection.get(val);
                    } else if (name == 'employee') {
                        val = this.employeeSelector.collection.get(val);
                    }
                }

                this.model.set(name, val, { silent : true });
                this.trigger("change", this);
            }
        }

    });
});