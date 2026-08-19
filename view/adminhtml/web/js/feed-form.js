/*jshint browser:true jquery:true*/
define(
    [
        "jquery",
        "mage/backend/tabs",
        'domReady!'
    ],
    function ($) {
        "use strict";

        $.widget(
            "mageos.shoppingFeedForm", {
                _create: function () {
                    $('#saveandcontinue, #save').on('click', this._preventDoubleSubmit);
                    $(document).on('click', '.ui-tabs-panel a', this._switchTabs);
                },
                _preventDoubleSubmit: function () {
                    var continueButton = $('#saveandcontinue'),
                    saveButton = $("#save"),
                    form = $('#edit_form');
                    continueButton.prop('disabled', true);
                    saveButton.prop('disabled', true);
                    if (!form.valid()) {
                        continueButton.prop('disabled', false);
                        saveButton.prop('disabled', false);
                    }
                },
                _switchTabs: function (e) {
                    if ($(this).data('tab-id') !== undefined) {
                        e.preventDefault();
                        var id = $(this).data('tab-id'),
                        switchToId = $('ul[data-ui-id="feed-tabs-tab-feed-tabs"] li').index($('ul[data-ui-id="feed-tabs-tab-feed-tabs"]').find(id).parent());
                        $('#feed_tabs').tabs({active: switchToId});
                    }
                }
            }
        );

        return $.mageos.shoppingFeedForm;
    }
);
