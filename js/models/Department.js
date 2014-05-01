define([
    "backbone"
], function(
    Backbone
) {
    return Backbone.Model.extend({
            toString : function() {
                return this.get('name');
            }
        });
});