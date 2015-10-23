define("gallery/selection/0.9.0/selection-debug", [], function(require, exports, module) {
    var selection = function(inputor) {
        if (inputor && inputor.length) {
            // if inputor is jQuery or zepto or a list of elements
            inputor = inputor[0];
        }
        if (inputor) {
            // detect feature first.
            if (typeof inputor.selectionStart != "undefined") {
                return new Selection(inputor);
            }
            var tag = inputor.tagName.toLowerCase();
        }
        if (tag && (tag === "textarea" || tag === "input")) {
            // if has inputor and inputor element is textarea or input
            return new Selection(inputor, true);
        }
        if (window.getSelection) return new DocumentSelection();
        if (document.selection) return new DocumentSelection(true);
        throw new Error("your browser is very weird");
    };
    selection.version = "<%= pkg.version %>";
    module.exports = selection;
    // Selection in Texarea or Input
    function Selection(inputor, isIE) {
        this.element = inputor;
        this.cursor = function(start, end) {
            // get cursor
            var inputor = this.element;
            if (typeof start === "undefined") {
                if (isIE) {
                    return getIECursor(inputor);
                }
                return [ inputor.selectionStart, inputor.selectionEnd ];
            }
            // set cursor
            if (isArray(start)) {
                var _s = start;
                start = _s[0];
                end = _s[1];
            }
            if (typeof end === "undefined") end = start;
            if (isIE) {
                setIECursor(inputor, start, end);
            } else {
                inputor.setSelectionRange(start, end);
            }
            return this;
        };
        return this;
    }
    // get or set selected text
    Selection.prototype.text = function(text, cur) {
        var inputor = this.element;
        var cursor = this.cursor();
        if (typeof text == "undefined") {
            return inputor.value.slice(cursor[0], cursor[1]);
        }
        return insertText(this, text, cursor[0], cursor[1], cur);
    };
    // append text to the end, and select the appended text
    Selection.prototype.append = function(text, cur) {
        var end = this.cursor()[1];
        return insertText(this, text, end, end, cur);
    };
    // prepend text to the start, and select the prepended text
    Selection.prototype.prepend = function(text, cur) {
        var start = this.cursor()[0];
        return insertText(this, text, start, start, cur);
    };
    // get the surround words of the selection
    Selection.prototype.surround = function(count) {
        if (typeof count == "undefined") count = 1;
        var value = this.element.value;
        var cursor = this.cursor();
        var before = value.slice(Math.max(0, cursor[0] - count), cursor[0]);
        var after = value.slice(cursor[1], cursor[1] + count);
        return [ before, after ];
    };
    Selection.prototype.line = function() {
        var value = this.element.value;
        var cursor = this.cursor();
        var before = value.slice(0, cursor[0]).lastIndexOf("\n");
        var after = value.slice(cursor[1]).indexOf("\n");
        // we don't need \n
        var start = before + 1;
        if (after === -1) {
            return value.slice(start);
        }
        var end = cursor[1] + after;
        return value.slice(start, end);
    };
    // Selection on document
    // TODO: should it support this feature ?
    function DocumentSelection(isIE) {
        if (!isIE) {
            var sel = window.getSelection();
            this.element = getSelectionElement(sel);
            this.text = function() {
                // TODO set text
                return sel.toString();
            };
        } else {
            this.text = function() {
                return document.selection.createRange().text;
            };
        }
        return this;
    }
    // Helpers
    // -------------
    var toString = Object.prototype.toString;
    var isArray = Array.isArray;
    if (!isArray) {
        isArray = function(val) {
            return toString.call(val) === "[object Array]";
        };
    }
    // IE sucks. This is how to get cursor position in IE.
    // Thanks to [ichord](https://github.com/ichord/At.js)
    function getIECursor(inputor) {
        var range = document.selection.createRange();
        if (range && range.parentElement() === inputor) {
            var start, end;
            var normalizedValue = inputor.value.replace(/\r\n/g, "\n");
            var len = normalizedValue.length;
            var textInputRange = inputor.createTextRange();
            textInputRange.moveToBookmark(range.getBookmark());
            var endRange = inputor.createTextRange();
            endRange.collapse(false);
            if (textInputRange.compareEndPoints("StartToEnd", endRange) > -1) {
                start = end = len;
            } else {
                start = -textInputRange.moveStart("character", -len);
                end = -textInputRange.moveEnd("character", -len);
            }
            // when select to the last character, end = 1
            if (end < start) {
                end = len;
            }
            return [ start, end ];
        }
        return [ 0, 0 ];
    }
    function setIECursor(inputor, start, end) {
        var range = inputor.createTextRange();
        range.move("character", start);
        // why should it be named as ``moveEnd`` ?
        range.moveEnd("character", end - start);
        range.select();
    }
    function insertText(selection, text, start, end, cursor) {
        if (typeof text == "undefined") text = "";
        var value = selection.element.value;
        selection.element.value = [ value.slice(0, start), text, value.slice(end) ].join("");
        end = start + text.length;
        if (cursor === "left") {
            selection.cursor(start);
        } else if (cursor === "right") {
            selection.cursor(end);
        } else {
            selection.cursor(start, end);
        }
        return selection;
    }
    function getSelectionElement(sel) {
        // start point and end point maybe in the different elements.
        // then we find their common father.
        var element = null;
        var anchorNode = sel.anchorNode;
        var focusNode = sel.focusNode;
        while (!element) {
            if (anchorNode.parentElement === focusNode.parentElement) {
                element = focusNode.parentElement;
                break;
            } else {
                anchorNode = anchorNode.parentElement;
                focusNode = focusNode.parentElement;
            }
        }
        return element;
    }
});
