
define([
    "backbone",
    "models/DepartmentCollection",
    "models/Rank"
], function(
    Backbone,
    DepartmentCollection,
    Rank
) {
    return Backbone.Model.extend({
        initialize: function() {
            if ((this.attributes.startDate != null) && !(this.attributes.startDate instanceof Date))
                this.attributes.startDate = new Date(this.attributes.startDate);

            if ((this.attributes.endDate != null) && !(this.attributes.endDate instanceof Date))
                this.attributes.endDate = new Date(this.attributes.endDate);

            if ((this.attributes.lastPayPeriodEndDate != null) && !(this.attributes.lastPayPeriodEndDate instanceof Date))
                this.attributes.lastPayPeriodEndDate = new Date(this.attributes.lastPayPeriodEndDate);

            var depts = this.attributes.departments;
            if (depts) {
                if (!(depts instanceof DepartmentCollection))
                    depts = this.attributes.departments = new DepartmentCollection( depts );
/*
                depts.on("change", this._proxyChangeEvent, this);
                depts.on("add", this._proxyAddEvent, this);
                depts.on("remove", this._proxyRemoveEvent, this);
                depts.on("reset", this._proxyResetEvent, this);
                depts.on("sort", this._proxySortEvent, this);
*/
            }

            var rank = this.attributes.rank;
            if (rank) {
                if (!(rank instanceof Rank))
                    rank = this.attributes.rank = new Rank(rank);
                rank.on("change", this._proxyChangeEvent, this);
            }
        },

        _proxyChangeEvent : function() {
            this._proxyEvent("change", arguments);
        },
    /*
        _proxyAddEvent : function() {
            this._proxyEvent("add", arguments);
        },

        _proxyRemoveEvent : function() {
            this._proxyEvent("remove", arguments);
        },

        _proxyResetEvent : function() {
            this._proxyEvent("reset", arguments);
        },

        _proxySortEvent : function() {
            this._proxyEvent("sort", arguments);
        },
    */
        _proxyEvent : function(eventName, eventArgs) {
            var args = [ eventName ];
            args.push.apply(args, eventArgs);
            this.trigger.apply(this, args);
        },

        toJSON: function(options) {
            var rv = _.clone(this.attributes);
            rv.departments = (
                rv.departments && rv.departments instanceof DepartmentCollection
                ? rv.departments.map(function(d) { return d.id; })
                : null
            );
            rv.rank = (
                rv.rank && rv.rank.id
                ? rv.rank.id 
                : null
            );
            return rv;
        },

        clone: function() {
            var attributes = _.clone(this.attributes);

            // Perform shallow copy on the nested collection
            // so modifications to the clone's reference don't change both models
            if (attributes.departments instanceof DepartmentCollection)
                attributes.departments = attributes.departments.clone();

            return new this.constructor(attributes);
        },

        validate : function(attributes, options) {
            function ValidateError(message, attribute) {
                this.message = message;
                this.attribute = attribute;
            }

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

            if (!(attributes.departments instanceof DepartmentCollection) ||
                !attributes.departments.length ||
                attributes.departments.find(function(item) {  return (item == null) || !item.id; }))
            {
                errs.push(new ValidateError("You must select at least one department for employee", "departments"));
            }

            if (!(attributes.rank instanceof Rank))
                errs.push(new ValidateError("You must enter employee's rank", "rank"));

            if (attributes.numDeductions == null)
                errs.push(new ValidateError("You must enter employee's number of deductions", "numDeductions"));

            if (isNaN(attributes.numDeductions) || (attributes.numDeductions < 0))
                errs.push(new ValidateError("Invalid value specified for number of deductions", "numDeductions"));
            else
                attributes.numDeductions = Number(attributes.numDeductions);

            if (attributes.salary == null)
                errs.push(new ValidateError("You must enter employee's salary", "salary"));

            if (isNaN(attributes.salary) || (attributes.salary < 0))
                errs.push(new ValidateError("Invalid value specified for salary", "salary"));
            else
                attributes.salary = Number(attributes.salary);

            if (attributes.salary < attributes.rank.get("baseSalary")) {
                errs.push(new ValidateError("The salary cannot be less than the base salary assigned to the selected rank: "+ attributes.rank, "salary"));
            }

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
