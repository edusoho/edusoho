<template>
  <div class="flex mx-16 mb-12 bg-text-1 text-12 relative" style="height: 94px;border-radius: 6px;" @click="onClick(course.hasCertificate, $event)">
    <div :class="errImgUrl ? 'afterBack' : ''"></div>
    <div class="relative" style="width: 170px;height: 100%;border-radius: 6px 0 0 6px;">
      <img class="h-full w-full course-img" v-lazy="course.imgSrc.url" style="border-radius: 6px 0 0 6px;" />
      <div v-if="Number(isVip)" class="absolute left-0 bottom-0 px-8 text-white text-12 font-medium bg-black bg-opacity-80" style="padding-top: 2px; padding-bottom: 2px; line-height: 20px; border-bottom-left-radius: 6px; border-top-right-radius: 12px; z-index: 10;">会员免费</div>
      <div v-if="course.videoMaxLevel === '2k' && !Number(isVip)" class="absolute left-0 bottom-0 px-8 text-white text-12 font-medium bg-black bg-opacity-80" style="padding-top: 2px; padding-bottom: 2px; line-height: 20px; border-bottom-left-radius: 6px; border-top-right-radius: 12px; z-index: 10;">2K 优享</div>
      <div v-if="course.videoMaxLevel === '4k' && !Number(isVip)" class="absolute left-0 bottom-0 px-8 text-[#492F0B] text-12 font-medium bg-gradient-to-l from-[#F7D27B] to-[#FCEABE]" style="padding-top: 2px; padding-bottom: 2px; line-height: 20px; border-bottom-left-radius: 6px; border-top-right-radius: 12px; z-index: 10;">4K 臻享</div>
      <div v-if="courseType === 'live'" class="absolute bottom-0 w-full text-white font-medium text-12 bg-black bg-opacity-40 rounded-bl-6 text-right flex items-center flex-row-reverse" style="padding: 4px 6px;line-height: 12px;">直播<span class="inline-block w-4 h-4 bg-[#60F1A3] mr-4 text-right" style="border-radius: 9999px;"></span></div>
    </div>
    <div class="flex flex-col justify-between flex-1 p-12 relative" style="width: calc(100% - 170px);border-radius: 0 6px 6px 0;">
      <img v-if="isShowErrImg" class="err-img" :src="errImgUrl" />
      <div class="flex font-bold text-text-5">
        <div v-if="discountNum" style="width: 14px;height:14px;margin:3px 4px 0 0;text-align: center;line-height: 14px;border: 1px solid #ff900e;border-radius: 2px;">
          <div style="font-size: 12px; transform: scale(0.75); color: #FF900E;">{{ $t('e.discount') }}</div>
        </div>
        <div v-if="course.hasCertificate" style="width: 14px;height:14px;margin:3px 4px 0 0;text-align: center;line-height: 14px;border: 1px solid #3DCD7F;border-radius: 2px;">
          <div style="font-size: 12px; transform: scale(0.75); color: #3DCD7F;">{{ $t('e.certificate') }}</div>
        </div>
        <div class="text-14 text-overflow ">{{ course.header }}</div>
      </div>

      <div v-if="course.middle.value" class="text-text-3 text-12 text-overflow">{{ course.middle.value }}</div>
      <div
        class="e-course__bottom"
        v-html="course.bottom.html"
      />
    </div>
  </div>
</template>

<script>
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
    normalTagShow: {
      type: Boolean,
      default: true,
    },
    vipTagShow: {
      type: Boolean,
      default: false,
    },
    isVip: {
      type: [String, Number],
      default: '0',
    },
    showNumberData: {
      type: String,
      default: '',
    },
    vipCenter: {
      type: Boolean,
      default: false,
    },
  },
  data() {
    return {
      pathName: this.$route.name,
    };
  },
  computed: {
    ...mapState(['vipSwitch', 'isLoading']),
    // eslint-disable-next-line vue/return-in-computed-property
    discountNum() {
      if (this.typeList === 'class_list') return false;
      if (this.discount !== '') {
        const discount = Number(this.discount);
        // 减价
        if (this.discountType === 'reduce') {
          return `${this.$t('e.reduction')}${discount}`;
        }
        // 打折
        if (this.discountType === 'discount') {
          if (discount === 10) return false;
          if (discount == 0) return this.$t('e.limitedExemption');
          return `${discount}${this.$t('e.discount')}`;
        }
      }
    },
    isShowErrImg() {
      if(this.course?.bottom?.data?.courseSet?.status == 'closed') {
        return true;
      }

      if(this.course?.bottom?.data?.status == 'closed') {
        return true;
      }

      if(this.course?.bottom?.data?.isExpired) {
        return true;
      }

      return false;
    },
    errImgUrl() {
      if(this.course?.bottom?.data?.courseSet?.status == 'closed') {
        return 'static/images/closed.png';
      }

      if(this.course?.bottom?.data?.status == 'closed') {
        return 'static/images/closed.png';
      }

      if(this.course?.bottom?.data?.isExpired) {
        return 'static/images/expired.png';
      }

      return '';
    }
  },
  watch: {
    course: {
      handler(course) {
        // 小程序后台替换图片协议
        const courseSet = course.courseSet;
        if (this.pathName !== 'h5Setting' && courseSet) {
          const keys = Object.keys(courseSet.cover);
          for (let i = 0; i < keys.length; i++) {
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
      if (!this.feedback) {
        return;
      }
      const isOrder = this.type === 'order';
      // const id = this.course.id || this.course.targetId;
      if (e.target.tagName === 'SPAN') {
        return;
      }
      if (isOrder) {
        location.href = this.order.targetUrl;
        return;
      }

      if (this.typeList === 'class') {
        return;
      }

      if(this.typeList === 'classroom_course_list') {
        this.$router.push({
          path: `/course/${this.course.id}`
        });
      }

      if (this.typeList === 'classroom_list') {
        this.$router.push({
          path: `/goods/${this.course.goodsId}/show`,
          query: {
            targetId: this.course.id,
            type: 'classroom_list',
            hasCertificate,
          },
        });
      }

      if (this.typeList === 'course_list') {
        this.$router.push({
          path: `/goods/${this.course.goodsId}/show`,
          query: {
            targetId: this.course.id,
            type: 'course_list',
            hasCertificate,
          },
        });
      }
    },
  },
};
</script>

<style lang="scss" scoped>

.afterBack {
  background: rgba(255, 255, 255, 0.60);
  position: absolute;
  width: 100%;
  height: 100%;
  z-index: 1;
}
  .tag-live {
    position: absolute;
    // left: -3px;
    // top: -1px;
    right: 0px;
    bottom: -1px;
    padding: 2px 8px;
    color: #fff;
    font-weight: 500;
    line-height: 14px;
    font-size: 12px;
    background-color: #FF900E;
    // border-radius: 6px 0;
    border-radius: 6px 0 0;
    transform: scale(0.83);
  }

  .tag-vip {
    position: absolute;
    // right: -1px;
    // top: -1px;
    left: -5px;
    bottom: -1px;
    padding: 2px 8px;
    color: #fff;
    font-weight: 500;
    line-height: 14px;
    font-size: 12px;
    background-color: #162923;
    // border-radius: 0 0 0 6px;
    border-radius: 0 6px;
    transform: scale(0.83);
  }

  .err-img {
    position: absolute;
    height: 40px;
    top: 0;
    right: 0;
    z-index: 2;
  }
</style>
