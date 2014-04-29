define([
    "backbone",
    "models/EmployeeHistory"
], function(
    Backbone,
    EmployeeHistory
) {
    return Backbone.Model.extend({
        initialize: function() {
            var current = this.attributes.current;
            if (current) {
                if (!(current instanceof EmployeeHistory))
                    current = this.attributes.current = new EmployeeHistory(current);
                //current.on("change", this._proxyChangeEvent, this);
            }
        }
    });
});