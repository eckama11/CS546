define([
    "backbone",
    "models/Employee"
], function(
    Backbone,
    Employee
) {
    return Backbone.Collection.extend({ model : Employee });
});