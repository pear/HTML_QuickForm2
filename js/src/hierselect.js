/**
 * @preserve HTML_QuickForm2: support functions for hierselect elements
 * Package version @package_version@
 * https://pear.php.net/package/HTML_QuickForm2
 *
 * Copyright 2006-2019, Alexey Borzov, Bertrand Mansion
 * Licensed under BSD 3-Clause License
 * https://opensource.org/licenses/BSD-3-Clause
 */

/**
 * @namespace Functions for hierselect elements
 */
qf.elements.hierselect = (function(){
    /**
     * Returns 'onreset' handler for form containing hierselect.
     *
     * This repopulates options in second and subsequent selects based on default
     * values.
     *
     * @see     <a href="http://pear.php.net/bugs/bug.php?id=2970">PEAR bug #2970</a>
     * @param   {Element}   firstSelect First select element in hierselect chain
     * @returns {function()}
     * @private
     */
    function _getResetHandler(firstSelect)
    {
        return function() {
            setTimeout(function() {
                if (!(firstSelect.id in qf.elements.hierselect.defaults)) {
                    return;
                }
                var defaults = qf.elements.hierselect.defaults[firstSelect.id],
                    next     = firstSelect.hierselect.next;
                for (var i = 0; i < next.length; i++) {
                    qf.elements.hierselect.replaceOptions(
                        document.getElementById(next[i]),
                        qf.elements.hierselect.getOptions(firstSelect.id, defaults.slice(0, i + 1))
                    );
                    qf.form.setValue(next[i], defaults[i + 1]);
                }
            }, 1);
        };
    }

    /**
     * Returns 'onload' handler for page containing hierselect.
     *
     * This resets hierselect to default values and repopulates options in second and
     * subsequent selects.
     *
     * @see     <a href="http://pear.php.net/bugs/bug.php?id=3176">PEAR bug #3176</a>
     * @param   {Element}   firstSelect First select element in hierselect chain
     * @returns {function()}
     * @private
     */
    function _getOnloadHandler(firstSelect)
    {
        return function() {
            if (!(firstSelect.id in qf.elements.hierselect.defaults)) {
                return;
            }
            var defaults = qf.elements.hierselect.defaults[firstSelect.id],
                next     = firstSelect.hierselect.next;
            qf.form.setValue(firstSelect, defaults[0]);
            for (var i = 0; i < next.length; i++) {
                qf.form.setValue(next[i], defaults[i + 1]);
            }
        };
    }

    /**
     * Stores options for a select element in options object.
     *
     * Useful mostly for asynchronous requests.
     *
     * @param   {String} selectId   ID attribute of first select element
     * @param   {Array}  keys       Values of previous select elements
     * @param   {Object} options    New options
     * @private
     */
    function _storeOptions(selectId, keys, options)
    {
        if (!(selectId in qf.elements.hierselect.options)) {
            qf.elements.hierselect.options[selectId] = [];
        }
        if (typeof qf.elements.hierselect.options[selectId][keys.length - 1] == 'undefined') {
            qf.elements.hierselect.options[selectId][keys.length - 1] = {};
        }
        var ary    = qf.elements.hierselect.options[selectId][keys.length - 1];
        var search = keys.concat();
        while (search.length) {
            var key = search.shift();
            if (0 == search.length) {
                ary[key] = options;
            } else if (!(key in ary)) {
                ary = ary[key] = {};
            } else {
                ary = ary[key];
            }
        }
    }

    /**
     * The 'onchange' handler for selects, replaces the options of subsequent select(s).
     * @param {Event} event
     * @private
     */
    function _onChangeHandler(event)
    {
        event = qf.events.fixEvent(event);
        if (event.target.hierselect && 0 != event.target.hierselect.next.length) {
            qf.elements.hierselect.cascade.call(event.target);
        }
    }

    return {
        /**
         * Adds event handlers for hierselect behavior.
         *
         * @param {Array} selects               IDs of select elements in hierselect
         * @param {Function} optionsCallback    function that will be called to
         *                  get missing options (presumably via AJAX)
         */
        init: function(selects, optionsCallback)
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
                qf.events.addListener(el, 'change', _onChangeHandler);
            }
            qf.events.addListener(firstSelect.form, 'reset', _getResetHandler(firstSelect));
            qf.events.addListener(window, 'load', _getOnloadHandler(firstSelect));
        },

        /**
         * Gets the value for a hierselect element.
         *
         * @param   {String[]}  selects Array of selects' ID attributes
         * @returns {Array}
         */
        getValue: function(selects)
        {
            var value = [];
            for (var i = 0; i < selects.length; i++) {
                value.push(qf.form.getValue(selects[i]));
            }
            return value;
        },

        /**
         * Replaces options of a select element.
         *
         * Options are provided in such a way rather than as {value: text, ...} object
         * due to the fact that browsers can iterate over an object with a 'for in'
         * loop in random order (see bug).
         *
         * @see     <a href="http://pear.php.net/bugs/bug.php?id=16603">PEAR bug #16603</a>
         * @param   {Element} ctl   Select element
         * @param   {Object}  options New options
         * @param   {Array}   options.values Values of new options
         * @param   {Array}   options.texts  Texts of new options
         */
        replaceOptions: function(ctl, options)
        {
            function unescapeEntities(str)
            {
                var div = document.createElement('div');
                div.innerHTML = str;
                return div.childNodes[0] ? div.childNodes[0].nodeValue : '';
            }

            ctl.options.length = 0;
            for (var i = 0; i < options.values.length; i++) {
                ctl.options[i] = new Option(
                    -1 == String(options.texts[i]).indexOf('&')? options.texts[i]: unescapeEntities(options.texts[i]),
                    options.values[i], false, false
                );
            }
        },

        /**
         * Finds options for next select element in hierselect.
         *
         * @param   {String} selectId   ID attribute of first select element
         * @param   {Array}  keys       Values of previous select elements
         * @param   {Function} callback Function to use for loading additional options
         * @returns {Object}
         */
        getOptions: function(selectId, keys, callback)
        {
            if (!(selectId in qf.elements.hierselect.options)
                || typeof qf.elements.hierselect.options[selectId][keys.length - 1] == 'undefined'
            ) {
                return qf.elements.hierselect.missingOptions;
            }
            var ary    = qf.elements.hierselect.options[selectId][keys.length - 1];
            // we need to pass keys to a callback, so don't mangle 'em.
            var search = keys.concat();
            while (search.length) {
                var key = search.shift();
                if (0 == search.length) {
                    if (!(key in ary) ) {
                        ary[key] = callback? callback(keys, selectId): qf.elements.hierselect.missingOptions;
                    }
                    return ary[key];
                } else if (!(key in ary)) {
                    ary[key] = {};
                }
                ary = ary[key];
            }
        },

        /**
         * Returns a callback that should be called on successful completion of asynchronous request for additional options.
         *
         * @param   {String} selectId   ID attribute of first select element in hierselect
         * @param   {Array}  keys       Values of previous select elements
         * @returns {Function}
         */
        getAsyncCallback: function(selectId, keys)
        {
            return function(result) {
                _storeOptions(selectId, keys, result);
                var hs   = document.getElementById(selectId).hierselect;
                var next = document.getElementById(hs.next[keys.length - 1]);
                qf.elements.hierselect.replaceOptions(next, result);
                if (keys.length < hs.next.length) {
                    qf.elements.hierselect.cascade.call(next);
                }
            };
        },

        /**
         * Replaces the options of subsequent selects based on values of this and previous ones.
         */
        cascade: function()
        {
            // find values, starting from first upto current
            var values = qf.elements.hierselect.getValue(this.hierselect.previous);
            // replace options on next select
            qf.elements.hierselect.replaceOptions(
                document.getElementById(this.hierselect.next[0]),
                qf.elements.hierselect.getOptions(this.hierselect.previous[0], values,
                                                  this.hierselect.callback)
            );
            // if next select is not last, call cascade on that, too
            if (1 < this.hierselect.next.length) {
                qf.elements.hierselect.cascade.call(document.getElementById(this.hierselect.next[0]));
            }
        },

        /**
         * Options to use if no options were found. Select without options is invalid in HTML.
         * @type {Object}
         */
        missingOptions: {values: [''], texts: [' ']},

        /**
         * Options cache for second and subsequent selects in hierselect. Keyed by
         * ID attribute of first select in chain.
         * @type {Object}
         */
        options: {},

        /**
         * Default values for hierselects. Keyed by ID attribute of first select in chain.
         * @type {Object}
         */
        defaults: {}
    };
})();

