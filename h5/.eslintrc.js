module.exports = {
  // 限定配置文件的使用范围
  root: true,
  // 指定代码运行的宿主环境
  env: {
    browser: true,
    es6: true,
    node: true,
  },
  // 指定eslint规范
  extends: ['plugin:vue/essential', 'standard', 'plugin:prettier/recommended'],
  // 声明在代码中的自定义全局变量
  globals: {
    Atomics: 'readonly',
    SharedArrayBuffer: 'readonly',
  },
  parser: 'vue-eslint-parser',
  // 设置解析器选项
  parserOptions: {
    parser: 'babel-eslint', // 指定eslint的解析器
    ecmaVersion: 11,
    sourceType: 'module',
  },
  // 引用第三方的插件
  plugins: ['vue'],
  // 启用额外的规则或覆盖默认的规则
  rules: {
    'no-var': 2,
    'prettier/prettier': 'error',
  },
};