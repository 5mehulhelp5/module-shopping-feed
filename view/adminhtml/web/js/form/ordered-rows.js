define(['Magento_Ui/js/dynamic-rows/dynamic-rows'], function (DynamicRows) {
    'use strict';
    return DynamicRows.extend({
        addChild: function (data, index, property) {
            if (!index && index !== 0) {
                index = this.recordData().length;
                var order = this.recordData().reduce(function (max, row) {
                    return Math.max(max, Number(row.order) || 0);
                }, 0) + 10;
                this.source.set(this.dataScope + '.' + this.index + '.' + index + '.order', order);
            }
            return this._super(data, index, property);
        }
    });
});
