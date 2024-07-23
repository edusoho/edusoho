import _ from 'lodash';

const trim = (rule, value, callback) => {
  value.trim().length > 0 ? callback() : callback(new Error(Translator.trans('validate.trim.message')));
};

const course_title = (rule, value, callback) => {
  /^[^<>]*$/.test(value) ? callback() : callback(new Error(Translator.trans('validate.course_title.message')));
};

const positive_price = (rule, value, callback) => {
  /^[0-9]{0,8}(\.\d{0,2})?$/.test(value) ? callback() : callback(new Error(Translator.trans('validate.positive_currency.message')));
};

const digits = (rule, value, callback) => {
  /(^[1-9]\d*$)/.test(value) ? callback() : callback(new Error(Translator.trans('validate.valid_digits_input.message')));
}

const digits_0 = (rule, value, callback) => {
  /(^[0-9]\d*$)/.test(value) ? callback() : callback(new Error(Translator.trans('validate.unsigned_integer.message')));
}

const max_year = (rule, value, callback) => {
  value < 100000 ? callback() : callback(new Error(Translator.trans('validate.max_year.message')));
}

const currency = (rule, value, callback) => {
  /^[0-9]{0,8}(\.\d{0,2})?$/.test(value) ? callback() : callback(new Error(Translator.trans('validate.currency.message')));
}

const course_title_length = (rule, value, callback) => {
  let l = calculateByteLength(value);
  l <= 200 ? callback() : callback(new Error(Translator.trans(`字符长度必须小于等于200，一个中文字算2个字符`)));
}

const calculateByteLength = (string) => {
  let length = string.length;
  for (let i = 0; i < string.length; i++) {
    if (string.charCodeAt(i) > 127)
      length++;
  }
  return length;
}

const isPositiveInteger = (num) => {
  return _.isInteger(num) && num > 0 ;
}

const inter_byte = (rule, value, callback) => {

  if (!value || (!rule.maxSize && !rule.minSize)) {
    return callback();
  }

  let byteLength = 0;
  for (let i = 0 ; i < value.length; i++) {
    let c = value.charAt(i);

    if (/^[\u0000-\u00ff]$/.test(c)) {
      byteLength++;
    } else {
      byteLength += 2;
    }
  }

  if ( rule.maxSize && isPositiveInteger(rule.maxSize) && byteLength > rule.maxSize ) {
    callback(new Error(Translator.trans('validate.length_max.message', {'length': rule.maxSize})));
  } else if ( rule.minSize && isPositiveInteger(rule.minSize) && byteLength < rule.minSize ) {
    callback(new Error(Translator.trans('validate.length_min.message', {'length': rule.minSize})));
  } else {
    callback();
  }
}


export {
  trim,
  course_title,
  positive_price,
  max_year,
  digits,
  digits_0,
  currency,
  course_title_length,
  inter_byte
};