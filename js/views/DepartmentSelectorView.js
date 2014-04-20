
define([
    "backbone",
    "text!./DepartmentSelectorView.txt",
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

        events : {
            "change label > input[type=checkbox]" : "_handleSelectionChanged"
        },

        name : null,

        _selectedValues : [ ],

        initialize : function(options) {
            options = options || {};

            this.setSelectedValues(options.selectedValues);

            this.name = options.name || this.name;

            this.setCollection(this.collection);
        },

        render : function() {
            if (this.className)
                this.$el.addClass(this.className);

            this.$el.html(
                this.template({
                    collection : this.collection,
                    name : this.name
                })
            );

            this.setSelectedValues( this._selectedValues );

            return this;
        },

        _handleSelectionChanged : function(e) {
            var $cb = $(e.target);
            if ($cb.prop("checked"))
                this._selectValue($cb);
            else
                this._deselectValue($cb);
        },

        _updateStatus : function() {
            var selected = null;
            var selectionText = "No selection";

            if (this._selectedValues.length) {
                selected = this.getSelectedLabels().join(",");
                selectionText = this._selectedValues.length +" selected";
            }

            this.$("> div:nth-child(1)").text(selectionText);

            var $sel = this.$("> div:nth-child(3)");
            if (selected == null)
                $sel.html("&nbsp;");
            else
                $sel.text(selected);
        },

        _selectValue : function($cb) {
            if ($cb.length) {
                var val = $cb.val();
                $cb.prop("checked", true);
                $cb.parent().addClass("selected");
                this._selectedValues.push(val);
                this._updateStatus();
            }
        },

        _deselectValue : function($cb) {
            if ($cb.length) {
                var val = $cb.val();
                $cb.prop("checked", false);
                $cb.parent().removeClass("selected");
                var idx = this._selectedValues.indexOf(val);
                if (idx != -1)
                    this._selectedValues.splice(idx, 1);
                this._updateStatus();
            }
        },

        deselectValue : function(value) {
            value = parseInt(value);
            if (isNaN(value))
                return;

            if (this.el.childNodes.length) {
                // If already rendered update directly
                this._deselectValue(this.$("label > input[type=checkbox][value="+ value +"]:checked"));
            } else {
                // if not rendered then only update the array
                var idx = this._selectedValues.indexOf(value);
                if (idx != -1)
                    this._selectedValues.splice(idx, 1);
            }
            return this;
        },

        selectValue : function(value) {
            value = parseInt(value);
            if (isNaN(value))
                return;

            if (this.el.childNodes.length) {
                // If already rendered update directly
                this._selectValue(this.$("label > input[type=checkbox][value="+ value +"]").not(":checked"));
            } else {
                // if not rendered then only update the array
                this._selectedValues.push(value);
            }
            return this;
        },

        clearSelection : function() {
            this._selectedValues = [ ];
            this._deselectValue(this.$("label > input[type=checkbox]:checked"));
            return this;
        },

        getSelectedLabels : function() {
            return this.$("label > input[type=checkbox]:checked").parent().map(
                function(i, label) {
                    return $(label).text();
                }
            ).get();
        },

        getSelectedValues : function() {
            return this._selectedValues.concat();
        },

        setSelectedValues : function(items) {
            if (items == null)
                items = [];

            if (!_.isArray(items))
                throw new Error("The items must be an array");

            this.clearSelection();
            for (var i = 0; i < items.length; ++i) {
                this.selectValue(items[i]);
            }
            return this;
        },

        getSelectedDepartments : function() {
            var sel = [ ];
            for (var i = 0; i < this._selectedValues.length; ++i) {
                var dept = this.collection.get(this._selectedValues[i]);
                if (dept)
                    sel.push(dept);
            } // for
            return new DepartmentCollection(sel);
        },

        setCollection : function(collection) {
            if ((collection != null) && !(collection instanceof DepartmentCollection))
                throw new Error("The collection must be a DepartmentCollection");

            if (this.collection)
                this.collection.off("change add remove reset sort", this.render, this);

            this.collection = collection;

            if (this.collection)
                this.collection.on("change add remove reset sort", this.render, this);

            this.render();
        }

    });
});
