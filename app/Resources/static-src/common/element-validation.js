const validatePass = (rule, value, callback) => {
  if (value !== 'afe') {
    callback(new Error('fefaefaef范'));
  } else {
    callback();
  }
};

export {
  validatePass
};