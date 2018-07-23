import axios from 'axios';

// API配置
import config from '@/api/config';

// 拦截器
import '@/api/interceptors';
import { parseUrl, addPrefix } from './utils';

// axios.defaults.baseURL = 'http://zyc.st.edusoho.cn';

const Api = {};

const axiosApi = () => {
  config.map(item => {
    Api[item.name] = options => {
      let url = item.url;
      url = options && options.query ? parseUrl(url, options.query) : url;
      url = addPrefix(url);

      return axios(Object.assign({}, item, options, { url }));
    };
    return item;
  });
};

axiosApi();

export default Api;
