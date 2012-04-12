/**
 * @preserve HTML_QuickForm2: support functions for repeat elements
 * Package version @package_version@
 * http://pear.php.net/package/HTML_QuickForm2
 *
 * Copyright 2006-2012, Alexey Borzov, Bertrand Mansion
 * Licensed under new BSD license
 * http://opensource.org/licenses/bsd-license.php
 */

/* $Id$ */

qf.Repeat = function(container, itemId, triggers, rulesTpl, scriptsTpl)
{
    container.repeat = this;

    this.form            = null;
    this.repeatPrototype = null;

    this.container       = container;
    this.itemId          = itemId;
    this.rulesTpl        = rulesTpl;
    this.scriptsTpl      = scriptsTpl;
    this.triggers        = triggers;

    // find all elements with class repeatAdd inside container...
    var adders = this.getElementsByClass('repeatAdd', container);
    for (var i = 0, element; element = adders[i]; i++) {
        qf.events.addListener(element, 'click', qf.Repeat.handleAdd);
    }
    // find all elements with class repeatRemove inside container...
    var removers = this.getElementsByClass('repeatRemove', container);
    for (i = 0; element = removers[i]; i++) {
        qf.events.addListener(element, 'click', qf.Repeat.handleRemove);
    }
};

qf.Repeat.handleAdd = function(event)
{
    event = qf.events.fixEvent(event);

    var parent = event.target;
    while (parent && !qf.classes.has(parent, 'repeat')) {
        parent = parent.parentNode;
    }
    if (parent && parent.repeat) {
        parent.repeat.add();
    }
    event.preventDefault();
};

qf.Repeat.handleRemove = function(event)
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
    if (parent && parent.repeat) {
        parent.repeat.remove(item);
    }
    event.preventDefault();
};

qf.Repeat.prototype = {
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
    findIndex: function(item)
    {
        var itemRegexp = new RegExp('^' + this.itemId.replace(':idx:', '(\\d+?)') + '$'),
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
    },
    findForm: function()
    {
        var parent = this.container;
        while (parent && 'form' !== parent.nodeName.toLowerCase()) {
            parent = parent.parentNode;
        }
        return parent;
    },
    add: function()
    {
        if (!this.repeatPrototype) {
            this.repeatPrototype = this.getElementsByClass('repeatPrototype', this.container)[0];
        }

        var items    = this.getElementsByClass('repeatItem', this.container),
            lastItem = items[items.length - 1],
            clone    = this.repeatPrototype.cloneNode(true),
            index;

        if (qf.classes.has(lastItem, 'repeatPrototype')) {
            // last item *is* prototype -> use 0 as index
            index = 0;
        } else {
            index = this.findIndex(lastItem) - (-1);
        }

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
            if (qf.classes.has(element, 'repeatAdd')) {
                qf.events.addListener(element, 'click', qf.Repeat.handleAdd);
            }
            if (qf.classes.has(element, 'repeatRemove')) {
                qf.events.addListener(element, 'click', qf.Repeat.handleRemove);
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
    },
    remove: function(item)
    {
        if (this.rulesTpl) {
            if (!this.form) {
                this.form = this.findForm();
            }
            if (this.form.validator) {
                var check = new qf.Map(),
                    index = this.findIndex(item),
                    rules = this.form.validator.rules,
                    trigger, rule, i;
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
    }
};
