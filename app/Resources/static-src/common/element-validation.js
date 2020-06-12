const trim = (rule, value, callback) => {
  console.log(rule);
  if (value.trim().length > 0) {
    callback();
  } else {
    callback(new Error(Translator.trans('validate.trim.message')));
  }
};

export {
  trim
};