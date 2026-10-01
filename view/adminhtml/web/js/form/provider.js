define(['Magento_Ui/js/form/provider'], function (Provider) {
    'use strict';

    return Provider.extend({
        initialize: function () {
            this._super();
            var initial = this.get('data').feed_form_initial_data;
            if (typeof initial === 'string') {
                this.set('data', JSON.parse(initial));
            }
            this.childIds = {};
            ['schedules', 'uploads'].forEach(function (key) {
                this.childIds[key] = (this.get('data')[key] || []).filter(function (row) {
                    return row.id;
                }).map(function (row) { return row.id; });
            }, this);
            return this;
        },

        save: function (options) {
            var data = JSON.parse(JSON.stringify(this.get('data')));
            // DynamicRows removes deleted rows. Existing child models require explicit deletion IDs.
            ['schedules', 'uploads'].forEach(function (key) {
                if (!Array.isArray(data[key])) { return; }
                (this.childIds[key] || []).forEach(function (id) {
                    if (!data[key].some(function (row) { return String(row.id) === String(id); })) {
                        data[key].push({id: id, delete: true});
                    }
                });
            }, this);
            // Magento's HTML form serializer drops empty arrays. Keep the complete edit contract.
            this.client.save({feed_form_data: JSON.stringify(data)}, options);
            return this;
        }
    });
});
