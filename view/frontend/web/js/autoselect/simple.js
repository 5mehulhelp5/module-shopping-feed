/*jshint browser:true jquery:true*/
define(
    [
        "jquery",
        "jquery-ui-modules/widget",
        "jquery/jquery.parsequery"
    ],
    function ($) {
        "use strict";

        $.widget(
            "mageos.shoppingFeedAutoSelectSimple", {
                values: {},
                changed: false,
                _create: function () {
                    if ($('.product-options-wrapper').length) {
                        // Override defaults with URL query parameters and/or inputs values
                        this._overrideDefaults();
                    }
                },

                /**
                 * Override default options values settings with either URL query parameters or
                 * initialized inputs values.
                 *
                 * @private
                 */
                _overrideDefaults: function () {
                    var hashIndex = window.location.href.indexOf('#');

                    if (hashIndex !== -1) {
                        this._parseQueryParams(window.location.href.substr(hashIndex + 1));
                    }

                    this._selectValuesByAttribute();

                    if (this.changed == true) {
                        setTimeout(
                            function () {
                                $('.product-custom-option').trigger('change');
                            }, 300
                        );
                    }
                },
                /**
                 * Parse query parameters from a query string and set options values based on the
                 * key value pairs of the parameters.
                 *
                 * @param   {*} queryString - URL query string containing query parameters.
                 * @private
                 */
                _parseQueryParams: function (queryString) {
                    var queryParams;
                    try {
                        queryParams = $.parseQuery({ query: queryString });
                    } catch (error) {
                        // The platform parser throws on malformed URL encoding.
                        return;
                    }

                    $.each(
                        queryParams, $.proxy(
                            function (key, value) {
                                this.values[key] = value;
                            }, this
                        )
                    );
                },

                /**
                 * Select options with values based on each element's attribute identifier.
                 *
                 * @private
                 */
                _selectValuesByAttribute: function () {
                    $.each(
                        this.values, $.proxy(
                            function (attributeId, optionId) {
                                if (!/^\d+$/.test(attributeId) || typeof optionId !== 'string' || !/^\d+$/.test(optionId)) {
                                    return;
                                }

                                var element = $(document.getElementById('select_' + attributeId));
                                var option;

                                if (element.length) {
                                    option = element.find('option').filter(function () {
                                        return this.value === optionId;
                                    });
                                    if (option.length) {
                                        option.prop('selected', true);
                                        this.changed = true;
                                    }
                                    return;
                                }

                                element = $(document.getElementById('options-' + attributeId + '-list'));

                                if (element.length) {
                                    option = element.find('input').filter(function () {
                                        return this.value === optionId;
                                    });
                                    if (option.length) {
                                        if (option.prop("type") == 'radio' || option.prop("type") == 'checkbox') {
                                            option.prop('checked', true);
                                            this.changed = true;
                                            return;
                                        }
                                    }
                                }
                            }, this
                        )
                    );
                },
            }
        );

        return $.mageos.shoppingFeedAutoSelectSimple;
    }
);
