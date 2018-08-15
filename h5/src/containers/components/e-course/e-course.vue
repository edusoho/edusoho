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
      }
    },
    computed: {
      imgSrc() {
        return this.course.courseSet ? this.course.courseSet.cover.middle : this.order.cover.middle
      },
      title() {
        return this.course.courseSetTitle
          || (this.course.courseSet ? this.course.courseSet.title : '')
          || this.order.title;
      },
      teachPlan() {
        if (this.course.title) {
          return this.course.title
        } else {
          return false
        }
      }
    },
    methods: {
      onClick(e) {
        const name = this.type === 'order' ? 'order' : 'course';
        const id = this.course.id || this.course.targetId || this.order.targetId;
        if (e.target.tagName === 'SPAN') {
          console.log(e.target.tagName);
          return;
        }
        this.$router.push({
          path: `/course/${id}`,
        });
      }
    }
  }
</script>
