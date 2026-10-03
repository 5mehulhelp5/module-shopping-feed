define(['Magento_Ui/js/form/element/multiselect', './preserve-options'], function (Multiselect, preserve) {
    'use strict';
    return Multiselect.extend({
        normalizeData: function (value) {
            value = this._super(value);
            this.setOptions(preserve(this.options(), value));
            return value;
        },
        // The JSON form provider preserves [] directly; no synthetic POST field is needed.
        setPrepareToSendData: function () {}
    });
});
