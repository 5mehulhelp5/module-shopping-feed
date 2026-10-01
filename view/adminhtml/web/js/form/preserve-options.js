define([], function () {
    'use strict';
    return function (options, values) {
        var result = options.slice(), known = Object.create(null);
        function index(items) {
            items.forEach(function (option) {
                if (Array.isArray(option.value)) {
                    index(option.value);
                } else {
                    known[String(option.value)] = true;
                }
            });
        }
        index(result);
        (Array.isArray(values) ? values : [values]).forEach(function (value) {
            if (value !== null && value !== undefined && !known[String(value)]) {
                result.push({value: value, label: String(value) || ' '});
                known[String(value)] = true;
            }
        });
        return result;
    };
});
