/**
 * @namespace Client-side implementations of Rules that are a bit too complex to inline
 */
qf.rules = qf.rules || {};

// NB: we do not overwrite qf.rules namespace because some custom rules may be already added

/**
 * Returns true if all the given callbacks return true, false otherwise.
 *
 * Client-side implementation of HTML_QuickForm2_Rule_Each, consult PHPDoc
 * description there.
 *
 * @param   {function()[]} callbacks
 * @returns {boolean}
 */
qf.rules.each = function(callbacks)
{
    for (var i = 0; i < callbacks.length; i++) {
        if (!callbacks[i]()) {
            return false;
        }
    }
    return true;
};

/**
 * Tests that a given value is empty.
 *
 * A scalar value is empty if it either null, undefined or an empty string. An
 * array is empty if it contains only empty values.
 *
 * @param   {*} value
 * @returns {boolean}
 */
qf.rules.empty = function(value)
{
    switch (qf.typeOf(value)) {
        case 'array':
            for (var i = 0; i < value.length; i++) {
                if (!qf.rules.empty(value[i])) {
                    return false;
                }
            }
            return true;
        case 'undefined':
        case 'null':
            return true;
        default:
            return '' == value;
    }
};

/**
 * Tests that a given value is not empty.
 *
 * A scalar value is not empty if it isn't either null, undefined or an empty
 * string. A container is not empty if it contains at least 'minValid'
 * nonempty values.
 *
 * @param   {*} value May usually be a string, an Array or an instance of qf.Map
 * @param   {number} minValid Minimum number of nonempty values in Array or
 *      qf.Map, defaults to 1
 * @returns {boolean}
 */
qf.rules.nonempty = function(value, minValid)
{
    var i, valid = 0;

    if ('array' == qf.typeOf(value)) {
        for (i = 0; i < value.length; i++) {
            if (qf.rules.nonempty(value[i], 1)) {
                valid++;
            }
        }
        return valid >= minValid;

    } else if (value instanceof qf.Map) {
        var values = value.getValues();
        // corner case: group of checkboxes or something similar
        if (1 == value.length()) {
            var k = value.getKeys()[0], v = values[0];
            if ('[]' == k.slice(-2) && 'array' == qf.typeOf(v)) {
                return qf.rules.nonempty(v, minValid);
            }
        }
        for (i = 0; i < values.length; i++) {
            if (qf.rules.nonempty(values[i], 1)) {
                valid++;
            }
        }
        return valid >= minValid;

    } else {
        // in Javascript (null != '') is true!
        return '' != value && 'undefined' != qf.typeOf(value) && 'null' != qf.typeOf(value);
    }
};

/**
 * Tests that a given value is in a commonly used email address format.
 *
 * @param   {*} value The email address to test
 * @returns {boolean}
 */
qf.rules.email = function(value)
{
    if (qf.rules.empty(value)) {
        return true;
    }
    var parts = value.split("@");
    if (parts.length != 2) {
        return false;
    }
    if (parts[0].length > 64) {
        return false;
    }
    if (parts[1].length < 4 || parts[1].length > 255) {
        return false;
    }
    var locals = parts[0].split(".");
    for (var i = 0; i < locals.length; i++) {
        if (!(/^[A-Za-z0-9!#$%&'*+\/=?^_`{|}~-]+$/.test(locals[i]))) {
            return false;
        }
    }
    return /^([a-z0-9][a-z0-9\-]*[a-z0-9]|[a-z0-9])(\.([a-z0-9][a-z0-9\-]*[a-z0-9]|[a-z0-9])){0,10}\.([a-z]{2,}){1}$/i.test(parts[1]);
};
