/**
 * HTML_QuickForm2: JS library for hierselect elements
 *
 * $Id$
 */

qf.addNamespace('qf.elements.hierselect');

/**
 * Adds event handlers for hierselect behavior
 *
 * @param {Array} selects               IDs of select elements in hierselect
 * @param {Function} optionsCallback    function that will be called to
 *                  get missing options (presumably via AJAX)  
 */
qf.elements.hierselect.init = function(selects, optionsCallback)
{
    var previous    = [];
    var firstSelect = document.getElementById(selects[0]);
    // add onchange listeners to all hierselect members
    for (var select; selects.length && (select = selects.shift());) {
        previous.push(select);
        var el = document.getElementById(select);
        el.hierselect = {
            previous: previous.concat(),
            next:     selects.concat(),
            callback: optionsCallback
        };
        qf.events.addListener(el, 'change', qf.elements.hierselect.cascade);
    }
    // form reset handler, repopulates options based on default values 
    // http://pear.php.net/bugs/bug.php?id=2970
    qf.events.addListener(firstSelect.form, 'reset', function() {
        setTimeout(function() {
            if (!(firstSelect.id in qf.elements.hierselect.defaults)) {
                return;
            }
            var defaults = qf.elements.hierselect.defaults[firstSelect.id],
                next     = firstSelect.hierselect.next,
                values   = [];
            for (var i = 0; i < next.length; i++) {
                values.push(defaults[i]);
                qf.elements.hierselect.replaceOptions(
                    document.getElementById(next[i]),
                    qf.elements.hierselect.getOptions(firstSelect.id, values)
                );
                qf.form.setValue(next[i], defaults[i + 1]);
            }
        }, 1);
    });
    // page load handler, resets selects to default values
    // http://pear.php.net/bugs/bug.php?id=3176
    qf.events.addListener(window, 'load', function() {
        if (!(firstSelect.id in qf.elements.hierselect.defaults)) {
            return;
        }
        var defaults = qf.elements.hierselect.defaults[firstSelect.id],
            next     = firstSelect.hierselect.next;
        qf.form.setValue(firstSelect, defaults[0]);
        for (var i = 0; i < next.length; i++) {
            qf.form.setValue(next[i], defaults[i + 1]);
        }
    });
};

qf.elements.hierselect.getValue = function(selects)
{
    var value = [];
    for (var i = 0; i < selects.length; i++) {
        value.push(qf.form.getValue(selects[i]));
    }
    return value;
};

qf.elements.hierselect.replaceOptions = function(ctl, options)
{
    function unescapeEntities(str)
    {
        var div = document.createElement('div');
        div.innerHTML = str;
        return div.childNodes[0] ? div.childNodes[0].nodeValue : '';
    }

    ctl.options.length = 0;
    // we don't store options as {'value': 'text', ...} anymore, since browsers
    // can iterate over that in random order (http://pear.php.net/bugs/bug.php?id=16603)
    // instead, we store separate arrays for values and texts
    for (var i = 0; i < options.values.length; i++) {
        ctl.options[i] = new Option(
            -1 == String(options.texts[i]).indexOf('&')? options.texts[i]: unescapeEntities(options.texts[i]),
            options.values[i], false, false
        );
    }
};

qf.elements.hierselect.getOptions = function(selectId, keys)
{
    // select element without options is invalid, so we provide some
    var missing = {values: [''], texts: [' ']};

    if (!(selectId in qf.elements.hierselect.options)
        || typeof qf.elements.hierselect.options[selectId][keys.length - 1] == 'undefined'
    ) {
        return missing;
    }
    var ary = qf.elements.hierselect.options[selectId][keys.length - 1];
    while (keys.length) {
        var key = keys.shift();
        if (!(key in ary)) {
            return missing;
        } else if (0 == keys.length) {
            return ary[key];
        } else {
            ary = ary[key];
        }
    }
};

qf.elements.hierselect.cascade = function()
{
    if (!this.hierselect || 0 == this.hierselect.next.length) {
        return true;
    }
    // find values, starting from first upto current
    var values = qf.elements.hierselect.getValue(this.hierselect.previous);
    // TODO: get new options for next select via optionsCallback
    // replace options on next select
    qf.elements.hierselect.replaceOptions(
        document.getElementById(this.hierselect.next[0]),
        qf.elements.hierselect.getOptions(this.hierselect.previous[0], values)
    );
    // if next select is not last, call cascade on that, too
    if (1 < this.hierselect.next.length) {
        qf.elements.hierselect.cascade.call(document.getElementById(this.hierselect.next[0]));
    }
};


qf.elements.hierselect.options  = {};
qf.elements.hierselect.defaults = {};
