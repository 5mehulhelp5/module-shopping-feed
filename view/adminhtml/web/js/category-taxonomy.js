/*jshint browser:true jquery:true*/
define(
    [
    "jquery",
    "jquery/ui",
    "domReady!"
    ], function ($) {
        "use strict";

        $.widget(
            "mageos.shoppingFeedCategoryTaxonomy", {
                options: {
                    someBlock: "#widget_viewed_item"
                },
                _create: function () {
                    var form = $('#edit_form').form();
                    form.on('beforeSubmit', this._beforeSubmitHandler);

                    this.element.find('.active input').on('change', this._taxonomyChangeHandler);
                    this.element.find('.active input.taxonomy,.active input.prod_type').on('change', this._autoFillChildrenHandler);

                    this.element.find('.active input.disabled').on('change', this._disabledHandler).trigger('change');
                    this.element.find('.active input.priority').on('change', this._validatePriorityHandler);

                    $('#taxonomy-collapse').on('click', this._collapseAllHandler);
                    $('#taxonomy-expand').on('click', this._expandAllHandler);
                    $('#taxonomy-enable').on('click', this._enableAllHandler);
                    $('#taxonomy-disable').on('click', this._disableAllHandler);

                    $('.row-expand').on('click', this._expandHandler);
                    $('.category_row:not(.level2)').hide();
                },
                _beforeSubmitHandler: function () {
                    $('#taxonomy_aggregated_values').val(JSON.stringify(taxonomy));
                },
                _taxonomyChangeHandler: function () {
                    var input = $(this);
                    var element = input.data('element');
                    var rowContainer = input.parents(".category_row");
                    var categoryId = rowContainer.data('category-id');

                    if (input.prop('type') == 'checkbox') {
                        var value = (input.prop('checked')) ? 1 : 0;
                    } else {
                        var value = input.val();
                    }

                    taxonomy[categoryId][element] = value;
                },
                _autoFillChildrenHandler: function () {
                    var input = $(this);
                    var rowContainer = input.parents(".category_row");
                    var parentPath = rowContainer.data('category-path');
                    var value = input.val();
                    var element = input.data('element');

                    var childElements = $('div[data-parent-category-path="' + parentPath + '"].active')
                    .find('input[data-element='+element+']');

                    if (childElements.length == 0) {
                        return;
                    }

                    if (childElements.val().length == 0) {
                        childElements.val(value).trigger('change');
                    }
                },
                _validatePriorityHandler: function () {

                    $('input.priority').css('color', 'black');
                    var prioritiesCount = [];

                    $('input.priority').each(
                        function () {
                            var priority = $(this).val();

                            if (priority in prioritiesCount) {
                                prioritiesCount[priority]++;
                            } else {
                                prioritiesCount[priority] = 1;
                            }
                        }
                    );

                    $.each(
                        prioritiesCount, function (priority, count) {
                            if (count <= 1) {
                                return;
                            }

                            $('input.priority').each(
                                function () {
                                    if (priority == parseInt($(this).val())) {
                                        $(this).css('color', 'red');
                                    }
                                }
                            );
                        }
                    );
                },
                _disabledHandler: function () {
                    var input = $(this);
                    var rowContainer = input.parents(".category_row");

                    if (input.prop('checked') && !rowContainer.hasClass('disabled')) {
                        rowContainer.find('input[type!=checkbox]').attr('disabled', false);
                    } else {
                        rowContainer.find('input[type!=checkbox]').attr('disabled', true);
                    }
                },
                _collapseAllHandler: function (event) {
                    $('.category_row:not(.level2)').hide();
                    return false;
                },
                _expandAllHandler: function (event) {
                    $('.category_row:not(.level2)').show();
                    return false;
                },
                _enableAllHandler: function (event) {
                    $('.active input.disabled').prop('checked', true).trigger('change');
                    return false;
                },
                _disableAllHandler: function (event) {
                    $('.active input.disabled').prop('checked', false).trigger('change');
                    return false;
                },
                _displayLoadingMask: function () {
                    $('body').loadingPopup();
                },
                _expandHandler: function (event) {
                    var input = $(this);
                    var rowContainer = input.parents(".category_row");

                    var parentPath = rowContainer.data('category-path');
                    $('div[data-parent-category-path="' + parentPath + '"]').toggle();
                    input.parent().toggleClass('expanded');
                    return false;
                }
            }
        );

        return $.mageos.shoppingFeedCategoryTaxonomy;
    }
);
