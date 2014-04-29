
define([
    "jquery",
    "backbone",
    "text!./ProjectEmployeeView.txt",
    "models/ProjectEmployeeCollection"
], function(
    $,
    Backbone,
    templateText,
    ProjectEmployeeCollection
) {
    return Backbone.View.extend({
        tagName : "table",

        className : "ProjectEmployeeView",

        template : _.template(templateText),

        initialize : function(options) {
            options = options || {};

            _.bindAll(this, "render");

            if (this.className)
                this.$el.addClass(this.className);

            if (this.collection) {
                if (!(this.collection instanceof ProjectEmployeeCollection))
                    throw new Error("The collection must be an ProjectEmployeeCollection");

                if (!this.collection.comparator)
                    this.collection.comparator =
                        function(a, b) {
                            a = a.get('startDate');
                            b = b.get('startDate');
                            return (a == b ? 0 : (a < b ? 1 : -1));
                        }

                this.collection.on("add remove change reset sort", this.render);
            }
        },

        render : function() {
            this.$el.html(
                this.template({
                    employees : this.collection,
                    dateFormat : "Y-m-d"
                })
            );

            return this;
        }

    });
});
