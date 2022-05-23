/*
 HTML_QuickForm2: support functions for repeat elements
 Package version 2.3.1
 https://pear.php.net/package/HTML_QuickForm2

 Copyright 2006-2022, Alexey Borzov, Bertrand Mansion
 Licensed under BSD 3-Clause License
 https://opensource.org/licenses/BSD-3-Clause
*/
qf.elements.Repeat=function(a,b,d,e,f){a.repeat=this;this.repeatPrototype=this.form=null;this.container=a;this.itemId=b;this.rulesTpl=e;this.scriptsTpl=f;this.triggers=d;e=this.getElementsByClass("repeatAdd",a);for(b=0;d=e[b];b++)qf.events.addListener(d,"click",qf.elements.Repeat.addHandler);a=this.getElementsByClass("repeatRemove",a);for(b=0;d=a[b];b++)qf.events.addListener(d,"click",qf.elements.Repeat.removeHandler)};
qf.elements.Repeat.addHandler=function(a){a=qf.events.fixEvent(a);for(var b=a.target;b&&!qf.classes.has(b,"repeat");)b=b.parentNode;b&&b.repeat&&b.repeat.onBeforeAdd()&&b.repeat.add();a.preventDefault()};qf.elements.Repeat.removeHandler=function(a){a=qf.events.fixEvent(a);for(var b=a.target,d;b&&!qf.classes.has(b,"repeat");)qf.classes.has(b,"repeatItem")&&(d=b),b=b.parentNode;b&&d&&b.repeat&&b.repeat.onBeforeRemove(d)&&b.repeat.remove(d);a.preventDefault()};
qf.elements.Repeat.prototype={getElementsByClass:function(){return document.getElementsByClassName?function(a,b){return b.getElementsByClassName(a)}:function(a,b){b=b.getElementsByTagName("*");for(var d=[],e=0,f;f=b[e];e++)qf.classes.has(f,a)&&d.push(f);return d}}(),findIndexByItem:function(a){var b=new RegExp("^"+this.itemId.replace(":idx:","([a-zA-Z0-9_]+?)")+"$"),d;if(a.id&&(d=b.exec(a.id)))return d[1];a=a.getElementsByTagName("*");for(var e=0,f;f=a[e];e++)if(f.id&&(d=b.exec(f.id)))return d[1];
return null},findItemByIndex:function(a){a=this.itemId.replace(":idx:",a);if((a=document.getElementById(a))&&!qf.classes.has(a,"repeatItem")){do a=a.parentNode;while(a&&!qf.classes.has(a,"repeatItem"))}return a},findForm:function(){for(var a=this.container;a&&"form"!==a.nodeName.toLowerCase();)a=a.parentNode;return a},generateIndex:function(){do var a="add"+Math.round(1E4*Math.random());while(document.getElementById(this.itemId.replace(":idx:",a)));return a},add:function(a){this.repeatPrototype||
(this.repeatPrototype=this.getElementsByClass("repeatPrototype",this.container)[0]);0!=arguments.length&&/^[a-zA-Z0-9_]+$/.test(a)||(a=this.generateIndex());var b=this.getElementsByClass("repeatItem",this.container),d=b[b.length-1],e=this.repeatPrototype.cloneNode(!0);qf.classes.remove(e,"repeatPrototype");e.id&&(e.id=e.id.replace(":idx:",a));var f=e.getElementsByTagName("*");b=0;for(var c;c=f[b];b++)c.id&&(c.id=c.id.replace(":idx:",a)),c.name&&(c.name=c.name.replace(":idx:",a)),!c.type||"checkbox"!=
c.type&&"radio"!=c.type||(c.value=c.value.replace(":idx:",a)),c.htmlFor&&(c.htmlFor=c.htmlFor.replace(":idx:",a)),"script"==c.nodeName.toLowerCase()&&eval(c.innerHTML.replace(/:idx:/g,a)),qf.classes.has(c,"repeatAdd")&&qf.events.addListener(c,"click",qf.elements.Repeat.addHandler),qf.classes.has(c,"repeatRemove")&&qf.events.addListener(c,"click",qf.elements.Repeat.removeHandler);d.parentNode.insertBefore(e,d.nextSibling);this.scriptsTpl&&eval(this.scriptsTpl.replace(/:idx:/g,a));if(this.rulesTpl&&
(this.form||(this.form=this.findForm()),this.form.validator))for(d=eval(this.rulesTpl.replace(/:idx:/g,a)),b=0;e=d[b];b++)this.form.validator.rules.push(e);this.onChange();return a},remove:function(a){if("string"==typeof a){var b=a;if(!(a=this.findItemByIndex(b)))return}if(this.rulesTpl&&(this.form||(this.form=this.findForm()),this.form.validator)){var d=new qf.Map,e=this.form.validator.rules,f,c;b||(b=this.findIndexByItem(a));for(c=0;f=this.triggers[c];c++)d.set(f.replace(":idx:",b),!0);for(c=e.length-
1;b=e[c];c--)d.hasKey(b.owner)&&e.splice(c,1)}a.parentNode.removeChild(a);this.onChange()},onBeforeAdd:function(){return!0},onBeforeRemove:function(a){return!0},onChange:function(){}};
