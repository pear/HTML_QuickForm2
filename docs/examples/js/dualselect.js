/**
 * Javascript for dualselect element handling
 *
 * Contains methods for moving options between selects and also for selecting
 * all options of 'to' select on form submit. This is necessary since
 * unselected options obviously won't send any values.
 *
 * $Id$
 */

qf.addNamespace('qf.elements.dualselect');

qf.elements.dualselect.init = function(destId)
{
    var dest = document.getElementById(destId);
    qf.events.addListener(dest.form, 'submit', function() {
        for (var option, i = 0; option = dest.options[i]; i++) {
            option.selected = true;
        }
    });
};

qf.elements.dualselect.moveOptions = function(srcId, destId, keepSorted)
{
    var src  = document.getElementById(srcId);
    var dest = document.getElementById(destId);

    for (var i = src.options.length - 1; i >= 0; i--) {
        if (src.options[i].selected) {
            var option = src.options[i];
            src.remove(i);
            option.selected = false;
            qf.elements.dualselect.addOption(dest, option, keepSorted);
        }
    }
};

qf.elements.dualselect.addOption = function(box, option, keepSorted)
{
    /*@cc_on
    return qf.elements.dualselect.addOptionIE(box, option, keepSorted);
    @*/
    return qf.elements.dualselect.addOptionDOM(box, option, keepSorted);
};

qf.elements.dualselect.addOptionIE = function(box, option, keepSorted)
{
    if (box.options.length <= 0 || (keepSorted && option.text < box.options[0].text))  {
        box.add(option, 0);

    } else if (!keepSorted || (option.text > box.options[box.options.length - 1].text)) {
        box.add(option);

    } else {
        for (var i = box.options.length; i >= 0; i--) {
            if (option.text >= box.options[i-1].text) {
                box.add(option, i);
                break;
            }
        }
    }
    return true;
};

qf.elements.dualselect.addOptionDOM = function(box, option, keepSorted)
{
    if (!keepSorted || 0 == box.options.length ||
        option.text > box.options[box.options.length-1].text
    ) {
        box.add(option, null);

    } else if (option.text < box.options[0].text) {
        box.add(option, box.options[0]);

    } else {
        for (var i = box.options.length - 1; i >= 0; i--) {
            if (option.text >= box.options[i].text) {
                box.add(option, box.options[i + 1]);
                break;
            }
        }
    }
    return true;
};

qf.elements.dualselect.getValue = function (destId)
{
    var values = [], el = document.getElementById(destId);
    if (el.disabled) {
        return null;
    }
    for (var option, i = 0; option = el.options[i]; i++) {
        values.push(option.value);
    }
    return values;
};
