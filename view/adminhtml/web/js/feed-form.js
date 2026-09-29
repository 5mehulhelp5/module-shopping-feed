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
                    var id = $(this).data('tab-id') || $(this).attr('href'),
                        switchToId;
                    if (typeof id === 'string' && /^#feed_tabs_[a-z_]+$/.test(id)) {
                        switchToId = $('ul[data-ui-id="feed-tabs-tab-feed-tabs"] li').index($('ul[data-ui-id="feed-tabs-tab-feed-tabs"]').find(id).closest('li'));
                        if (switchToId >= 0) {
                            e.preventDefault();
                            $('#feed_tabs').tabs({active: switchToId});
                        }
                    }
                }
            }
        );

        return $.mageos.shoppingFeedForm;
    }
);
