import Vue from 'vue';

export default (module, startValidate) => {
  // 轮播图
  if (module.type == 'slide_show') {
    for (let num in module.data) {
      const imgUri = module.data[num].image.uri;
      if (!imgUri) {
        if (!startValidate) return true;
        Vue.prototype.$message({
          message: '请完善轮播图模块信息！',
          type: 'error'
        });
        return true;
      }
    }
  }

  // 课程
  if (module.type == 'course_list') {
    const courseExist = module.data.items.length;
    if (!module.data.title || (module.data.sourceType == 'custom' && !courseExist)) {
      if (!startValidate) return true;
      Vue.prototype.$message({
        message: '请完善课程模块信息！',
        type: 'error'
      });
      return true;
    }
  }

  // 班级
  if (data.type == 'class_list') {
    const classExist = data.data.items.length;
    if (!data.data.title || (data.data.sourceType == 'custom' && !classExist)) {
      if (!startValidate) return true;
      Vue.prototype.$message({
        message: '请完善班级模块信息！',
        type: 'error'
      });
      return true;
    }
  }

  // 广告
  if (module.type == 'poster') {
    const imgUri = module.data.image.uri;
    if (!imgUri) {
      if (!startValidate) return true;
      Vue.prototype.$message({
        message: '请完善广告模块信息！',
        type: 'error'
      });
      return true;
    }
  }

  // 营销活动——拼团
  if (module.type == 'groupon') {
    const activityExist = module.data.activity.id;
    if (!activityExist) {
      if (!startValidate) return true;
      Vue.prototype.$message({
        message: '请完善拼团模块信息！',
        type: 'error'
      });
      return true;
    }
  }

  return false;
}
