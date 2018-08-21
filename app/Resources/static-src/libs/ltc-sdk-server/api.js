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
  api[item.name] = ({ pathParams, data, queryParams } = { pathParams: {} }) => {
    const url = pathMatch(item.path, pathParams);
    const method = item.method;
    const options = {
      url: url,
      method: method
    };

    if (data) {
      options["data"] = data;
    }
    if (queryParams) {
      options["params"] = queryParams;
    }

    return instance(options);
  };
});

const Api = options => {
  instance = axios.create(options);
  return api;
};

export default Api;
