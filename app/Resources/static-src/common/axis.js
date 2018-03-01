(function (root, factory) {
	if (typeof define === 'function' && define.amd) {
		define([], factory);
	} else if (typeof exports === 'object') {
		module.exports = factory();
	} else {
		root.axis = factory();
	}
}(this, function () {
	'use strict';
	var axis = {};
	var types = 'Array Object String Date RegExp Function Boolean Number Null Undefined'.split(' ');
	function type() {
		return Object.prototype.toString.call(this).slice(8, -1);
	}
	for (var i = types.length; i--;) {
		axis['is' + types[i]] = (function (self) {
			return function (elem) {
				return type.call(elem) === self;
			};
		})(types[i]);
	}
	return axis;
}));
// axis.isArray([]); 
// axis.isObject({}); 
// axis.isString('');
// axis.isDate(new Date()); 
// axis.isRegExp(/test/i); 
// axis.isFunction(function () {});
// axis.isBoolean(true); 
// axis.isNumber(1); 
// axis.isNull(null); 
// axis.isUndefined(); 
