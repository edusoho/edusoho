<template>
  <div class="e-course">
    <div class="clearfix" @click="onClick(course.hasCertificate, $event)">
      <div class="e-course__left pull-left">
        <img v-lazy="course.imgSrc.url" :class="course.imgSrc.className" />
        <div v-if="normalTagShow">
          <span v-if="courseType === 'live'" class="tag tag-live">{{ $t('e.live') }}</span>
          <span v-if="discountNum" class="tag tag-discount">{{
            discountNum
          }}</span>
        </div>
        <span
          v-if="vipTagShow && vipSwitch && Number(isVip)"
          class="tag tag-vip"
          >{{ $t('e.freeForMembers') }}</span
        >
      </div>
      <div class="e-course__right pull-left">
        <!-- header -->
        <div class="e-course__header text-overflow">
          <span class="certificate-icon" v-if="course.hasCertificate">{{ $t('e.certificate') }}</span
          >{{ course.header }}
        </div>
        <!-- middle -->
        <div class="e-course__middle">
          <div
            v-if="course.middle.value && !vipCenter"
            v-html="course.middle.html"
          />
        </div>
        <!-- bottom -->
        <div
          class="e-course__bottom"
          v-if="!vipCenter"
          v-html="course.bottom.html"
        />
        <div class="e-course__bottom" v-else v-html="course.middle.vipHtml" />
      </div>
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
