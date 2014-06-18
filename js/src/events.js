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

