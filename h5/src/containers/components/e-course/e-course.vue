<template>
  <div class="e-course">
    <div class="clearfix" @click="onClick">
      <div class="e-course__left pull-left">
        <img v-bind:src="imgSrc">
      </div>
      <div class="e-course__right pull-left">
        <div class="e-course__title text-overflow">{{ title }}</div>
        <div class="e-course__project text-overflow">
          <span v-if="teachPlan">{{ teachPlan }}</span>
        </div>
        <switchBox :type="type" :course="course" :order="order" :studentNum="course.studentNum" :publishedTaskNum="course.publishedTaskNum"></switchBox>
      </div>
    </div>
  </div>
</template>

<script>
  import switchBox from './e-course-switch-box.vue';

  export default {
    components: {
      switchBox,
    },
    props: {
      course: {
        type: Object,
        default() {
          return {}
        }
      },
      order: {
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
    computed: {
      imgSrc() {
        const courseSet = this.course.courseSet;
        return courseSet ? courseSet.cover.middle : this.order.cover.middle;
      },
      title() {
        return this.course.courseSetTitle
          || (this.course.courseSet ? this.course.courseSet.title : '')
          || this.order.title;
      },
      teachPlan() {
        if (this.course.title) {
          return this.$route.query.planTitle || this.course.title
        } else {
          return false
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
