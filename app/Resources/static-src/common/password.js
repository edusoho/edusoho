/**
 * 注意：密码逻辑的修改，需要同步修改后端的密码校验逻辑 src/Biz/User/Support/PasswordValidator.php
 */

/**
 * 获得密码等级
 * @param password
 * @returns {number} 1: 强密码（适用于管理员） 2: 普通密码（适用于非管理员） 0: 密码不符合要求
 */
export const getPasswordLevel = (password) => {
  if (validateStrongPassword(password)) {
    return 1;
  } else if (validatePassword(password)) {
    return 2;
  } else {
    return 0;
  }
};

/**
 * 校验密码
 * 规则：8-32位字符，包含字母、数字、符号任意两种及以上组合成的密码
 * @param password
 */
export  const validatePassword = (password) => {
  if (typeof password !== 'string') {
    return false;
  }

  if (password.length < 8 || password.length > 32) {
    return false;
  }

  const hasLetter = /[a-zA-Z]/.test(password);
  const hasNumber = /[0-9]/.test(password);
  const hasSymbol = /[^a-zA-Z0-9]/.test(password);

  const typeCount = [hasLetter, hasNumber, hasSymbol].filter(Boolean).length;

  return typeCount >= 2;
};

/**
 * 校验强密码
 * 规则：8-32位字符，包含字母大小写、数字、符号四种字符组合成的密码
 * @param password
 */
export const validateStrongPassword = (password) => {
  if (typeof password !== 'string') {
    return false;
  }

  if (password.length < 8 || password.length > 32) {
    return false;
  }

  const hasUpper = /[A-Z]/.test(password);
  const hasLower = /[a-z]/.test(password);
  const hasDigit = /[0-9]/.test(password);
  const hasSymbol = /[^A-Za-z0-9]/.test(password); // 非字母数字的都算符号

  return hasUpper && hasLower && hasDigit && hasSymbol;
};