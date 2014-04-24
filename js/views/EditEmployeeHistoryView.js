define([
    "backbone",
    "bootstrap-datepicker",
    "text!./EditEmployeeHistoryView.txt",
    "views/DepartmentSelectorView",
    "views/RankSelectorView",
    "models/EmployeeHistory",
], function(
    Backbone,
    bsDatepicker,
    templateText,
    DepartmentSelectorView,
    RankSelectorView,
    EmployeeHistory
) {
    var defaultEvents = {
            "change" : "_handleChangeEvent"
        };

    return Backbone.View.extend({
        tagName : "div",

        className : "EditEmployeeHistoryView",

        template : _.template(templateText),

        employeeId : null,
        model : null,
        departments : null,
        ranks : null,

        departmentSelector : null,
        rankSelector : null,

        initialize : function(options) {
            options = options || {};

            this.events = _.extend(defaultEvents, this.events || {});

            this.setDepartments(options.departments);
            this.setRanks(options.ranks);
            this.setEmployeeId(options.employeeId);

            this.setModel(options.model);

            this.on("invalid", this._handleInvalidInput, this);
        },

        setEmployeeId : function(employeeId) {
            if (isNaN(employeeId) || !employeeId)
                throw new Error("The specified employeeId is invalid.");
            this.employeeId = employeeId;
        },

        setModel : function(model) {
            if (model == null)
                model = new EmployeeHistory();
            else if (!(model instanceof EmployeeHistory))
                throw new Error("The model must be a EmployeeHistory");

            if (this.model)
                this.model.off("change", this.render, this);

            this.model = model;
            this.model.on("change", this.render, this);

            this.render();
        },

        setRanks : function(ranks) {
            this.ranks = ranks;
            if (this.rankSelector)
                this.rankSelector.setCollection(this.ranks);
        },

        setDepartments : function(departments) {
            this.departments = departments;
            if (this.departmentSelector)
                this.departmentSelector.setCollection(this.departments);
        },

        render : function() {
            if (this.className)
                this.$el.addClass(this.className);

            this.$el.html(
                this.template({
                    employeeId : this.employeeId,
                    model : this.model
                })
            );

            this.$('[data-provide="datepicker"]').datepicker();

            var $deptSel = this.$(".departmentSelector");
            var depts = this.model.get("departments");
            depts = (depts ? depts.map(function(val) { return val.get("id"); }) : null);

            if (!this.departmentSelector) {
                this.departmentSelector = new DepartmentSelectorView({
                        el : $deptSel,
                        name : "departments",
                        collection : this.departments,
                        selectedValues : depts
                    });
            } else {
                $deptSel.replaceWith(this.departmentSelector.el);
                this.departmentSelector.delegateEvents();
                this.departmentSelector.setSelectedValues(depts);
            }

            var $rankSel = this.$(".rankSelector");
            var rank = this.model.get("rank");
            rank = (rank ? rank.get("id") : null);

            if (!this.rankSelector) {
                this.rankSelector = new RankSelectorView({
                        el : $rankSel,
                        name : "rank",
                        collection : this.ranks,
                        selectedValue : rank
                    }).render();
            } else {
                $rankSel.replaceWith(this.rankSelector.el);
                this.rankSelector.delegateEvents();
                this.rankSelector.setSelectedValue(rank);
            }

            this._resetTooltips();

            return this;
        },

        showSpinner : function() {
            this.$("> .spinner").show();
        },

        hideSpinner : function() {
            this.$("> .spinner").hide();
        },

        _resetTooltips : function() {
            var form = this.$('form').get(0);
            function removeTooltip(elem) {
                elem.tooltip("destroy")
                    .removeClass("error")
                    .data("title", "");
            }
            removeTooltip($(form.elements['rank']));
            removeTooltip(this.departmentSelector.$('div.form-control'));
        },

        _handleInvalidInput : function(validationError, model) {
            var form = this.$('form').get(0);
            for (var i = validationError.length - 1; i >= 0; --i) {
                var err = validationError[i];
                console.log(err);
                
                var elem = (err.attribute == 'departments'
                    ? this.departmentSelector.$('div.form-control')
                    : $(form.elements[err.attribute]));

                elem.tooltip("destroy")
                    .addClass("error")
                    .data("title", err.message)
                    .tooltip();
            }
        },

        save : function(options) {
            if (!this.model.isValid()) {
                // Errors occurred
                this.trigger("invalid", this.model.validationError, this.model);
                return false;
            }

            this.showSpinner();

            var self = this;
            var xhr = $.ajax({
                "type" : "POST",
                "url" : "Admin/doEditEmployeeSalary.php",
                "data" : this.model.toJSON(),
                "dataType" : "json"
                })
                .done(function(data) {
                    self.hideSpinner();
                    if (data.error != null) {
                        self.trigger('error', self.model, data, options);
                    } else {
                        self.trigger("sync", self.model, data, options);
                    }
                })
                .fail(function( jqXHR, textStatus, errorThrown ) {
                    console.log("Error: "+ textStatus +" errorThrown=", errorThrown);
                    console.log(jqXHR.textContent);
                    self.trigger(
                        'error',
                        self.model,
                        {
                            status : jqXHR.status,
                            textStatus : textStatus,
                            errorThrown : errorThrown,
                            xhr : jqXHR,
                        },
                        options
                    );
                    self.hideSpinner();
                });

            this.trigger('request', this.model, xhr, options);

            return false;
        },

        _handleChangeEvent : function(e) {
            if (e.target.form == this.$("> form").get(0)) {
                var name = e.target.name;
                var elem = e.target.form[name];
                name = name.replace(/\[\]$/, "");

                var $el = $(e.target);
                var val = $el.val();

                if ((name == 'startDate') || (name == 'endDate')) {
                    val = new Date(val);
                } else if (name == 'rank') {
                    val = this.ranks.get(val);
                } else if (name == 'departments') {
                    val = this.departmentSelector.getSelectedDepartments();
                }

                this.model.set(name, val, { silent : true });
                this.trigger("change", this);
            }
        }

    });
});