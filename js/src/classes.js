/**
 * @namespace Functions for CSS classes handling
 */
qf.classes = {
    /**
     * Adds a class or a list of classes to an element, without duplicating class names
     *
     * @param {Node} element            DOM node to add class(es) to
     * @param {string|string[]} name    Class name(s) to add
     */
    add: function(element, name)
    {
        if ('string' == qf.typeOf(name)) {
            name = name.split(/\\s+/);
        }
        if (!element.className) {
            element.className = name.join(' ');
        } else {
            var checkName = ' ' + element.className + ' ',
                newName   = element.className;
            for (var i = 0, len = name.length; i < len; i++) {
                if (name[i] && 0 > checkName.indexOf(' ' + name[i] + ' ')) {
                    newName += ' ' + name[i];
                }
            }
            element.className = newName;
        }
    },

    /**
     * Removes a class or a list of classes from an element
     *
     * @param {Node} element            DOM node to remove class(es) from
     * @param {string|string[]} name    Class name(s) to remove
     */
    remove: function(element, name)
    {
        if (!element.className) {
            return;
        }
        if ('string' == qf.typeOf(name)) {
            name = name.split(/\\s+/);
        }
        var className = (' ' + element.className + ' ').replace(/[\n\t\r]/g, ' ');
        for (var i = 0, len = name.length; i < len; i++) {
            if (name[i]) {
                className = className.replace(' ' + name[i] + ' ', ' ');
            }
        }
        element.className = className.replace(/^\s+/, '').replace(/\s+$/, '');
    },

    /**
     * Checks whether a given element has a given class
     *
     * @param   {Node} element  DOM node to check
     * @param   {string} name   Class name to check for
     * @returns {boolean}
     */
    has: function(element, name)
    {
        return (-1 < (' ' + element.className + ' ').replace(/[\n\t\r]/g, ' ').indexOf(' ' + name + ' '));
    }
};

