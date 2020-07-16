import { mapState } from 'vuex';

export default {
  props: {
    course: {
      type: Object,
      default() {
        return {};
      },
    },
    type: {
      type: String,
      default: 'price',
    },
    courseType: {
      type: String,
      default: 'normal',
    },
    discountType: {
      type: String,
      default: 'discount',
    },
    discount: {
      type: String,
      default: '10',
    },
    feedback: {
      type: Boolean,
      default: true,
    },
    typeList: {
      type: String,
      default: 'course_list',
    },
    isAppUse: {
      type: Boolean,
      default: false,
    },
    normalTagShow: {
      type: Boolean,
      default: true,
    },
    vipTagShow: {
      type: Boolean,
      default: false,
    },
    isVip: {
      type: String,
      default: '0',
    },
  },
  data() {
    return {
      pathName: this.$route.name,
    };
  },
  computed: {
    ...mapState(['vipSwitch', 'isLoading']),
    discountNum() {
      const discount = Number(this.discount);
      if (this.typeList === 'class_list' || isNaN(discount)) return false;
      // 减价
      if (this.discountType === 'reduce') {
        return `减${discount}`;
      }
      // 打折
      if (this.discountType === 'discount') {
        if (discount === 10) return false;
        if (discount === 0) return '限免';
        return `${discount}折`;
      }
      return false;
    },
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
              'https://',
            );
          }
        }
      },
      immediate: true,
    },
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
      this.toMore(this.typeList, id);
    },
    toMore(type, id) {
      let path = '';
      switch (type) {
        case 'course_list':
          path = `/course/${id}`;
          break;
        // case 'item_bank_exercise':
        //   path = `/course/${id}`;
        //   break;
        case 'classroom_list':
          path = `/classroom/${id}`;
          break;
      }
      this.$router.push({ path: path });
    },
    // 调用app接口
    postMessage(type, id) {
      let action = '';
      let data = {};
      switch (type) {
        case 'course_list':
          action = 'kuozhi_course';
          data = { courseId: id };
          break;
        case 'item_bank_exercise':
          action = 'kuozhi_itembank';
          data = { exerciseId: id };
          break;
        case 'classroom_list':
          action = 'kuozhi_classroom';
          data = { classroomId: id };
          break;
      }
      // 调用app接口
      window.postNativeMessage({ action, data });
    },
  },
};
