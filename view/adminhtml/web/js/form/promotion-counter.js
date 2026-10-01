define(['Magento_Ui/js/form/element/abstract'], function (Element) {
    'use strict';
    return Element.extend({
        defaults: {incremented: false},
        initObservable: function () { this._super().observe('incremented'); return this; },
        increment: function () {
            if (!this.disabled() && !this.incremented()) {
                this.value((parseInt(this.value(), 10) || 0) + 1);
                this.incremented(true);
            }
        }
    });
});
