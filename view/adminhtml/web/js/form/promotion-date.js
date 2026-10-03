define(['Magento_Ui/js/form/element/date'], function (DateElement) {
    'use strict';
    return DateElement.extend({
        prepareDateTimeFormats: function () {
            this._super();
            // Core adopts the Admin locale for output as well as display. Promotions store Y/m/d.
            this.inputDateFormat = 'YYYY/MM/DD';
            this.outputDateFormat = 'YYYY/MM/DD';
            this.validationParams.dateFormat = this.outputDateFormat;
        }
    });
});
