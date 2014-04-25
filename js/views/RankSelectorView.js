define([
    "backbone",
    "underscore",
    "text!./RankSelectorView.txt",
    "models/RankCollection"
], function(
    Backbone,
    _,
    templateText,
    RankCollection
) {
    return Backbone.View.extend({
        tagName : "div",

        template : _.template(templateText),

        events : {
        },

        name : null,

        selectedValue : null,

        "$selectEl" : null,

        initialize : function(options) {
            options = options || {};

            if (options.selectedValue)
                this.setSelectedValue(options.selectedValue);

            this.name = options.name || this.name;

            this.setCollection(this.collection);
        },

        render : function() {
            this.selectedValue = this.getSelectedValue();

            this.$el.html(
                this.template({
                    collection : this.collection,
                    name : this.name,
                    selectedValue : this.selectedValue
                })
            );

            this.$selectEl = this.$("> select");

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
            }
            return this;
        },

        setCollection : function(collection) {
            if ((collection != null) && !(collection instanceof RankCollection))
                throw new Error("The collection must be a RankCollection");

            if (this.collection)
                this.collection.off("change add remove reset sort", this.render, this);

            this.collection = collection;

            if (this.collection)
                this.collection.on("change add remove reset sort", this.render, this);

            this.render();
        }

    });
});