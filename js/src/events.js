/* $Id$ */

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
     * @param   {boolean}    capture
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
    }
};

