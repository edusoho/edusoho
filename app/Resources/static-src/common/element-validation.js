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

const trans = {
  created: function () {
    let refFormRule = this.$options.refFormRule;

    let map = this.transMap();

    let keys = Object.keys(map);

    for (let field in refFormRule) {
      keys.map((value) => {
        refFormRule[field].map((rule) => {
          if (Object.keys(rule).indexOf(value)) {
            // console.log(rule[value]);
            // console.log(value);
          }
          console.log(rule);
        });
      });
    }
    console.log(refFormRule);
  },
  methods: {
    transMap: function (key, params) {
      let map = {
        required: Translator.trans('validate.required.message', params)
      };

      return map[key] ? map[key] : null;
    }
  }
}


export {
  trim,
  course_title,
  positive_price,
  max_year,
  digits,
  trans
};