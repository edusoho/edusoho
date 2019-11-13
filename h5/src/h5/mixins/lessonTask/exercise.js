
import { Dialog } from 'vant';
/**
 * 异常离开或者页面刷新作业监控，用户可选直接提交和继续做题
 * promise函数，reject代码要交卷，resolve代表继续做题
 */

export default {
  methods: {
    // 继续答题或提交
    canDoing(result, userId) {
      return new Promise((resolve, reject) => {
        if (result && result.status === 'doing') {
          // 获取localstorge数据
          const answerName = `exercise-${userId}-${result.id}`;
          let answer = JSON.parse(localStorage.getItem(answerName));

          // 本地是否有answer缓存，没有则为一个空对象
          if (answer) {
            answer = Object.keys(answer).forEach(key => {
              answer[key] = answer[key].filter(t => t !== '');
            });
          } else {
            answer = {};
          }

          Dialog.confirm({
            title: '提示',
            cancelButtonText: '放弃做题',
            confirmButtonText: '继续做题',
            message: '您有未完成的练习，是否继续？'
          }).then(() => {
            resolve();
          })
            .catch(() => {
              reject({ answer });
            });
        }
      });
    }
  }
};
