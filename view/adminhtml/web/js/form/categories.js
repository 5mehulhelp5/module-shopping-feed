define(['Magento_Ui/js/form/element/abstract', 'ko'], function (Element, ko) {
    'use strict';
    return Element.extend({
        defaults: {
            categories: [], taxonomyOptions: [], localInventory: false,
            ignoreTmpls: {categories: true, taxonomyOptions: true}
        },
        initialize: function () {
            this._super();
            var self = this, saved = this.value() || {};
            this.rows = this.categories.map(function (category) {
                var entry = saved[category.id] || {}, row = Object.assign({}, category, {
                    store_active: String(category.store_active) === '1',
                    enabled: ko.observable(String(entry.d === undefined ? 1 : entry.d) === '1'),
                    priority: ko.observable(entry.p === undefined ? '' : entry.p),
                    taxonomy: ko.observable(entry.tx === undefined ? '' : entry.tx),
                    productType: ko.observable(entry.ty === undefined ? '' : entry.ty),
                    expanded: ko.observable(true), shown: ko.observable(true)
                });
                ['enabled', 'priority', 'taxonomy', 'productType'].forEach(function (key) {
                    row[key].subscribe(function () { self.publish(); });
                });
                row.taxonomy.subscribe(function (text) { self.fillDescendants(row, 'taxonomy', text); });
                row.productType.subscribe(function (text) { self.fillDescendants(row, 'productType', text); });
                return row;
            });
            return this;
        },
        publish: function () {
            var result = Object.assign({}, this.value() || {});
            this.rows.forEach(function (row) {
                if (row.store_active) {
                    result[row.id] = Object.assign({}, result[row.id] || {}, {
                        d: row.enabled() ? 1 : 0, p: row.priority(), tx: row.taxonomy(), ty: row.productType()
                    });
                }
            });
            this.value(result);
        },
        fillDescendants: function (parent, key, text) {
            if (!text) { return; }
            this.rows.forEach(function (row) {
                if (row.store_active && row.path.indexOf(parent.path + '/') === 0 && !row[key]()) {
                    row[key](text);
                }
            });
        },
        enableAll: function () { this.setEnabled(true); },
        disableAll: function () { this.setEnabled(false); },
        setEnabled: function (enabled) {
            if (this.disabled()) { return; }
            this.rows.forEach(function (row) { if (row.store_active) { row.enabled(enabled); } });
        },
        toggle: function (row) {
            row.expanded(!row.expanded());
            this.updateVisibility();
        },
        expandAll: function () { this.rows.forEach(function (row) { row.expanded(true); }); this.updateVisibility(); },
        collapseAll: function () { this.rows.forEach(function (row) { row.expanded(false); }); this.updateVisibility(); },
        updateVisibility: function () {
            var rows = this.rows;
            rows.forEach(function (row) {
                row.shown(!rows.some(function (parent) {
                    return row.path.indexOf(parent.path + '/') === 0 && !parent.expanded();
                }));
            });
        },
        validate: function () {
            var valid = this.rows.every(function (row) {
                return !row.store_active || row.priority() === '' || /^\d+$/.test(String(row.priority()));
            });
            this.error(valid ? '' : 'Category priority must be a non-negative integer.');
            if (!valid) { this.source.set('params.invalid', true); }
            return {valid: valid, target: this};
        }
    });
});
