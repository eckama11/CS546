define([
    "backbone",
    "underscore",
    "text!./ModalDialogView.txt"
], function(
    Backbone,
    _,
    templateText
) {
    var defaultEvents = {
            'hidden.bs.modal': '_hidden'
            //,"click .btn-primary" : "_primaryButtonClicked"
        };

    return Backbone.View.extend({

        className : 'modal fade',

        template : _.template(templateText),

        title : "Dialog",

        contentView : null,

        "$modalBody" : null,

        "$modalTitle" : null,

        initialize: function(options) {
            options = options || {};

            this.title = options.title || this.title;

            this.events = _.extend(defaultEvents, this.events || {});

            this.contentView = options.contentView;

            this.render();
        },

        setContentView : function(view) {
            if (view && !(view instanceof Backbone.View))
                throw new Error("The contentView must be a Backbone View");

            this.contentView = view;
            if (this.$modalBody) {
                if (view == null)
                    this.$modalBody.empty();
                else
                    this.$modalBody.html( view.el );
            }
        },

        setTitle : function(title) {
            if (this.title == title)
                return;

            this.title = title;
            if (this.$modalTitle)
                this.$modalTitle.text( name );
        },

        show: function() {
            this.$el.modal('show');
            this.delegateEvents();
        },

        close: function() {
            this.$el.modal('hide');
        },

        _hidden: function() {
            //console.log("hidden!");
//            this.$el.data('modal', null);
        },

        render: function() {
            if (this.$modalBody)
                return;

            this.$el.html(
                this.template({
                    title : this.title
                })
            );

            this.$modalBody = this.$(".modal-body");
            this.$modalTitle = this.$(".modal-title");

            this.setContentView(this.contentView);

            if (this.title)
                this.$modalTitle.text( this.title );

            this.$el.modal({
                show : false,       // don't show modal on instantiation
                backdrop : 'static' // don't close the dialog when clicking on the backdrop
            });
            //document.body.appendChild(this.el);

            return this;
        }

    });

});