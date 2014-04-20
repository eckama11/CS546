define([
    "backbone",
    "models/Rank"
], function(
    Backbone,
    Rank
) {
    return Backbone.Collection.extend({ model : Rank });
});