
define([
    "jquery",
    "backbone",
    "text!./EmployeeSalaryHistoryView.txt",
    "views/DepartmentListView",
    "models/EmployeeHistoryCollection"
], function(
    $,
    Backbone,
    templateText,
    DepartmentListView,
    EmployeeHistoryCollection
) {
    return Backbone.View.extend({
        tagName : "table",

        className : "EmployeeSalaryHistoryView",

        template : _.template(templateText),

        initialize : function(options) {
            options = options || {};

            _.bindAll(this, "render");

            if (this.className)
                this.$el.addClass(this.className);

            if (this.collection) {
                if (!(this.collection instanceof EmployeeHistoryCollection))
                    throw new Error("The collection must be an EmployeeHistoryCollection");

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
            var history = this.collection;

            this.$el.html(
                this.template({
                    history : history,
                    dateFormat : "Y-m-d"
                })
            );

            // Add in the child DepartmentListView(s) for each row, since they can't be created in the template
            this.$("tr[history-id]").each(function(index, el) {
                    var id = el.getAttribute("history-id");
                    var depts = history.get(id).get("departments");
                    var view = new DepartmentListView({ collection : depts });
                    $("td.departments", el).html(view.render().el);
                });

            return this;
        }

    });
});
