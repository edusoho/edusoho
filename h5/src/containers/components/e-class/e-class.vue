<template>
  <div class="e-course">
    <div class="clearfix" @click="onClick">
      <div class="e-course__left pull-left">
        <img :class="course.imgSrc.className" v-bind:src="course.imgSrc.url">
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
      feedback: {
        type: Boolean,
        default: true,
      }
    },
    data() {
      return {
        pathName: this.$route.name,
      };
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
          console.log(e.target.tagName);
          return;
        }
        if (isOrder) {
          location.href = this.order.targetUrl;
          return;
        }
        this.$router.push({
          path: `/course/${id}`,
        });
      }
    }
  }
</script>
