define([
    "backbone",
    "models/Department",
    "models/Project",
    "models/Employee"
], function(
    Backbone,
    Department,
    Project,
    Employee
) {
    return Backbone.Model.extend({
        initialize: function() {
            if ((this.attributes.startDate != null) && !(this.attributes.startDate instanceof Date))
                this.attributes.startDate = new Date(this.attributes.startDate);

            if ((this.attributes.endDate != null) && !(this.attributes.endDate instanceof Date))
                this.attributes.endDate = new Date(this.attributes.endDate);

            if ((this.attributes.lastPayPeriodEndDate != null) && !(this.attributes.lastPayPeriodEndDate instanceof Date))
                this.attributes.lastPayPeriodEndDate = new Date(this.attributes.lastPayPeriodEndDate);

            var project = this.attributes.project;
            if (project) {
                if (!(project instanceof Project))
                    project = this.attributes.project = new Project(project);
                //project.on("change", this._proxyChangeEvent, this);
            }

            var department = this.attributes.department;
            if (department) {
                if (!(department instanceof Department))
                    department = this.attributes.department = new Department(department);
                //department.on("change", this._proxyChangeEvent, this);
            }

            var employee = this.attributes.employee;
            if (employee) {
                if (!(employee instanceof Employee))
                    employee = this.attributes.employee = new Employee(employee);
                //employee.on("change", this._proxyChangeEvent, this);
            }
        },

        toJSON: function(options) {
            var rv = _.clone(this.attributes);
            if (rv.startDate) rv.startDate = formatDate(rv.startDate, "Y-m-d");
            if (rv.endDate) rv.endDate = formatDate(rv.endDate, "Y-m-d");
            if (rv.lastPayPeriodEndDate) rv.lastPayPeriodEndDate = formatDate(rv.lastPayPeriodEndDate, "Y-m-d");
            rv.project = (
                rv.project && rv.project.id
                ? rv.project.id 
                : null
            );
            rv.department = (
                rv.department && rv.department instanceof Department
                ? rv.department.id
                : null
            );
            rv.employee = (
                rv.employee && rv.employee.id
                ? rv.employee.id 
                : null
            );
            return rv;
        },

        validate : function(attributes, options) {
            function ValidateError(message, attribute) {
                this.message = message;
                this.attribute = attribute;
            }
            ValidateError.prototype.toString = function() { return "ValidateError: ["+ this.attribute +"] "+ this.message }

            var errs = [];

            if (attributes.startDate == null)
                errs.push(new ValidateError("You must enter a start date", "startDate"));

            if (!(attributes.startDate instanceof Date))
                errs.push(new ValidateError("The start date is invalid", "startDate"));

            if (attributes.endDate) {
                if (!(attributes.endDate instanceof Date))
                    errs.push(new ValidateError("The end date is invalid", "startDate"));

                if (attributes.endDate < attributes.startDate)
                    errs.push(new ValidateError("The end date cannot be earlier than the start date", "endDate"));
            }

            if (attributes.lastPayPeriodEndDate && !(attributes.lastPayPeriodEndDate instanceof Date))
                errs.push(new ValidateError("The last pay period end date is invalid", "lastPayPeriodEndDate"));

            if (!(attributes.project instanceof Project) || !attributes.project.id)
                errs.push(new ValidateError("You must specify the project", "project"));

            if (!(attributes.department instanceof Department) || !attributes.department.id)
                errs.push(new ValidateError("You must select a department", "department"));

            if (!(attributes.employee instanceof Employee) || !attributes.employee.id)
                errs.push(new ValidateError("You must select an employee", "employee"));

            if (attributes.percentAllocation == null)
                errs.push(new ValidateError("You must enter the employee's percent allocation", "percentAllocation"));

            if (isNaN(attributes.percentAllocation) ||
                (attributes.percentAllocation < 0) ||
                (attributes.percentAllocation > 100))
            {
                errs.push(new ValidateError("Invalid value specified for percent allocation", "percentAllocation"));
            } else
                attributes.percentAllocation = Number(attributes.percentAllocation);

            if (errs.length)
                return errs;
        },

        isActive : function() {
            var now = new Date();
            var endDate = this.get("endDate");

            return (
                    (now >= this.get("startDate")) &&
                    (!endDate || now <= endDate)
                );
        },

        isAppliedToPaystub : function() {
            return (this.get("lastPayPeriodEndDate") != null);
        },

        canDelete : function() {
            return !this.isAppliedToPaystub();
        },

        canEdit : function() {
            return !this.isAppliedToPaystub() ||
               !this.get("endDate") ||
               (this.get("endDate") >= this.get("lastPayPeriodEndDate"));
        }

    });
});