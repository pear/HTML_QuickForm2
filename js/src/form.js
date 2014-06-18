/**
 * @namespace Helper functions for working with form values
 */
qf.form = (function() {
    /**
     * Gets the value of select-multiple element.
     *
     * @param   {Element}   el  The element
     * @returns {String[]}
     * @private
     */
    function _getSelectMultipleValue(el)
    {
        var values = [];
        for (var i = 0; i < el.options.length; i++) {
            if (el.options[i].selected) {
                values.push(el.options[i].value);
            }
        }
        return values;
    }

    /**
     * Sets the value of a select-one element.
     * @param   {Element} el
     * @param   {String}  value
     * @private
     */
    function _setSelectSingleValue(el, value)
    {
        el.selectedIndex = -1;
        for (var option, i = 0; option = el.options[i]; i++) {
            if (option.value == value) {
                option.selected = true;
                return;
            }
        }
    }

    /**
     * Sets the value of a select-multiple element.
     * @param   {Element} el
     * @param   {String|String[]} value
     * @private
     */
    function _setSelectMultipleValue(el, value)
    {
        if ('array' != qf.typeOf(value)) {
            value = [value];
        }
        for (var option, i = 0; option = el.options[i]; i++) {
            option.selected = false;
            for (var j = 0, l = value.length; j < l; j++) {
                if (option.value == value[j]) {
                    option.selected = true;
                }
            }
        }
    }

    return {
        /**
         * Gets the value of a form element.
         *
         * @param   {string|Element} el
         * @returns {string|string[]|null}
         */
        getValue: function(el)
        {
            if (typeof el == 'string') {
                el = document.getElementById(el);
            }
            if (!el || !('type' in el)) {
                return null;
            }
            switch (el.type.toLowerCase()) {
                case 'checkbox':
                case 'radio':
                    return el.checked? el.value: null;
                case 'select-one':
                    var index = el.selectedIndex;
                    return -1 == index? null: el.options[index].value;
                case 'select-multiple':
                    return _getSelectMultipleValue(el);
                default:
                    return (typeof el.value == 'undefined')? null: el.value;
            }
        },

        /**
         * Gets the submit value of a form element. It will return null for disabled
         * elements and elements that cannot have submit values (buttons, reset controls).
         *
         * @param   {string|Element} el
         * @returns {string|string[]|null}
         */
        getSubmitValue: function(el)
        {
            if (typeof el == 'string') {
                el = document.getElementById(el);
            }
            if (!el || !('type' in el) || el.disabled) {
                return null;
            }
            switch (el.type.toLowerCase()) {
                case 'reset':
                case 'button':
                    return null;
                default:
                    return qf.form.getValue(el);
            }
        },

        /**
         * Gets the submit values of a container.
         *
         * @param   [...] This accepts a variable number of arguments, that are either
         *      strings (considered element ID attributes), objects {name: element name,
         *      value: element value} or instances of qf.Map, representing the contained elements
         * @returns qf.Map
         */
        getContainerSubmitValue: function()
        {
            var k, v, map = new qf.Map();
            for (var i = 0; i < arguments.length; i++) {
                if (arguments[i] instanceof qf.Map) {
                    map.merge(arguments[i], qf.Map.mergeArrayConcat);
                } else {
                    if ('object' == qf.typeOf(arguments[i])) {
                        k = arguments[i].name;
                        v = arguments[i].value;
                    } else {
                        k = document.getElementById(arguments[i]).name;
                        v = qf.form.getSubmitValue(arguments[i]);
                    }
                    if (null !== v) {
                        var valueObj = {};
                        valueObj[k] = v;
                        map.merge(valueObj, qf.Map.mergeArrayConcat);
                    }
                }
            }
            return map;
        },

        /**
         * Sets the value of a form element.
         * @param   {String|Element} el
         * @param   {*} value
         */
        setValue: function(el, value)
        {
            if (typeof el == 'string') {
                el = document.getElementById(el);
            }
            if (!el || !('type' in el)) {
                return;
            }
            switch (el.type.toLowerCase()) {
                case 'checkbox':
                case 'radio':
                    el.checked = !!value;
                    break;
                case 'select-one':
                    _setSelectSingleValue(el, value);
                    break;
                case 'select-multiple':
                    _setSelectMultipleValue(el, value);
                    break;
                default:
                    el.value = value;
            }
        }
    };
})();


/**
 * Alias for qf.form.getSubmitValue
 * @type {Function}
 */
qf.$v = qf.form.getSubmitValue;

/**
 * Alias for qf.form.getContainerSubmitValue
 * @type {Function}
 */
qf.$cv = qf.form.getContainerSubmitValue;

