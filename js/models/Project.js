define([
    "backbone",
    "models/DepartmentCollection"
], function(
    Backbone,
    DepartmentCollection
) {
    return Backbone.Model.extend({
        initialize: function() {
            if ((this.attributes.startDate != null) && !(this.attributes.startDate instanceof Date))
                this.attributes.startDate = new Date(this.attributes.startDate);

            if ((this.attributes.endDate != null) && !(this.attributes.endDate instanceof Date))
                this.attributes.endDate = new Date(this.attributes.endDate);

            if ((this.attributes.departments != null) && !(this.attributes.departments instanceof DepartmentCollection))
                this.attributes.departments = new DepartmentCollection(this.attributes.departments);
        }
    });
});