define(['Magento_Ui/js/form/element/abstract'], function (Element) {
    'use strict';
    return Element.extend({
        defaults: {listens: {dependency: 'updateVisibility'}},
        updateVisibility: function (value) { this.visible(String(value) === this.showWhen); }
    });
});
