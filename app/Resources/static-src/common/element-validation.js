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

const max_year = (rule, value, callback) => {
  value < 100000 ? callback() : callback(new Error(Translator.trans('validate.max_year.message')));
}

const currency = (rule, value, callback) => {
  /^[0-9]{0,8}(\.\d{0,2})?$/.test(value) ? callback() : callback(new Error(Translator.trans('validate.currency.message')));
}

export {
  trim,
  course_title,
  positive_price,
  max_year,
  digits,
  currency,
};