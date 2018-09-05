import Vue from 'vue';

export default (data) => {
  // 轮播图
  if (data.type == 'slide_show') {
    for (let num in data.data) {
      const imgUri = data.data[num].image.uri;
      if (!imgUri) {
        Vue.prototype.$message({
          message: '请完善轮播图模块信息！',
          type: 'error'
        });
        return true;
      }
    }
  }

  // 课程
  if (data.type == 'course_list') {
    const courseExist = data.data.items.length;
    if (!data.data.title || (data.data.sourceType == 'custom' && !courseExist)) {
      Vue.prototype.$message({
        message: '请完善课程模块信息！',
        type: 'error'
      });
      return true;
    }
  }

  // 广告
  if (data.type == 'poster') {
    const imgUri = data.data.image.uri;
    if (!imgUri) {
      Vue.prototype.$message({
        message: '请完善广告模块信息！',
        type: 'error'
      });
      return true;
    }
  }
}
