<template>
  <div class="e-course">
    <div class="clearfix" @click="onClick">
      <div class="e-course__left pull-left">
        <img :class="course.imgSrc.className" v-bind:src="course.imgSrc.url">
        <div v-if="normalTagShow">
          <span class="tag tag-live" v-if="courseType === 'live'">直播</span>
          <span class="tag tag-discount" v-if="discountNum">{{discountNum}}</span>
        </div>
        <span class="tag tag-vip" v-if="vipTagShow && Number(isVip)">会员免费</span>
      </div>
      <div class="e-course__right pull-left">
        <!-- header -->
        <div class="e-course__header text-overflow">{{ course.header }}</div>
        <!-- middle -->
        <div class="e-course__middle">
          <div v-if="course.middle.value" v-html="course.middle.html"></div>
        </div>
        <!-- bottom -->
        <div class="e-course__bottom" v-html="course.bottom.html"></div>
      </div>
    </div>
  </div>
</template>

<script>

  export default {
    props: {
      course: {
        type: Object,
        default() {
          return {}
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
      discount: {
        type: String,
        default: '10'
      },
      feedback: {
        type: Boolean,
        default: true,
      },
      typeList: {
        type: String,
        default: 'course_list'
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
        pathName: this.$route.name,
      };
    },
    computed: {
      discountNum() {
        if (this.typeList === 'class_list') return false;
        if (this.discount !== '') {
          const discount = Number(this.discount);
          if (discount === 10) return false;
          if (discount == 0) return '限免';
          return discount + '折';
        }
      }
    },
    watch: {
      course: {
        handler(course) {
          // 小程序后台替换图片协议
          const courseSet = course.courseSet;
          if (this.pathName !== 'h5Setting' && courseSet) {
            const keys = Object.keys(courseSet.cover);
            for (var i = 0; i < keys.length; i++) {
              courseSet.cover[keys[i]] = courseSet.cover[keys[i]].replace(/^(\/\/)|(http:\/\/)/, 'https://');
            }
          }
        },
        immediate: true,
      }
    },
    methods: {
      onClick(e) {
        if (!this.feedback) {
          return;
        }
        const isOrder = this.type === 'order';
        const id = this.course.id || this.course.targetId;
        if (e.target.tagName === 'SPAN') {
          return;
        }
        if (isOrder) {
          location.href = this.order.targetUrl;
          return;
        }
        this.$router.push({
          path: (this.typeList === 'course_list') ? `/course/${id}` : `/classroom/${id}`,
        });
      }
    }
  }
</script>
