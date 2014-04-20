
define([
    "backbone",
    "text!./DepartmentListView.txt",
    "models/DepartmentCollection"
], function(
    Backbone,
    templateText,
    DepartmentCollection
) {
    return Backbone.View.extend({
        tagName : "div",

        className : "DepartmentSelectorView",

        template : _.template(templateText),

        initialize : function(options) {
            options = options || {};

            if (this.className)
                this.$el.addClass(this.className);

            if (this.collection) {
                if (!(this.collection instanceof DepartmentCollection))
                    throw new Error("The collection must be a DepartmentCollection");

                this.collection.on("change add remove reset sort", this.render, this);
            }
        },

        render : function() {
            this.$el.html(
                this.template({
                    departments : this.collection
                })
            );
            return this;
        }

    });
});
