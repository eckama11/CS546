define([
    "backbone",
    "models/ProjectEmployee"
], function(
    Backbone,
    ProjectEmployee
) {
    return Backbone.Collection.extend({ model : ProjectEmployee });
});