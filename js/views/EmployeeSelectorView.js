define([
    "backbone",
    "underscore",
    "text!./EmployeeSelectorView.txt",
    "models/EmployeeCollection"
], function(
    Backbone,
    _,
    templateText,
    EmployeeCollection
) {
    return Backbone.View.extend({
        tagName : "div",

        template : _.template(templateText),

        events : {
        },

        name : null,

        selectedValue : null,

        readOnly : false,

        "$selectEl" : null,

        initialize : function(options) {
            options = options || {};

            if (options.selectedValue)
                this.setSelectedValue(options.selectedValue);

            this.name = options.name || this.name;

            if (options.readOnly != null)
                this.readOnly = options.readOnly;

            this.setCollection(this.collection);
        },

        render : function() {
            this.selectedValue = this.getSelectedValue();

            this.$el.html(
                this.template({
                    collection : this.collection,
                    name : this.name,
                    selectedValue : this.selectedValue,
                    readOnly : this.readOnly
                })
            );

            this.$selectEl = this.$("> select");
            if (!this.$selectEl.length)
                this.$selectEl = null;

            return this;
        },

        getSelectedValue : function() {
            if (this.$selectEl)
                this.selectedValue = this.$selectEl.val();

            return this.selectedValue;
        },

        setSelectedValue : function(value) {
            this.selectedValue = value;
            if (this.$selectEl) {
                if (this.selectedValue) {
                    this.$selectEl.val(this.selectedValue);
                    this.selectedValue = this.$selectEl.val();
                } else
                    this.$selectEl.get(0).selectedIndex = 0;
            } else if (this.readOnly)
                this.render();
            return this;
        },

        setCollection : function(collection) {
            if ((collection != null) && !(collection instanceof EmployeeCollection))
                throw new Error("The collection must be a EmployeeCollection");

            if (this.collection)
                this.collection.off("change add remove reset sort", this.render, this);

            this.collection = collection;

            if (this.collection)
                this.collection.on("change add remove reset sort", this.render, this);

            this.render();

            return this;
        }

    });
});