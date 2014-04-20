define([
    "backbone",
    "models/EmployeeHistory"
], function(
    Backbone,
    EmployeeHistory
) {
    return Backbone.Collection.extend({ model : EmployeeHistory });
});