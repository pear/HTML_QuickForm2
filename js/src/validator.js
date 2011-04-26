/* $Id$ */

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

qf.Validator.prototype = (function() {
    /**
     * Clears validation status and error message of a given element
     * 
     * @param   {string} elementId
     * @returns {Node}              Parent element that gets 'error' / 'valid'
     *                              classes applied
     * @private
     */
    function _clearValidationStatus(elementId)
    {
        var el = document.getElementById(elementId), parent = el;
        while (!qf.classes.has(parent, 'element') && 'fieldset' != parent.nodeName.toLowerCase()) {
            parent = parent.parentNode;
        }
        qf.classes.remove(parent, ['error', 'valid']);

        _clearErrors(parent);

        return parent;
    };

    /**
     * Removes <span> elements with "error" class that are children of a given element 
     * 
     * @param   {Node} element
     * @private
     */
    function _clearErrors(element)
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
    function _removeRelatedErrors(errors, rule)
    {
        if (errors.hasKey(rule.owner)) {
            errors.remove(rule.owner);
            _clearValidationStatus(rule.owner);
        }
        if (typeof rule.chained != 'undefined') {
            for (var i = 0; i < rule.chained.length; i++) {
                for (var j = 0; j < rule.chained[i].length; j++) {
                    _removeRelatedErrors(errors, rule.chained[i][j]);
                }
            }
        }
    };

    return {
        /**
         * Message prefix in alert in case of failed validation
         * @type {String}
         */
        msgPrefix: 'Invalid information entered:',

        /**
         * Message postfix in alert in case of failed validation
         * @type {String}
         */
        msgPostfix: 'Please correct these fields.',

        /**
         * Called before starting the validation. May be used e.g. to clear the errors from form elements.
         * @param {HTMLFormElement} form The form being validated currently
         */
        onStart: function(form) 
        {
            _clearErrors(form);
        },

        /**
         * Called on setting the element error
         *
         * @param {string} elementId ID attribute of an element
         * @param {string} errorMessage
         * @deprecated Use onFieldError() instead
         */
        onError: function(elementId, errorMessage)
        {
            this.onFieldError(elementId, errorMessage);
        },

        /**
         * Called on successfully validating the form
         * @deprecated Use onFormValid() instead
         */
        onValid: function()
        {
            this.onFormValid();
        },

        /**
         * Called on failed validation
         * @deprecated Use onFormError() instead
         */
        onInvalid: function()
        {
            this.onFormError();
        },

        /**
         * Called on setting the element error
         *
         * @param {string} elementId ID attribute of an element
         * @param {string} errorMessage
         */
        onFieldError: function(elementId, errorMessage)
        {
            var parent = _clearValidationStatus(elementId);
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
        },

        /**
         * Called on successfully validating the element
         *
         * @param {string} elementId
         */
        onFieldValid: function(elementId)
        {
            var parent = _clearValidationStatus(elementId);
            qf.classes.add(parent, 'valid');
        },

        /**
         * Called on successfully validating the form
         */
        onFormValid: function() {},

        /**
         * Called on failed validation
         */
        onFormError: function()
        {
            //alert(this.msgPrefix + '\n - ' + this.errors.getValues().join('\n - ') + '\n' + this.msgPostfix);
        },

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
                    if (typeof rule.triggers == 'undefined' || ruleHash.hasKey(i)) {
                        continue;
                    }
                    for (var j = 0, trigger; trigger = rule.triggers[j]; j++) {
                        if (-1 < testId.indexOf(' ' + trigger + ' ')) {
                            ruleHash.set(i, true);
                            _removeRelatedErrors(this.errors, rule);
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
         * @param   {Object} rule Validation rule, maybe with chained rules
         * @returns {boolean}
         */
        validate: function(rule)
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
        }
    };
})();

