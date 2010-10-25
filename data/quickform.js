/**
 * HTML_QuickForm2: JS library used for client-side validation support 
 *
 * $Id$
 */

/**
 * @namespace Base namespace for QuickForm, we no longer define our stuff in global namespace
 */
var qf = qf || {};

/**
 * Enhanced version of typeof operator.
 *
 * Returns "null" for null values and "array" for arrays. Handles edge cases
 * like objects passed across browser windows, etc. Borrowed from closure library.
 *
 * @param   {*} value   The value to get the type of
 * @returns {string}    Type name 
 */
qf.typeOf = function(value) {
    var s = typeof value;
    if ('function' == s && 'undefined' == typeof value.call) {
        return 'object';
    } else if ('object' == s) {
        if (!value) {
            return 'null';

        } else {
            if (value instanceof Array
                || (!(value instanceof Object)
                    && '[object Array]' == Object.prototype.toString.call(value)
                    && 'number' == typeof value.length
                    && 'undefined' != typeof value.splice
                    && 'undefined' != typeof value.propertyIsEnumerable
                    && !value.propertyIsEnumerable('splice'))
            ) {
                return 'array';
            }
            if (!(value instanceof Object)
                && ('[object Function]' == Object.prototype.toString.call(value)
                    || 'undefined' != typeof value.call
                    && 'undefined' != typeof value.propertyIsEnumerable
                    && !value.propertyIsEnumerable('call'))
            ) {
                return 'function';
            }
        }
    }
    return s;
};

/**
 * Builds an object structure for the provided namespace path.
 *
 * Ensures that names that already exist are not overwritten. For
 * example:
 * <code>
 * "a.b.c" -> a = {};a.b={};a.b.c={};
 * </code>
 * Borrowed from closure library.
 * 
 * @param   {string}    ns name of the object that this file defines.
 */
qf.addNamespace = function(ns) {
    var parts = ns.split('.');
    var cur   = window;

    for (var part; parts.length && (part = parts.shift());) {
        if (cur[part]) {
            cur = cur[part];
        } else {
            cur = cur[part] = {};
        }
    }
};


/**
 * Class for Hash Map datastructure.
 *
 * Used for storing container values and validation errors, mostly borrowed
 * from closure library.
 *
 * @param   {Object}    [inMap] Object or qf.Map instance to initialize the map
 * @constructor
 */
qf.Map = function(inMap)
{
   /**
    * Actual JS Object used to store the map
    * @type {Object}
    * @private
    */
    this._map   = {};

   /**
    * An array of map keys
    * @type {String[]}
    * @private
    */
    this._keys  = [];

   /**
    * Number of key-value pairs in the map
    * @type {number}
    * @private
    */
    this._count = 0;

    if (inMap) {
        this.merge(inMap);
    }
};

/**
 * Wrapper function for hasOwnProperty
 * @param   {Object}    obj
 * @param   {*}         key
 * @returns {boolean}
 * @private
 */
qf.Map._hasKey = function (obj, key)
{
    return Object.prototype.hasOwnProperty.call(obj, key);
};

/**
 * Whether the map has the given key
 * @param   {*}     key
 * @returns {boolean}
 */
qf.Map.prototype.hasKey = function(key)
{
    return qf.Map._hasKey(this._map, key);
};

/**
 * Returns the number of key-value pairs in the Map
 * @returns {number}
 */
qf.Map.prototype.length = function()
{
    return this._count;
};

/**
 * Returns the values of the Map
 * @returns {Array}
 */
qf.Map.prototype.getValues = function()
{
    this._cleanupKeys();

    var ret = [];
    for (var i = 0; i < this._keys.length; i++) {
        ret.push(this._map[this._keys[i]]);
    }
    return ret;
};

/**
 * Returns the keys of the Map
 * @returns {String[]}
 */
qf.Map.prototype.getKeys = function()
{
    this._cleanupKeys();
    return (this._keys.concat());
};

/**
 * Returns whether the Map is empty
 * @returns {boolean}
 */
qf.Map.prototype.isEmpty = function()
{
    return 0 == this._count;
};

/**
 * Removes all key-value pairs from the map 
 */
qf.Map.prototype.clear = function()
{
    this._map         = {};
    this._keys.length = 0;
    this._count       = 0;
};

/**
 * Removes a key-value pair from the Map
 * @param   {*}         key The key to remove
 * @returns {boolean}   Whether the pair was removed
 */
qf.Map.prototype.remove = function(key)
{
    if (!qf.Map._hasKey(this._map, key)) {
        return false;
    }

    delete this._map[key];
    this._count--;
    if (this._keys.length > this._count * 2) {
        this._cleanupKeys();
    }
    return true;
};

/**
 * Returns the value for the given key
 * @param   {*} key The key to look for
 * @param   {*} [defaultVal] The value to return if the key is not in the Map
 * @returns {*}
 */
qf.Map.prototype.get = function(key, defaultVal)
{
    if (qf.Map._hasKey(this._map, key)) {
        return this._map[key];
    }
    return defaultVal;
};

/**
 * Adds a key-value pair to the Map
 * @param {*} key
 * @param {*} value
 */
qf.Map.prototype.set = function(key, value)
{
    if (!qf.Map._hasKey(this._map, key)) {
        this._count++;
        this._keys.push(key);
    }
    this._map[key] = value;
};

/**
 * Merges key-value pairs from another Object or Map
 * @param {Object} map
 * @param {function(*, *)} [mergeFn] Optional function to call on values if 
 *      both maps have the same key. By default a value from the map being
 *      merged will be stored under that key.
 */
qf.Map.prototype.merge = function(map, mergeFn)
{
    var keys, values, i = 0;
    if (map instanceof qf.Map) {
        keys   = map.getKeys();
        values = map.getValues();
    } else {
        keys   = [];
        values = [];
        for (var key in map) {
            keys[i]     = key;
            values[i++] = map[key];
        }
    }

    var fn = mergeFn || qf.Map.mergeReplace;

    for (i = 0; i < keys.length; i++) {
        if (!this.hasKey(keys[i])) {
            this.set(keys[i], values[i]);
        } else {
            this.set(keys[i], fn(this.get(keys[i]), values[i]));
        }
    }
};

/**
 * Removes keys that are no longer in the map from the _keys array
 * @private
 */
qf.Map.prototype._cleanupKeys = function()
{
    if (this._count == this._keys.length) {
        return;
    }
    var srcIndex  = 0;
    var destIndex = 0;
    var seen      = {};
    while (srcIndex < this.keys_.length) {
        var key = this.keys_[srcIndex];
        if (qf.Map._hasKey(this._map, key)
            && !qf.Map._hasKey(seen, key)
        ) {
            this.keys_[destIndex++] = key;
            seen[key] = true;
        }
        srcIndex++;
    }
    this.keys_.length = destIndex;
};

/**
 * Callback for merge(), forces to use second value.
 * 
 * This makes Map.merge() behave like PHP's array_merge() function
 * 
 * @param   {*} a Original value in map
 * @param   {*} b Value in the map being merged
 * @returns {*} second value
 */
qf.Map.mergeReplace = function(a, b)
{
    return b;
};

/**
 * Callback for merge(), forces to use first value.
 * 
 * This makes Map.merge() behave like PHP's + operator for arrays
 * 
 * @param   {*} a Original value in map
 * @param   {*} b Value in the map being merged
 * @returns {*} first value
 */
qf.Map.mergeKeep = function(a, b)
{
    return a;
};

/**
 * Callback for merge(), concatenates values.
 *
 * If the values are not arrays, they are first converted to ones. 
 * 
 * This callback makes Map.merge() behave somewhat like PHP's array_merge_recursive()
 *
 * @param   {*} a Original value in map
 * @param   {*} b Value in the map being merged
 * @returns {Array} array containing both values
 */
qf.Map.mergeArrayConcat = function(a, b)
{
    if ('array' != qf.typeOf(a)) {
        a = [a];
    }
    if ('array' != qf.typeOf(b)) {
        b = [b];
    }
    return a.concat(b);
};


/**
 * @name qf.form
 * @namespace Helper functions for working with form values
 */
qf.addNamespace('qf.form');

/**
 * Gets the value of select-multiple element.
 *
 * @param   {Element}   el  The element
 * @returns {String[]}
 * @private
 */
qf.form._getSelectMultipleValue = function(el)
{
    var values = [];
    for (var i = 0; i < el.options.length; i++) {
        if (el.options[i].selected) {
            values.push(el.options[i].value);
        }
    }
    return values;
};

/**
 * Gets the value of a form element.
 * 
 * @param   {string|Element} el
 * @returns {string|string[]|null}
 */
qf.form.getValue = function(el)
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
            return qf.form._getSelectMultipleValue(el);
        default:
            return (typeof el.value == 'undefined')? null: el.value;
    }
};

/**
 * Gets the submit value of a form element. It will return null for disabled
 * elements and elements that cannot have submit values (buttons, reset controls).
 *
 * @param   {string|Element} el
 * @returns {string|string[]|null}
 */
qf.form.getSubmitValue = function(el)
{
    if (typeof el == 'string') {
        el = document.getElementById(el);
    }
    if (!el || (!'type' in el) || el.disabled) {
        return null;
    }
    switch (el.type.toLowerCase()) {
        case 'reset':
        case 'button':
            return null;
        default:
            return qf.form.getValue(el);
    }
};

/**
 * Alias for qf.form.getSubmitValue
 * @type {Function}
 */
qf.$v = qf.form.getSubmitValue;

/**
 * Gets the submit values of a container.
 *
 * @param   [...] This accepts a variable number of arguments, that are either
 *      strings (considered element ID attributes), objects {name: element name,
 *      value: element value} or instances of qf.Map, representing the contained elements 
 * @returns qf.Map
 */
qf.form.getContainerSubmitValue = function()
{
    var map = new qf.Map();
    for (var i = 0; i < arguments.length; i++) {
        if (arguments[i] instanceof qf.Map) {
            map.merge(arguments[i], qf.Map.mergeArrayConcat);
        } else {
            if ('object' == qf.typeOf(arguments[i])) {
                var k  = arguments[i].name;
                var v  = arguments[i].value;
            } else {
                var k = document.getElementById(arguments[i]).name;
                var v = qf.form.getSubmitValue(arguments[i]);
            }
            if (null !== v) {
                var valueObj = {};
                valueObj[k] = v;
                map.merge(valueObj, qf.Map.mergeArrayConcat);
            }
        }
    }
    return map;
};

/**
 * Alisas for qf.form.getContainerSubmitValue
 * @type {Function}
 */
qf.$cv = qf.form.getContainerSubmitValue;

/**
 * Sets the value of a select-one element.
 * @param   {Element} el
 * @param   {String}  value
 * @private
 */
qf.form._setSelectSingleValue = function(el, value)
{
    el.selectedIndex = -1;
    for (var option, i = 0; option = el.options[i]; i++) {
        if (option.value == value) {
            option.selected = true;
            return;
        }
    }
};

/**
 * Sets the value of a select-multiple element.
 * @param   {Element} el
 * @param   {String|String[]} value
 * @private
 */
qf.form._setSelectMultipleValue = function(el, value)
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
};

/**
 * Sets the value of a form element.
 * @param   {String|Element} el
 * @param   {*} value
 */
qf.form.setValue = function(el, value)
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
            qf.form._setSelectSingleValue(el, value);
            break;
        case 'select-multiple':
            qf.form._setSelectMultipleValue(el, value);
            break;
        default:
            el.value = value;
    }
};

/**
 * @name qf.events
 * @namespace Minor fixes to JS events to make them a bit more crossbrowser
 */
qf.addNamespace('qf.events');

/**
 * A na&iuml;ve wrapper around addEventListener() and attachEvent().
 *
 * QuickForm does not need a complete framework for crossbrowser event handling
 * and does not provide one. Use one of a zillion javascript libraries if you
 * need such a framework in your application.
 *
 * @param   {Element}    element
 * @param   {String}     type
 * @param   {function()} handler
 */
qf.events.addListener = function(element, type, handler)
{
    if (element.addEventListener) {
        element.addEventListener(type, handler, false);
    } else {
        element.attachEvent('on' + type, handler);
    }
};

/**
 * Adds some standard fields to (IE's) event object.
 *
 * This is intended to be used in event handlers like this:
 * <code>
 * function handler(event) {
 *     event = qf.events.fixEvent(event);
 *     ...
 * }
 * </code>
 *
 * @param   {Event} [e]
 * @returns {Event}
 */
qf.events.fixEvent = function(e)
{
    e = e || window.event;

    e.preventDefault  = e.preventDefault || function() { this.returnValue = false; };
    e.stopPropagation = e.stopPropagation || function() { this.cancelBubble = true; };

    if (!e.target) {
        e.target = e.srcElement;
    }

    if (!e.relatedTarget && e.fromElement) {
        e.relatedTarget = e.fromElement == e.target ? e.toElement : e.fromElement;
    }

    if (e.pageX == null && e.clientX != null) {
        var html = document.documentElement;
        var body = document.body;
        e.pageX = e.clientX + (html && html.scrollLeft || body && body.scrollLeft || 0) - (html.clientLeft || 0);
        e.pageY = e.clientY + (html && html.scrollTop || body && body.scrollTop || 0) - (html.clientTop || 0);
    }

    if (!e.which && e.button) {
        e.which = e.button & 1 ? 1 : (e.button & 2 ? 3 : (e.button & 4 ? 2 : 0));
    }

    return e;
};

/**
 * Form validator, attaches onsubmit handler that runs the given rules.
 *
 * @param {HTMLFormElement} form
 * @param {Array} rules
 * @constructor
 */
qf.Validator = function(form, rules)
{
   /**
    * Validation rules
    * @type {Object[]}
    */
    this.rules  = rules || [];

   /**
    * Form errors, keyed by element's ID attribute
    * @type {qf.Map}
    */
    this.errors = new qf.Map();

    form.validator = this;
    qf.events.addListener(form, 'submit', qf.Validator.submitHandler);
};

/**
 * Event handler for form's onsubmit events. 
 * @param {Event} event
 */
qf.Validator.submitHandler = function(event)
{
    event    = qf.events.fixEvent(event);
    var form = event.target;
    if (form.validator && !form.validator.run(form)) {
        event.preventDefault();
    }
};

/**
 * Message prefix in alert in case of failed validation
 * @type {String}
 */
qf.Validator.prototype.msgPrefix  = 'Invalid information entered:';

/**
 * Message postfix in alert in case of failed validation
 * @type {String}
 */
qf.Validator.prototype.msgPostfix = 'Please correct these fields.';

/**
 * Called before starting the validation. May be used e.g. to clear the errors from form elements.
 * @param {HTMLFormElement} form The form being validated currently
 */
qf.Validator.prototype.onStart = function(form) {};

/**
 * Called on setting the element error
 *
 * @param {string} elementId ID attribute of an element
 * @param {string} errorMessage
 */
qf.Validator.prototype.onError = function(elementId, errorMessage) {};

/**
 * Called on successfully validating the form
 */
qf.Validator.prototype.onValid = function() {};

/**
 * Called on failed validation
 */
qf.Validator.prototype.onInvalid = function()
{
    alert(this.msgPrefix + '\n - ' + this.errors.getValues().join('\n - ') + '\n' + this.msgPostfix);
};

/**
 * Performs validation using the stored rules
 * @param   {HTMLFormElement} form    The form being validated
 * @returns {boolean}
 */
qf.Validator.prototype.run = function(form)
{
    this.onStart(form);

    this.errors.clear();
    for (var i = 0, rule; rule = this.rules[i]; i++) {
        if (this.errors.hasKey(rule.owner)) {
            continue;
        }
        this.validate(rule);
    }

    if (this.errors.isEmpty()) {
        this.onValid();
        return true;

    } else {
        this.onInvalid();
        return false;
    }
};

/**
 * Performs validation, sets the element's error if validation fails.
 *
 * @param   {Object} rule Validation rule, maybe with chained rules
 * @returns {boolean}
 */
qf.Validator.prototype.validate = function(rule)
{
    var globalValid, localValid = rule.callback.call(this);

    if (typeof rule.chained == 'undefined') {
        globalValid = localValid;

    } else {
        globalValid = false;
        for (var i = 0; i < rule.chained.length; i++) {
            for (var j = 0; j < rule.chained[i].length; j++) {
                localValid = localValid && this.validate(rule.chained[i][j]);
                if (!localValid) {
                    break;
                }
            }
            globalValid = globalValid || localValid;
            if (globalValid) {
                break;
            }
            localValid = true;
        }
    }

    if (!globalValid && rule.message && !this.errors.hasKey(rule.owner)) {
        this.errors.set(rule.owner, rule.message);
        this.onError(rule.owner, rule.message);
    }

    return globalValid;
};

/**
 * @name qf.rules
 * @namespace Client-side implementations of Rules that are a bit too complex to inline
 */
qf.addNamespace('qf.rules');

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
