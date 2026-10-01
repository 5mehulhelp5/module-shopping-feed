define(['./select'], function (Select) {
    'use strict';
    return Select.extend({
        defaults: {listens: {dependency: 'updateVisibility'}},
        updateVisibility: function (value) { this.visible(String(value) === this.showWhen); }
    });
});
