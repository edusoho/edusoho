import { Toast } from 'vant';
import Api from '@/api';
export default {
  data() {
    return {
      userInfoCellect: null,
      userInfoCellectForm: {},
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

      return new Promise((resolve, reject) => {
        Api.getInfoCollectionEvent({
          query,
          params,
        })
          .then(res => {
            this.userInfoCellect = res;
            resolve(res);
          })
          .catch(err => {
            this.userInfoCellect = {};
            reject(err);
            Toast(err.message);
          });
      });
    },
    // 根据事件id获取表单
    getInfoCollectionForm() {
      const query = {
        eventId: this.userInfoCellect.id,
      };
      return new Promise((resolve, reject) => {
        Api.getInfoCollectionForm({
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
      });
    },
  },
};
