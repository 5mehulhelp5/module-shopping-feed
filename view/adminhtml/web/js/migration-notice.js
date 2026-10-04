define(['jquery'], function ($) {
    'use strict';

    return function (config, element) {
        $(element).find('[data-action="dismiss-migration-notice"]').on('click', function () {
            $(element).hide();
        });
    };
});
