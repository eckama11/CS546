define([
    "backbone",
    "models/Department"
], function(
    Backbone,
    Department
) {
    return Backbone.Collection.extend({ model : Department });
});