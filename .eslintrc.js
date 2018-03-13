module.exports = {
    "env": {
        "browser": true,
        "commonjs": true,
        "es6": true,
        "jquery": true,
    },
    "globals": {
      "Translator": true,
      "echo": true,
      "res": true,
      "define": true,
      "app": true,
      "Waypoint": true,
      "cd": true,
      "CKEDITOR": true,
      "Browser": true,
      "notify": true,
      "echarts": true,
      "VideoPlayerSDK": true,
      "WeixinJSBridge": true,
      "UploaderSDK": true,
      "QiQiuYun": true,
      "noUiSlider": true,
      "introJs": true,
      "Handlebars": true,
      "store": true,
    },
    "extends": "eslint:recommended",
    "parserOptions": {
        "ecmaFeatures": {
            "experimentalObjectRestSpread": true,
            "jsx": true
        },
        "sourceType": "module",
        "ecmaVersion": 6,
    },
    "plugins": [
        "react"
    ],
    "rules": {
        "no-useless-escape": 0,
        "no-console": 0,
        "no-empty": 2,
        "no-unused-vars": 0,
        "indent": [
            "error",
            2
        ],
        "linebreak-style": [
            "error",
            "unix"
        ],
        "quotes": [
            "error",
            "single"
        ],
        "semi": [
            "error",
            "always"
        ]
    }
};