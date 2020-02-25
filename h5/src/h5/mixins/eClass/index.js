import { mapState } from 'vuex';

export default {
  props: {
    course: {
      type: Object,
      default() {
        return {};
      }
    },
    type: {
      type: String,
      default: 'price'
    },
    courseType: {
      type: String,
      default: 'normal'
    },
    discountType: {
      type: String,
      default: 'discount'
    },
    discount: {
      type: String,
      default: '10'
    },
    feedback: {
      type: Boolean,
      default: true
    },
    typeList: {
      type: String,
      default: 'course_list'
    },
    isAppUse: {
      type: Boolean,
      default: false
    },
    normalTagShow: {
      type: Boolean,
      default: true
    },
    vipTagShow: {
      type: Boolean,
      default: false
    },
    isVip: {
      type: String,
      default: '0'
    }
  },
  data() {
    return {
      pathName: this.$route.name
    };
  },
  computed: {
    ...mapState(['vipSwitch', 'isLoading']),
    discountNum() {
      if (this.typeList === 'class_list') return false;
      if (this.discount !== '') {
        const discount = Number(this.discount);
        // 减价
        if (this.discountType === 'reduce') {
          return `减${discount}`;
        }
        // 打折
        if (this.discountType === 'discount') {
          if (discount === 10) return false;
          if (discount == 0) return '限免';
          return `折${discount}`;
        }
      }
    }
  },
  watch: {
    course: {
      handler(course) {
        // 小程序后台替换图片协议
        const courseSet = course.courseSet;
        if (this.pathName === 'miniprogramSetting' && courseSet) {
          const keys = Object.keys(courseSet.cover);
          for (let i = 0; i < keys.length; i += 1) {
            courseSet.cover[keys[i]] = courseSet.cover[keys[i]].replace(
              /^(\/\/)|(http:\/\/)/,
              'https://'
            );
          }
        }
      },
      immediate: true
    }
  },
  methods: {
    onClick(e) {
      const isOrder = this.type === 'order';
      const id = this.course.id || this.course.targetId;
      if (!this.feedback) {
        return;
      }
      if (this.isAppUse) {
        this.postMessage(this.typeList, id);
        return;
      }
      if (e.target.tagName === 'SPAN') {
        return;
      }
      if (isOrder) {
        location.href = this.order.targetUrl;
        return;
      }
      this.$router.push({
        path:
          this.typeList === 'course_list' ? `/course/${id}` : `/classroom/${id}`
      });
    },
    // 调用app接口
    postMessage(type, id) {
      let action;
      let data = {};
      if (type === 'course_list') {
        action = 'kuozhi_course';
        data = { courseId: id };
      } else {
        action = 'kuozhi_classroom';
        data = { classroomId: id };
      }
      // 调用app接口
      window.postNativeMessage({ action, data });
    }
  }
};
