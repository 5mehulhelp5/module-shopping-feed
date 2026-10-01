define(['Magento_Ui/js/form/element/select', './preserve-options'], function (Select, preserve) {
    'use strict';
    return Select.extend({
        normalizeData: function (value) {
            if (value !== null && value !== undefined) {
                this.setOptions(preserve(this.options(), value));
                return value;
            }
            return this._super(value);
        }
    });
});
