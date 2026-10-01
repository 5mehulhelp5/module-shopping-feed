define(['Magento_Ui/js/form/element/abstract', './preserve-options'], function (Element, preserve) {
    'use strict';
    return Element.extend({
        defaults: {
            definitions: {}, kind: 'none', parameterLabel: '', parameterNotice: '', parameterOptions: [],
            ignoreTmpls: {definitions: true}, listens: {attribute: 'changeAttribute'}
        },
        initObservable: function () {
            this._super().observe('kind parameterLabel parameterNotice parameterOptions');
            return this;
        },
        normalizeData: function (value) {
            return value === undefined ? '' : value;
        },
        changeAttribute: function (attribute) {
            var known = Object.prototype.hasOwnProperty.call(this.definitions, attribute),
                definition = known ? this.definitions[attribute] : {kind: 'none'},
                value = this.value();
            if (this.previousAttribute !== undefined && this.previousAttribute !== attribute) {
                value = definition.default === undefined ? '' : definition.default;
                if (definition.kind === 'multiselect') {
                    value = Array.isArray(value) ? value : (value ? String(value).split(',') : []);
                }
                this.value(value);
            }
            if (!known && value !== '' && value !== null && value !== undefined) {
                definition = {
                    kind: 'unsupported',
                    notice: 'This saved parameter is preserved. Its extension needs a UI component parameter definition to edit it.'
                };
            }
            this.previousAttribute = attribute;
            this.parameterOptions(preserve(definition.options || [], value));
            this.parameterLabel(definition.label || '');
            this.parameterNotice(definition.notice || '');
            // Unknown structured parameters remain untouched instead of being coerced to text.
            this.kind(definition.kind);
        }
    });
});
