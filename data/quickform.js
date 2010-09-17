/**
 * HTML_QuickForm2: JS library used for client-side validation support 
 *
 * $Id$
 */

var qf = qf || {};

// stuff borrowed from closure library

qf.map = function(inMap)
{
    this._map   = {};
    this._keys  = [];
    this._count = 0;

    if (inMap) {
        this.merge(inMap);
    }
};

qf.map._hasKey = function (obj, key)
{
    return Object.prototype.hasOwnProperty.call(obj, key);
};

qf.map.prototype.hasKey = function(key)
{
    return qf.map._hasKey(this._map, key);
};

qf.map.prototype.length = function()
{
    return this._count;
};

qf.map.prototype.getValues = function()
{
    this._cleanupKeys();

    var ret = [];
    for (var i = 0; i < this._keys.length; i++) {
        ret.push(this._map[this._keys[i]]);
    }
    return ret;
};

qf.map.prototype.getKeys = function()
{
    this._cleanupKeys();
    return (this._keys.concat());
};

qf.map.prototype.isEmpty = function()
{
    return 0 == this._count;
};

qf.map.prototype.clear = function()
{
    this.map_         = {};
    this.keys_.length = 0;
    this.count_       = 0;
};

qf.map.prototype.remove = function(key)
{
    if (!qf.map._hasKey(this._map, key)) {
        return false;
    }

    delete this._map[key];
    this._count--;
    if (this._keys.length > this._count * 2) {
        this._cleanupKeys();
    }
    return true;
};

qf.map.prototype.get = function(key, defaultVal)
{
    if (qf.map._hasKey(this._map, key)) {
        return this._map[key];
    }
    return defaultVal;
};

qf.map.prototype.set = function(key, value)
{
    if (!qf.map._hasKey(this._map, key)) {
        this._count++;
        this._keys.push(key);
    }
    this._map[key] = value;
};

qf.map.prototype.merge = function(map)
{
    var keys, values, i = 0;
    if (map instanceof qf.map) {
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

    for (i = 0; i < keys.length; i++) {
        this.set(keys[i], values[i]);
    }
};

qf.map.prototype._cleanupKeys = function()
{
    if (this._count == this._keys.length) {
        return;
    }
    var srcIndex  = 0;
    var destIndex = 0;
    var seen      = {};
    while (srcIndex < this.keys_.length) {
        var key = this.keys_[srcIndex];
        if (qf.map._hasKey(this._map, key)
            && !qf.map._hasKey(seen, key)
        ) {
            this.keys_[destIndex++] = key;
            seen[key] = true;
        }
        srcIndex++;
    }
    this.keys_.length = destIndex;
};

// working with form values, closure library and QF 3.x

qf.form = {
    _getCheckableValue: function(el) {
        return el.checked? el.value: null;
    },
    _getSelectSingleValue: function(el) {
        var index = el.selectedIndex;
        return -1 == index? null: el.options[index].value;
    },
    _getSelectMultipleValue: function(el) {
        var values = [];
        for (var i = 0; i < el.options.length; i++) {
            if (el.options[i].selected) {
                values.push(el.options[i].value);
            }
        }
        return values;
    },
    getValue: function(el) {
        if (typeof el == 'string') {
            el = document.getElementById(el);
        }
        if (typeof el.type == 'undefined') {
            return null;
        }
        switch(el.type.toLowerCase()) {
            case 'checkbox':
            case 'radio':
                return qf.form._getCheckableValue(el);
            case 'select-one':
                return qf.form._getSelectSingleValue(el);
            case 'select-multiple':
                return qf.form._getSelectMultipleValue(el);
            default:
                return (typeof el.value == 'undefined')? null: el.value;
        }
    },
    getValueByName: function(form, name) {
        var elements = form.elements[name];
        if (typeof elements.length == 'undefined') {
            return qf.form.getValue(elements);
        } else {
            for (var i = 0; i < elements.length; i++) {
                var value = qf.form.getValue(elements[i]);
                if (value) {
                    return value;
                }
            }
            return null;
        }
    },
    getContainerValue: function() {
        var map = new qf.map();
        if (arguments.length > 0) {
            for (var i = 0; i < arguments.length; i++) {
                if (arguments[i] instanceof qf.map) {
                    map.merge(arguments[i]);
                } else {
                    var element = document.getElementById(arguments[i]);
                    var value   = this.getValue(element);
                    if (null != value) {
                        var prevValue = map.get(element.name);
                        if (typeof prevValue == 'undefined') {
                            map.set(element.name, value);
                        } else {
                            if (!prevValue instanceof Array) {
                                prevValue = [prevValue];
                            }
                            if (!value instanceof Array) {
                                value = [value];
                            }
                            map.set(element.name, prevValue.concat(value));
                        }
                    }
                }
            }
        }
        return map;
    }
};


qf.events = {
    // XXX: we maybe need a more complex solution
    addListener: function(element, type, handler) {
        if (element.addEventListener) {
            element.addEventListener(type, handler, false);
        } else {
            element.attachEvent('on' + type, handler);
        }
    },
    fixEvent: function(e) {
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
    }
};

/**
 * Form validator, attaches onsubmit handler that runs the given rules
 */
qf.validator = function(form, rules)
{
    this.prefix  = 'Invalid information entered.';
    this.postfix = 'Please correct these fields.';

    form.validator = this;
    qf.events.addListener(form, 'submit', function(event) {
        event = qf.events.fixEvent(event);

        if (!rules.length) {
            return true;
        }

        var errorMap = new qf.map();
        for (var i = 0; i < rules.length; i++) {
            if (errorMap.hasKey(rules[i].owner)) {
                continue;
            }
            form.validator.validate(rules[i], errorMap);
        }

        if (errorMap.isEmpty()) {
            form.validator.onvalid();
            return true;

        } else {
            form.validator.oninvalid(errorMap);
            event.preventDefault();
            return false;
        }
    });
};

qf.validator.prototype.onerror = function(elementId, errorMessage)
{
    // called on setting the element error
};

qf.validator.prototype.onvalid = function()
{
    // called on successfully validating the form
};

qf.validator.prototype.oninvalid = function(errorMap)
{
    // called on failed validation
    alert(this.prefix + '\n - ' + errorMap.getValues().join('\n - ') + '\n' + this.postfix);
};

// port of H_QF2_Rule::validate();
qf.validator.prototype.validate = function(rule, errorMap)
{
    var globalValid, localValid = rule.callback();

    if (typeof rule.chained == 'undefined') {
        globalValid = localValid;

    } else {
        globalValid = false;
        for (var i = 0; i < rule.chained.length; i++) {
            for (var j = 0; j < rule.chained[i].length; j++) {
                localValid = localValid && this.validate(rule.chained[i][j], errorMap);
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

    if (!globalValid && rule.message && !errorMap.hasKey(rule.owner)) {
        errorMap.set(rule.owner, rule.message);
        this.onerror(rule.owner, rule.message);
    }

    return globalValid;
};

/**
 * Client-side implementations of Rules that are a bit too complex to inline
 */
qf.rules = {
    each: function(callbacks) {
        for (var i = 0; i < callbacks.length; i++) {
            if (!callbacks[i]()) {
                return false;
            }
        }
        return true;
    },
    empty: function(value) {
        if (!value instanceof Array) {
            return '' == value;
        } else {
            for (var i = 0; i < value.length; i++) {
                if ('' != value[i]) {
                    return false;
                }
            }
            return true;
        }
    },
    nonempty: function(value, minValid) {
        var i, valid = 0;

        if (value instanceof Array) {
            for (i = 0; i < value.length; i++) {
                if ('' != value[i]) {
                    valid++;
                }
            }
            return valid >= minValid;

        } else if (value instanceof qf.map) {
            var values = value.getValues();
            for (i = 0; i < values.length; i++) {
                if (this.nonempty(values[i], 1)) {
                    valid++;
                }
            }
            return valid >= minValid;

        } else {
            return '' != value;
        }
    }
};
