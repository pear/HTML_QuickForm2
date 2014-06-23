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
