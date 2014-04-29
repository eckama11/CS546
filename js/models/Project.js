define([
    "backbone"
], function(
    Backbone
) {
    return Backbone.Model.extend({
        initialize: function() {
            if ((this.attributes.startDate != null) && !(this.attributes.startDate instanceof Date))
                this.attributes.startDate = new Date(this.attributes.startDate);

            if ((this.attributes.endDate != null) && !(this.attributes.endDate instanceof Date))
                this.attributes.endDate = new Date(this.attributes.endDate);
        }
    });
});