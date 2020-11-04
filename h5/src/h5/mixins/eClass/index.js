import { mapState } from 'vuex';
import Api from '@/api';

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
    onClick(hasCertificate, e) {
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
      this.toMore(hasCertificate, this.typeList, id);
    },
    toMore(hasCertificate, type, id) {
      let path = '';
      let params = {
        hasCertificate,
      };
      switch (type) {
        case 'course_list':
          Api.meCourseMember({
            query: {
              id: this.course.id,
            },
          })
            .then(res => {
              if (res.id) {
                path = `/course/${this.course.id}`;
              } else {
                path = `/goods/${this.course.goodsId}/show`;
                params = Object.assign(params, {
                  targetId: id,
                });
              }
              this.$router.push({ path: path, query: params });
            })
            .catch(() => {
              path = `/goods/${this.course.goodsId}/show`;
              params = Object.assign(params, {
                targetId: id,
              });
              this.$router.push({ path: path, query: params });
            });
          break;
        case 'item_bank_exercise':
          path = `/item_bank_exercise/${id}`;
          this.$router.push({ path: path, query: params });
          break;
        case 'classroom_list':
          Api.meClassroomMember({
            query: {
              id: this.course.id,
            },
          })
            .then(res => {
              if (res.id) {
                path = `/classroom/${this.course.id}`;
              } else {
                path = `/goods/${this.course.goodsId}/show`;
              }
              this.$router.push({ path: path, query: params });
            })
            .catch(() => {
              path = `/goods/${this.course.goodsId}/show`;
              this.$router.push({ path: path, query: params });
            });
          break;
      }
    },
    // 调用app接口
    postMessage(type, id) {
      let action = '';
      let data = {};
      switch (type) {
        case 'course_list':
          action = 'kuozhi_course';
          data = {
            courseId: id,
            goodsId: this.course.goodsId,
            specsId: this.course.specsId,
          };
          break;
        case 'item_bank_exercise':
          action = 'kuozhi_itembank';
          data = { exerciseId: id };
          break;
        case 'classroom_list':
          action = 'kuozhi_classroom';
          data = {
            classroomId: id,
            goodsId: this.course.goodsId,
            specsId: this.course.specsId,
          };
          break;
      }
      // 调用app接口
      window.postNativeMessage({ action, data });
    },
  },
};
