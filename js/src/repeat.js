/**
 * @preserve HTML_QuickForm2: support functions for repeat elements
 * Package version @package_version@
 * https://pear.php.net/package/HTML_QuickForm2
 *
 * Copyright 2006-2019, Alexey Borzov, Bertrand Mansion
 * Licensed under BSD 3-Clause License
 * https://opensource.org/licenses/BSD-3-Clause
 */

/**
 * Sets repeat properties and attaches handlers for adding and removing items
 *
 * @param {HTMLElement} container
 * @param {String} itemId
 * @param {String[]} triggers
 * @param {String} rulesTpl
 * @param {String} scriptsTpl
 * @constructor
 */
qf.elements.Repeat = function(container, itemId, triggers, rulesTpl, scriptsTpl)
{
    container.repeat = this;

    /**
     * Form containing the repeat element
     * @type {HTMLFormElement}
     */
    this.form            = null;

    /**
     * Prototype item which will be cloned in add()
     * @type {HTMLElement}
     */
    this.repeatPrototype = null;

    /**
     * HTML element containing all repeated items
     * @type {HTMLElement}
     */
    this.container       = container;

    /**
     * Id of repeated items, used to deduce index
     * @type {String}
     */
    this.itemId          = itemId;

    /**
     * String containing validation code template, will be eval()'d
     * @type {String}
     */
    this.rulesTpl        = rulesTpl;

    /**
     * String containing elements setup code template, will be eval()'d
     * @type {String}
     */
    this.scriptsTpl      = scriptsTpl;

    /**
     * Templates for element's id attributes, used to remove rules on removing repeated item
     * @type {String[]}
     */
    this.triggers        = triggers;

    // find all elements with class repeatAdd inside container...
    var adders = this.getElementsByClass('repeatAdd', container);
    for (var i = 0, element; element = adders[i]; i++) {
        qf.events.addListener(element, 'click', qf.elements.Repeat.addHandler);
    }
    // find all elements with class repeatRemove inside container...
    var removers = this.getElementsByClass('repeatRemove', container);
    for (i = 0; element = removers[i]; i++) {
        qf.events.addListener(element, 'click', qf.elements.Repeat.removeHandler);
    }
};

/**
 * Event handler for "add item" onclick events, added automatically on elements with class 'repeatAdd'
 *
 * @param {Event} event
 */
qf.elements.Repeat.addHandler = function(event)
{
    event = qf.events.fixEvent(event);

    var parent = event.target;
    while (parent && !qf.classes.has(parent, 'repeat')) {
        parent = parent.parentNode;
    }
    if (parent && parent.repeat && parent.repeat.onBeforeAdd()) {
        parent.repeat.add();
    }
    event.preventDefault();
};

/**
 * Event handler for "remove item" onclick events, added automatically on elements with class 'repeatRemove'
 *
 * @param {Event} event
 */
qf.elements.Repeat.removeHandler = function(event)
{
    event = qf.events.fixEvent(event);

    var parent = event.target,
        item;
    while (parent && !qf.classes.has(parent, 'repeat')) {
        if (qf.classes.has(parent, 'repeatItem')) {
            item = parent;
        }
        parent = parent.parentNode;
    }
    if (parent && item && parent.repeat && parent.repeat.onBeforeRemove(item)) {
        parent.repeat.remove(item);
    }
    event.preventDefault();
};

qf.elements.Repeat.prototype = {
    /**
     * Finds elements by CSS class name
     *
     * Wraps around native getElementsByClassName() if available, uses a custom
     * implementation if not.
     *
     * @function
     * @param {String} className
     * @param {Node} node
     * @returns {Node[]}
     */
    getElementsByClass: (function() {
        if (document.getElementsByClassName) {
            return function(className, node) {
                return node.getElementsByClassName(className);
            };
        } else {
            return function(className, node) {
                var list   = node.getElementsByTagName('*'),
                    result = [];

                for (var i = 0, child; child = list[i]; i++) {
                    if (qf.classes.has(child, className)) {
                        result.push(child);
                    }
                }
                return result;
            };
        }
    })(),
    /**
     * Finds an index for a given repeat item
     *
     * @param {Node} item
     * @returns {String}
     */
    findIndexByItem: function(item)
    {
        var itemRegexp = new RegExp('^' + this.itemId.replace(':idx:', '([a-zA-Z0-9_]+?)') + '$'),
            m;

        if (item.id && (m = itemRegexp.exec(item.id))) {
            // item has the needed id itself (fieldset case)
            return m[1];
        } else {
            // search for item with a needed id (group case)
            var elements = item.getElementsByTagName('*');
            for (var i = 0, element; element = elements[i]; i++) {
                if (element.id && (m = itemRegexp.exec(element.id))) {
                    return m[1];
                }
            }
        }
        return null;
    },
    /**
     * Finds a repeat item for a given index
     *
     * @param {String} index
     * @returns {Node}
     */
    findItemByIndex: function(index)
    {
        var id = this.itemId.replace(':idx:', index),
            el = document.getElementById(id);
        if (el && !qf.classes.has(el, 'repeatItem')) {
            do {
                el = el.parentNode;
            } while (el && !qf.classes.has(el, 'repeatItem'));
        }
        return el;
    },
    /**
     * Finds a form containing repeat element
     *
     * @returns {HTMLFormElement}
     */
    findForm: function()
    {
        var parent = this.container;
        while (parent && 'form' !== parent.nodeName.toLowerCase()) {
            parent = parent.parentNode;
        }
        return parent;
    },
    /**
     * Generates a new index for item being added to the repeat
     *
     * @returns {String}
     */
    generateIndex: function()
    {
        var index;

        do {
            // 10000 will be enough for everybody!
            index = 'add' + Math.round(Math.random() * 10000);
        } while (document.getElementById(this.itemId.replace(':idx:', index)));
        return index;
    },
    /**
     * Adds a new repeated item to the repeat element
     *
     * @param {String} [index] Explicit index to use, will be generated if not given
     * @return {String} Added element's index
     */
    add: function(index)
    {
        if (!this.repeatPrototype) {
            this.repeatPrototype = this.getElementsByClass('repeatPrototype', this.container)[0];
        }
        if (0 == arguments.length || !/^[a-zA-Z0-9_]+$/.test(index)) {
            index = this.generateIndex();
        }

        var items    = this.getElementsByClass('repeatItem', this.container),
            lastItem = items[items.length - 1],
            clone    = this.repeatPrototype.cloneNode(true);

        qf.classes.remove(clone, 'repeatPrototype');
        if (clone.id) {
            clone.id = clone.id.replace(':idx:', index);
        }
        // maybe get rid of this and mangle innerHTML instead?
        var elements = clone.getElementsByTagName('*');
        for (var i = 0, element; element = elements[i]; i++) {
            if (element.id) {
                element.id = element.id.replace(':idx:', index);
            }
            if (element.name) {
                element.name = element.name.replace(':idx:', index);
            }
            if (element.type && ('checkbox' == element.type || 'radio' == element.type)) {
                element.value = element.value.replace(':idx:', index);
            }
            if (element.htmlFor) {
                element.htmlFor = element.htmlFor.replace(':idx:', index);
            }
            // inline script found, eval() 'em
            if ('script' == element.nodeName.toLowerCase()) {
                eval(element.innerHTML.replace(/:idx:/g, index));
            }
            if (qf.classes.has(element, 'repeatAdd')) {
                qf.events.addListener(element, 'click', qf.elements.Repeat.addHandler);
            }
            if (qf.classes.has(element, 'repeatRemove')) {
                qf.events.addListener(element, 'click', qf.elements.Repeat.removeHandler);
            }
        }

        lastItem.parentNode.insertBefore(clone, lastItem.nextSibling);

        if (this.scriptsTpl) {
            eval(this.scriptsTpl.replace(/:idx:/g, index));
        }
        if (this.rulesTpl) {
            if (!this.form) {
                this.form = this.findForm();
            }
            if (this.form.validator) {
                var rules = eval(this.rulesTpl.replace(/:idx:/g, index)),
                    rule;
                for (i = 0; rule = rules[i]; i++) {
                    this.form.validator.rules.push(rule);
                }
            }
        }
        this.onChange();

        return index;
    },
    /**
     * Removes an item from repeat element
     *
     * @param {Node|String} item
     */
    remove: function(item)
    {
        var index;
        if (typeof item == 'string') {
            index = item;
            if (!(item = this.findItemByIndex(index))) {
                return;
            }
        }
        if (this.rulesTpl) {
            if (!this.form) {
                this.form = this.findForm();
            }
            if (this.form.validator) {
                var check = new qf.Map(),
                    rules = this.form.validator.rules,
                    trigger, rule, i;
                if (!index) {
                    index = this.findIndexByItem(item);
                }
                for (i = 0; trigger = this.triggers[i]; i++) {
                    check.set(trigger.replace(':idx:', index), true);
                }
                for (i = rules.length - 1; rule = rules[i]; i--) {
                    // repeated IDs are unlikely to appear in rule.triggers
                    // without appearing in rule.owner, so we check only owner
                    if (check.hasKey(rule.owner)) {
                        rules.splice(i, 1);
                    }
                }
            }
        }
        item.parentNode.removeChild(item);
        this.onChange();
    },
    /**
     * Called before adding a repeated item.
     *
     * If this method returns false, no item will be added
     *
     * @returns {Boolean}
     */
    onBeforeAdd: function()
    {
        return true;
    },
    /**
     * Called before removing a repeated item.
     *
     * If this method returns false, the item will not be removed
     *
     * @param {Node} item
     * @returns {Boolean}
     */
    onBeforeRemove: function(item)
    {
        return true;
    },
    /**
     * Called after adding or deleting the item
     */
    onChange: function()
    {
    }
};
