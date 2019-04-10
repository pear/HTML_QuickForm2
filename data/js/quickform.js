/**
 * HTML_QuickForm2 client-side validation library
 * Package version @package_version@
 * https://pear.php.net/package/HTML_QuickForm2
 *
 * Copyright 2006-2019, Alexey Borzov, Bertrand Mansion
 * Licensed under BSD 3-Clause License
 * https://opensource.org/licenses/BSD-3-Clause
 */

/**
 * @namespace Base namespace for QuickForm, we no longer define our stuff in global namespace
 */
var qf = qf || {};

/**
 * @namespace Namespace for QuickForm elements' javascript
 */
qf.elements = qf.elements || {};

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

qf.Map.prototype = (function(){
    /**
     * Wrapper function for hasOwnProperty
     * @param   {Object}    obj
     * @param   {*}         key
     * @returns {boolean}
     * @private
     */
    function _hasKey(obj, key)
    {
        return Object.prototype.hasOwnProperty.call(obj, key);
    }

    /**
     * Removes keys that are no longer in the map from the _keys array
     * @private
     */
    function _cleanupKeys()
    {
        if (this._count == this._keys.length) {
            return;
        }
        var srcIndex  = 0;
        var destIndex = 0;
        var seen      = {};
        while (srcIndex < this._keys.length) {
            var key = this._keys[srcIndex];
            if (_hasKey(this._map, key)
                && !_hasKey(seen, key)
            ) {
                this._keys[destIndex++] = key;
                seen[key] = true;
            }
            srcIndex++;
        }
        this._keys.length = destIndex;
    }

    return {
        /**
         * Whether the map has the given key
         * @param   {*}     key
         * @returns {boolean}
         */
        hasKey: function(key)
        {
            return _hasKey(this._map, key);
        },

        /**
         * Returns the number of key-value pairs in the Map
         * @returns {number}
         */
        length: function()
        {
            return this._count;
        },

        /**
         * Returns the values of the Map
         * @returns {Array}
         */
        getValues: function()
        {
            _cleanupKeys.call(this);

            var ret = [];
            for (var i = 0; i < this._keys.length; i++) {
                ret.push(this._map[this._keys[i]]);
            }
            return ret;
        },

        /**
         * Returns the keys of the Map
         * @returns {String[]}
         */
        getKeys: function()
        {
            _cleanupKeys.call(this);
            return (this._keys.concat());
        },

        /**
         * Returns whether the Map is empty
         * @returns {boolean}
         */
        isEmpty: function()
        {
            return 0 == this._count;
        },

        /**
         * Removes all key-value pairs from the map
         */
        clear: function()
        {
            this._map         = {};
            this._keys.length = 0;
            this._count       = 0;
        },

        /**
         * Removes a key-value pair from the Map
         * @param   {*}         key The key to remove
         * @returns {boolean}   Whether the pair was removed
         */
        remove: function(key)
        {
            if (!_hasKey(this._map, key)) {
                return false;
            }

            delete this._map[key];
            this._count--;
            if (this._keys.length > this._count * 2) {
                _cleanupKeys.call(this);
            }
            return true;
        },

        /**
         * Returns the value for the given key
         * @param   {*} key The key to look for
         * @param   {*} [defaultVal] The value to return if the key is not in the Map
         * @returns {*}
         */
        get: function(key, defaultVal)
        {
            if (_hasKey(this._map, key)) {
                return this._map[key];
            }
            return defaultVal;
        },

        /**
         * Adds a key-value pair to the Map
         * @param {*} key
         * @param {*} value
         */
        set: function(key, value)
        {
            if (!_hasKey(this._map, key)) {
                this._count++;
                this._keys.push(key);
            }
            this._map[key] = value;
        },

        /**
         * Merges key-value pairs from another Object or Map
         * @param {Object} map
         * @param {function(*, *)} [mergeFn] Optional function to call on values if
         *      both maps have the same key. By default a value from the map being
         *      merged will be stored under that key.
         */
        merge: function(map, mergeFn)
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
        }
    };
})();

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

/**
 * @namespace Functions for CSS classes handling
 */
qf.classes = {
    /**
     * Adds a class or a list of classes to an element, without duplicating class names
     *
     * @param {Node} element            DOM node to add class(es) to
     * @param {string|string[]} name    Class name(s) to add
     */
    add: function(element, name)
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
    },

    /**
     * Removes a class or a list of classes from an element
     *
     * @param {Node} element            DOM node to remove class(es) from
     * @param {string|string[]} name    Class name(s) to remove
     */
    remove: function(element, name)
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
    },

    /**
     * Checks whether a given element has a given class
     *
     * @param   {Node} element  DOM node to check
     * @param   {string} name   Class name to check for
     * @returns {boolean}
     */
    has: function(element, name)
    {
        return (-1 < (' ' + element.className + ' ').replace(/[\n\t\r]/g, ' ').indexOf(' ' + name + ' '));
    }
};

/**
 * @namespace Minor fixes to JS events to make them a bit more crossbrowser
 */
qf.events = {
    /**
     * Tests for specific events support
     *
     * Code "inspired" by jQuery, original technique from here:
     * http://perfectionkills.com/detecting-event-support-without-browser-sniffing/
     *
     * @type {Object}
     */
    test: (function() {
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
    })(),

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
     * @param   {boolean}    [capture]
     */
    addListener: function(element, type, handler, capture)
    {
        if (element.addEventListener) {
            element.addEventListener(type, handler, capture);
        } else {
            element.attachEvent('on' + type, handler);
        }
    },

    /**
     * A na&iuml;ve wrapper around removeEventListener() and detachEvent().
     *
     * @param   {Element}    element
     * @param   {String}     type
     * @param   {function()} handler
     * @param   {boolean}    capture
     */
    removeListener: function(element, type, handler, capture)
    {
        if (element.removeEventListener) {
            element.removeEventListener(type, handler, capture);
        } else {
            element.detachEvent('on' + type, handler);
        }
    },

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
    fixEvent: function(e)
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
    },

    /**
     * Attaches cross-browser "change" and "blur" handlers to form object
     *
     * @param {HTMLFormElement} form
     * @param {function()}      handler
     */
    addLiveValidationHandler: function(form, handler)
    {
        if (this.test.changeBubbles) {
            this.addListener(form, 'change', handler, true);

        } else {
            // Simulated bubbling change event for IE. Based on jQuery code,
            // works by on-demand attaching of onchange handlers to form elements
            // with a special case for checkboxes and radios
            this.addListener(form, 'beforeactivate', function(event) {
                var el = qf.events.fixEvent(event).target;

                if (/^(?:textarea|input|select)$/i.test(el.nodeName) && !el._onchange_attached) {
                    if (el.type !== 'checkbox' && el.type !== 'radio') {
                        qf.events.addListener(el, 'change', handler);

                    } else {
                        // IE doesn't fire onchange on checkboxes and radios until blur
                        // so we fire a fake change onclick after "checked" property
                        // was changed
                        qf.events.addListener(el, 'propertychange', function(event) {
                            if (qf.events.fixEvent(event).propertyName === 'checked') {
                                this._checked_changed = true;
                            }
                        });
                        qf.events.addListener(el, 'click', function(event) {
                            if (this._checked_changed) {
                                event = qf.events.fixEvent(event);
                                event._type = 'change';
                                this._checked_changed = false;
                                handler(event);
                            }
                        });
                    }
                    el._onchange_attached = true;
                }
            });
        }

        if (qf.events.test.focusinBubbles) {
            this.addListener(form, 'focusout', handler, true);
        } else {
            this.addListener(form, 'blur', handler, true);
        }
    }
};

/**
 * Client-side rule object
 *
 * @param {function()} callback
 * @param {string}     owner
 * @param {string}     message
 * @param {Array}      chained
 * @constructor
 */
qf.Rule = function(callback, owner, message, chained)
{
   /**
    * Function performing actual validation
    * @type {function()}
    */
    this.callback = callback;

   /**
    * ID of owner element
    * @type {string}
    */
    this.owner    = owner;

   /**
    * Error message to set if validation fails
    * @type {string}
    */
    this.message  = message;

   /**
    * Chained rules
    * @type {Array}
    */
    this.chained  = chained || [[]];
};

/**
 * Client-side rule object that should run onblur / onchange
 *
 * @param {function()} callback
 * @param {string}     owner
 * @param {string}     message
 * @param {string[]}   triggers
 * @param {Array}      chained
 * @constructor
 */
qf.LiveRule = function(callback, owner, message, triggers, chained)
{
    qf.Rule.call(this, callback, owner, message, chained);

   /**
    * IDs of elements that should trigger validation
    * @type {string[]}
    */
    this.triggers = triggers;
};

qf.LiveRule.prototype = new qf.Rule();
qf.LiveRule.prototype.constructor = qf.LiveRule;

/**
 * Form validator, attaches handlers that run the given rules.
 *
 * @param {HTMLFormElement} form
 * @param {qf.Rule[]} rules
 * @constructor
 */
qf.Validator = function(form, rules)
{
   /**
    * Validation rules
    * @type {qf.Rule[]}
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
        if (rule instanceof qf.LiveRule) {
            qf.events.addLiveValidationHandler(form, qf.Validator.liveHandler);
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
qf.Validator.liveHandler = function(event)
{
    event = qf.events.fixEvent(event);
    // need to check that target has a form property: http://news.php.net/php.pear.general/31445
    if (event.target.form && event.target.form.validator) {
        var id        = event.target.id,
            type      = event._type || event.type,
            validator = event.target.form.validator;
        // Prevent duplicate validation run on blur event fired immediately after change
        if ('change' === type || !validator._lastTarget || id !== validator._lastTarget) {
            validator.runLive(event);
        }
        validator._lastTarget = id;
    }
};

qf.Validator.prototype = {
    /**
     * CSS classes to use when marking validation status
     * @type {Object}
     */
    classes: {
        error:      'error',
        valid:      'valid',
        message:    'error',
        ancestor:   'element'
    },

    /**
     * Called before starting the validation. May be used e.g. to clear the errors from form elements.
     * @param {HTMLFormElement} form The form being validated currently
     */
    onStart: function(form)
    {
        for (var i = 0, rule; rule = this.rules[i]; i++) {
            this.removeRelatedErrors(rule);
        }
    },

    /**
     * Called on setting the element error
     *
     * @param {string} elementId ID attribute of an element
     * @param {string} errorMessage
     */
    onFieldError: function(elementId, errorMessage)
    {
        var parent = this.findAncestor(elementId);
        if (!parent) {
            return
        }
        qf.classes.add(parent, this.classes.error);

        var error = document.createElement('span');
        error.className = this.classes.message;
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
    },

    /**
     * Called on successfully validating the element
     *
     * @param {string} elementId
     */
    onFieldValid: function(elementId)
    {
        var ancestor = this.findAncestor(elementId);
        if (ancestor) {
            qf.classes.add(ancestor, this.classes.valid);
        }
    },

    /**
     * Called on successfully validating the form
     */
    onFormValid: function() {},

    /**
     * Called on failed validation
     */
    onFormError: function() {},

    /**
     * Performs validation using the stored rules
     * @param   {HTMLFormElement} form    The form being validated
     * @returns {boolean}
     */
    run: function(form)
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
    },

    /**
     * Performs live validation of an element and related ones
     *
     * @param {Event} event     Event triggering the validation
     */
    runLive: function(event)
    {
        var testId   = ' ' + event.target.id + ' ',
            ruleHash = new qf.Map(),
            length   = -1;

        // first: find all rules "related" to the given element, clear their error messages
        while (ruleHash.length() > length) {
            length = ruleHash.length();
            for (var i = 0, rule; rule = this.rules[i]; i++) {
                if (!(rule instanceof qf.LiveRule) || ruleHash.hasKey(i)) {
                    continue;
                }
                for (var j = 0, trigger; trigger = rule.triggers[j]; j++) {
                    if (-1 < testId.indexOf(' ' + trigger + ' ')) {
                        ruleHash.set(i, true);
                        this.removeRelatedErrors(rule);
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
    },

    /**
     * Performs validation, sets the element's error if validation fails.
     *
     * @param   {qf.Rule} rule Validation rule, maybe with chained rules
     * @returns {boolean}
     */
    validate: function(rule)
    {
        var globalValid = false, localValid = rule.callback.call(this);

        for (var i = 0, item; item = rule.chained[i]; i++) {
            for (var j = 0, multiplier; multiplier = item[j]; j++) {
                localValid = localValid && this.validate(multiplier);
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

        if (!globalValid && rule.message && !this.errors.hasKey(rule.owner)) {
            this.errors.set(rule.owner, rule.message);
            this.onFieldError(rule.owner, rule.message);
        } else if (!this.errors.hasKey(rule.owner)) {
            this.onFieldValid(rule.owner);
        }

        return globalValid;
    },

    /**
     * Returns the first ancestor of an element that is either a fieldset, a form or has preset CSS class
     *
     * @param   {string} elementId
     * @returns {Node}
     */
    findAncestor: function(elementId)
    {
        var parent = document.getElementById(elementId);
        // prevent setting anything on hidden elements
        if (parent.type && 'hidden' === parent.type) {
            return null;
        }
        while (!qf.classes.has(parent, this.classes.ancestor)
               && 'fieldset' != parent.nodeName.toLowerCase()
               && 'form' != parent.nodeName.toLowerCase()
        ) {
            parent = parent.parentNode;
        }
        return parent;
    },

    /**
     * Removes the error message for the given element
     *
     * @param {string} elementId
     */
    removeErrorMessage: function(elementId)
    {
        var parent = this.findAncestor(elementId);

        this.errors.remove(elementId);
        if (parent) {
            qf.classes.remove(parent, [this.classes.error, this.classes.valid]);

            var spans = parent.getElementsByTagName('span');
            for (var i = spans.length - 1; i >= 0; i--) {
                if (qf.classes.has(spans[i], this.classes.message)) {
                    spans[i].parentNode.removeChild(spans[i]);
                }
            }
        }
    },

    /**
     * Removes error messages from owner element(s) of a given rule and chained rules
     *
     * @param {qf.Rule} rule
     */
    removeRelatedErrors: function(rule)
    {
        this.removeErrorMessage(rule.owner);
        for (var i = 0, item; item = rule.chained[i]; i++) {
            for (var j = 0, multiplier; multiplier = item[j]; j++) {
                this.removeRelatedErrors(multiplier);
            }
        }
    }
};
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
