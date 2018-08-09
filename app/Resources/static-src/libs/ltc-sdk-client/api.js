const axios = require("axios");

const apiData = [...require("./api.json").api];

const pathMatch = (string, params) => {
  let arr = string.split("/");

  for (let key in arr) {
    if (arr[key].indexOf(":") !== -1) {
      arr[key] = params[arr[key].replace(":", "")];
    }
  }

  return arr.join("/");
};

const api = {};
let instance;

apiData.map(item => {
  api[item.name] = ({ params, data } = { params: {} }) => {
    const url = pathMatch(item.path, params);
    const options = {
      url: url
    };

    if (data) {
      options["data"] = data;
    }

    return instance(options);
  };
});

const Api = options => {
  instance = axios.create(options);
  return api;
};

export default Api;
