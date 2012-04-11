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

qf.Repeat = function(container, itemId)
{
    container.repeat = this;

    this.container       = container;
    this.repeatPrototype = null;
    this.itemId          = itemId;

    if (document.getElementsByClassName) {
        this.getElementsByClass = function(className, node) {
            return node.getElementsByClassName(className);
        };
    } else {
        this.getElementsByClass = function(className, node) {
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
    add: function()
    {
        if (!this.repeatPrototype) {
            this.repeatPrototype = this.getElementsByClass('repeatPrototype', this.container)[0];
        }

        var items      = this.getElementsByClass('repeatItem', this.container),
            lastItem   = items[items.length - 1],
            itemRegexp = new RegExp('^' + this.itemId.replace(':idx:', '(\\d+?)') + '$'),
            clone      = this.repeatPrototype.cloneNode(true),
            index, m, elements, i, element;

        if (qf.classes.has(lastItem, 'repeatPrototype')) {
            // last item *is* prototype -> use 0 as index
            index = 0;
        } else if (lastItem.id && (m = itemRegexp.exec(lastItem.id))) {
            // last item has the needed id (fieldset case)
            index = m[1] - (-1);
        } else {
            // search for item with a needed id
            elements = lastItem.getElementsByTagName('*');
            for (i = 0; element = elements[i]; i++) {
                if (element.id && (m = itemRegexp.exec(element.id))) {
                    index = m[1] - (-1);
                    break;
                }
            }
        }

        qf.classes.remove(clone, 'repeatPrototype');
        if (clone.id) {
            clone.id = clone.id.replace(':idx:', index);
        }
        // maybe get rid of this and mangle innerHTML instead?
        elements = clone.getElementsByTagName('*');
        for (i = 0; element = elements[i]; i++) {
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

        if (lastItem.nextSibling) {
            lastItem.parentNode.insertBefore(clone, lastItem.nextSibling);
        } else {
            lastItem.parentNode.appendChild(clone);
        }
    },
    remove: function(item)
    {
        item.parentNode.removeChild(item);
    }
};
