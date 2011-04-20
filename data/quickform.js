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
    while (srcIndex < this._keys.length) {
        var key = this._keys[srcIndex];
        if (qf.Map._hasKey(this._map, key)
            && !qf.Map._hasKey(seen, key)
        ) {
            this._keys[destIndex++] = key;
            seen[key] = true;
        }
        srcIndex++;
    }
    this._keys.length = destIndex;
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
 * Tests for specific events support
 *
 * Code "inspired" by jQuery, original technique from here:
 * http://perfectionkills.com/detecting-event-support-without-browser-sniffing/
 *
 * @type {Object}
 */
qf.events.test = (function() {
    var test = {
        submitBubbles: true,
        changeBubbles: true,
        focusinBubbles: false
    };
    var div = document.createElement('div');

    if (div.attachEvent) {
        for (var i in {'submit': true, 'change': true, 'focusin': true}) {
            var eventName   = 'on' + i;
            var isSupported = (eventName in div);
            if (!isSupported) {
                div.setAttribute(eventName, 'return;');
                isSupported = (typeof div[eventName] === 'function');
            }
            test[i + 'Bubbles'] = isSupported;
        }
    }
    return test;
})();

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
 * @param   {boolean}    capture
 */
qf.events.addListener = function(element, type, handler, capture)
{
    if (element.addEventListener) {
        element.addEventListener(type, handler, capture);
    } else {
        element.attachEvent('on' + type, handler);
    }
};

/**
 * A na&iuml;ve wrapper around removeEventListener() and detachEvent().
 *
 * @param   {Element}    element
 * @param   {String}     type
 * @param   {function()} handler
 * @param   {boolean}    capture
 */
qf.events.removeListener = function(element, type, handler, capture)
{
    if (element.removeEventListener) {
        element.removeEventListener(type, handler, capture);
    } else {
        element.detachEvent('on' + type, handler);
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
 * @name qf.classes
 * @namespace Functions for CSS classes handling
 */
qf.addNamespace('qf.classes');

/**
 * Adds a class or a list of classes to an element, without duplicating class names
 *
 * @param {Node} element            DOM node to add class(es) to
 * @param {string|string[]} name    Class name(s) to add
 */
qf.classes.add = function(element, name)
{
    if ('string' == qf.typeOf(name)) {
        name = name.split(/\\s+/);
    }
    if (!element.className) {
        element.className = name.join(' ');
    } else {
        var checkName = ' ' + element.className + ' ',
            newName   = element.className;
        for (var i = 0, len = name.length; i < len; i++) {
            if (name[i] && 0 > checkName.indexOf(' ' + name[i] + ' ')) {
                newName += ' ' + name[i];
            }
        }
        element.className = newName;
    }
};

/**
 * Removes a class or a list of classes from an element
 *
 * @param {Node} element            DOM node to remove class(es) from
 * @param {string|string[]} name    Class name(s) to remove
 */
qf.classes.remove = function(element, name)
{
    if (!element.className) {
        return;
    }
    if ('string' == qf.typeOf(name)) {
        name = name.split(/\\s+/);
    }
    var className = (' ' + element.className + ' ').replace(/[\n\t\r]/g, ' ');
    for (var i = 0, len = name.length; i < len; i++) {
        if (name[i]) {
            className = className.replace(' ' + name[i] + ' ', ' ');
        }
    }
    element.className = className.replace(/^\s+/, '').replace(/\s+$/, '');
};

/**
 * Checks whether a given element has a given class
 *
 * @param   {Node} element  DOM node to check
 * @param   {string} name   Class name to check for
 * @returns {boolean}
 */
qf.classes.has = function(element, name)
{
    if (-1 < (' ' + element.className + ' ').replace(/[\n\t\r]/g, ' ').indexOf(' ' + name + ' ')) {
        return true;
    }
    return false;
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

    for (var i = 0, rule; rule = this.rules[i]; i++) {
        if (typeof rule.triggers != 'undefined') {
            if (qf.events.test.changeBubbles) {
                qf.events.addListener(form, 'change', qf.Validator.liveHandler, true);

            } else {
                // This is IE with change event not bubbling... We don't
                // terribly need an onchange event here, only an event that
                // fires sometime around onchange. Therefore no checks whether
                // a value actually *changed*
                qf.events.addListener(form, 'click', function (event) {
                    event  = qf.events.fixEvent(event);
                    var el = event.target;
                    if ('select' == el.nodeName.toLowerCase() 
                        || 'input' == el.nodeName.toLowerCase() 
                         && ('checkbox' == el.type || 'radio' == el.type) 
                    ) {
                        qf.Validator.liveHandler(event);
                    }
                });
                qf.events.addListener(form, 'keydown', function (event) {
                    event  = qf.events.fixEvent(event);
                    var el = event.target, type = ('type' in el)? el.type: '';
                    if ((13 == event.keyCode && 'textarea' != el.nodeName.toLowerCase())
                        || (32 == event.keyCode && ('checkbox' == type || 'radio' == type))
                        || 'select-multiple' == type
                    ) {
                        qf.Validator.liveHandler(event);
                    }
                });
            }

            if (qf.events.test.focusinBubbles) {
                qf.events.addListener(form, 'focusout', qf.Validator.liveHandler, true);
            } else {
                qf.events.addListener(form, 'blur', qf.Validator.liveHandler, true);
            }
            break;
        }
    }
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
 * Event handler for form's onblur and onchange events
 * @param {Event} event
 */
qf.Validator.liveHandler = function (event)
{
    event    = qf.events.fixEvent(event);
    var form = event.target.form;
    if (form.validator) {
        form.validator.runLive(event);
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
qf.Validator.prototype.onStart = function(form) 
{
    this._clearErrors(form);
};

/**
 * Called on setting the element error
 *
 * @param {string} elementId ID attribute of an element
 * @param {string} errorMessage
 * @deprecated Use onFieldError() instead
 */
qf.Validator.prototype.onError = function(elementId, errorMessage)
{
    this.onFieldError(elementId, errorMessage);
};

/**
 * Called on successfully validating the form
 * @deprecated Use onFormValid() instead
 */
qf.Validator.prototype.onValid = function()
{
    this.onFormValid();
};

/**
 * Called on failed validation
 * @deprecated Use onFormError() instead
 */
qf.Validator.prototype.onInvalid = function()
{
    this.onFormError();
};

/**
 * Called on setting the element error
 *
 * @param {string} elementId ID attribute of an element
 * @param {string} errorMessage
 */
qf.Validator.prototype.onFieldError = function(elementId, errorMessage)
{
    var parent = this._clearValidationStatus(elementId);
    qf.classes.add(parent, 'error');

    var error = document.createElement('span');
    error.className = 'error';
    error.appendChild(document.createTextNode(errorMessage));
    error.appendChild(document.createElement('br'));
    if ('fieldset' != parent.nodeName.toLowerCase()) {
        parent.insertBefore(error, parent.firstChild);
    } else {
        // error span should be inserted *after* legend, IE will render it before fieldset otherwise
        var legends = parent.getElementsByTagName('legend');
        if (0 == legends.length) {
            parent.insertBefore(error, parent.firstChild);
        } else {
            legends[legends.length - 1].parentNode.insertBefore(error, legends[legends.length - 1].nextSibling);
        }
    }
};

/**
 * Called on successfully validating the element
 *
 * @param {string} elementId
 */
qf.Validator.prototype.onFieldValid = function(elementId)
{
    var parent = this._clearValidationStatus(elementId);
    qf.classes.add(parent, 'valid');
};

/**
 * Called on successfully validating the form
 */
qf.Validator.prototype.onFormValid = function() {};

/**
 * Called on failed validation
 */
qf.Validator.prototype.onFormError = function()
{
    /*alert(this.msgPrefix + '\n - ' + this.errors.getValues().join('\n - ') + '\n' + this.msgPostfix);*/
};

/**
 * Clears validation status and error message of a given element
 * 
 * @param   {string} elementId
 * @returns {Node}              Parent element that gets 'error' / 'valid'
 *                              classes applied
 * @private
 */
qf.Validator.prototype._clearValidationStatus = function(elementId)
{
    var el = document.getElementById(elementId), parent = el;
    while (!qf.classes.has(parent, 'element') && 'fieldset' != parent.nodeName.toLowerCase()) {
        parent = parent.parentNode;
    }
    qf.classes.remove(parent, ['error', 'valid']);

    this._clearErrors(parent);

    return parent;
};

/**
 * Removes <span> elements with "error" class that are children of a given element 
 * 
 * @param   {Node} element
 * @private
 */
qf.Validator.prototype._clearErrors = function(element)
{
    var spans = element.getElementsByTagName('span');
    for (var i = 0, span; span = spans[i]; i++) {
        if (qf.classes.has(span, 'error')) {
            span.parentNode.removeChild(span);
        }
    }
};

/**
 * Removes error messages from owner element(s) of a given rule and chained rules
 *
 * @param {Array} rule
 * @private
 */
qf.Validator.prototype._removeRelatedErrors = function(rule)
{
    if (this.errors.hasKey(rule.owner)) {
        this.errors.remove(rule.owner);
        this._clearValidationStatus(rule.owner);
    }
    if (typeof rule.chained != 'undefined') {
        for (var i = 0; i < rule.chained.length; i++) {
            for (var j = 0; j < rule.chained[i].length; j++) {
                this._removeRelatedErrors(rule.chained[i][j]);
            }
        }
    }
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
        this.onFormValid();
        return true;

    } else {
        this.onFormError();
        return false;
    }
};

/**
 * Performs live validation of an element and related ones
 *
 * @param {Event} event     Event triggering the validation
 */
qf.Validator.prototype.runLive = function(event)
{
    var testId   = ' ' + event.target.id + ' ',
        ruleHash = new qf.Map(),
        length   = -1;

    // first: find all rules "related" to the given element, clear their error messages
    while (ruleHash.length() > length) {
        length = ruleHash.length();
        for (var i = 0, rule; rule = this.rules[i]; i++) {
            if (typeof rule.triggers == 'undefined' || ruleHash.hasKey(i)) {
                continue;
            }
            for (var j = 0, trigger; trigger = rule.triggers[j]; j++) {
                if (-1 < testId.indexOf(' ' + trigger + ' ')) {
                    ruleHash.set(i, true);
                    this._removeRelatedErrors(rule);
                    testId += rule.triggers.join(' ') + ' ';
                    break;
                }
            }
        }
    }

    // second: run all "related" rules
    for (i = 0; rule = this.rules[i]; i++) {
        if (!ruleHash.hasKey(i) || this.errors.hasKey(rule.owner)) {
            continue;
        }
        this.validate(rule);
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
        this.onFieldError(rule.owner, rule.message);
    } else if (!this.errors.hasKey(rule.owner)) {
        this.onFieldValid(rule.owner);
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
