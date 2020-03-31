const axis = {};
const type = function() {
  return Object.prototype.toString.call(this).slice(8, -1);
};
const types = 'Array Object String Date RegExp Function Boolean Number Null Undefined'.split(' ');
for (let i = types.length; i--;) {
  axis['is' + types[i]] = (function (self) {
    return function (elem) {
      return type.call(elem) === self;
    };
  })(types[i]);
}

export { axis };
