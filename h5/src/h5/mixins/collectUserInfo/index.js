import { Toast } from 'vant';
import Api from '@/api';
export default {
  data() {
    return {
      userInfoCellect: null,
      userInfoCellectForm: {},
      isReqUserInfoCellect: false,
      isRequserInfoCellectForm: false,
      needCollectUserInfo: false,
    };
  },
  computed: {
    hasUserInfoCellectForm() {
      return Object.keys(this.userInfoCellectForm).length > 0;
    },
  },
  methods: {
    //  根据购买前后判断是否需要采集用户信息
    getInfoCollectionEvent(paramsList) {
      const query = {
        action: paramsList.action,
      };
      const params = {
        targetType: paramsList.targetType,
        targetId: paramsList.targetId,
      };

      // eslint-disable-next-line no-async-promise-executor
      return new Promise(async (resolve, reject) => {
        await Api.getInfoCollectionEvent({
          query,
          params,
        })
          .then(res => {
            this.userInfoCellect = res;
            resolve(res);
          })
          .catch(err => {
            reject(err);
            Toast(err.message);
          });
        this.isReqUserInfoCellect = true;
      });
    },
    // 根据事件id获取表单
    getInfoCollectionForm(eventId) {
      const query = {
        eventId,
      };
      // eslint-disable-next-line no-async-promise-executor
      return new Promise(async (resolve, reject) => {
        await Api.getInfoCollectionForm({
          query,
        })
          .then(res => {
            this.userInfoCellectForm = { ...res };
            resolve(res);
          })
          .catch(err => {
            reject(err);
            Toast(err.message);
          });
        this.isRequserInfoCellectForm = true;
      });
    },
  },
};
