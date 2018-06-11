<template>
  <div class="e-course">
    <div class="clearfix" @click="onClick">
      <div class="e-course__left pull-left">
        <img v-bind:src="course.image">
      </div>
      <div class="e-course__right pull-left">
        <div class="e-course__title text-overflow">{{ course.courseSetTitil }}</div>
        <div class="e-course__project text-overflow">
          <span v-if="course.title !== '默认教学课程'">{{ course.title }}</span>
        </div>
        <switchBox :type="type" :course="course"></switchBox>
      </div>
    </div>
  </div>
</template>

<script>
  import switchBox from './e-course-switch-box.vue';

  export default {
    props: {
      course: {
        type: Object,
        default: {},
      },
      type: {
        type: String,
        default: 'price'
      }
    },
    data () {
      return {

      };
    },
    components: {
      switchBox,
    },
    created(){
      console.log('course', this.course);
    },
    methods: {
      onClick() {
        const name = this.type === 'order'? 'order' : 'course';
        const id = this.course.id || this.course.targetId;

        this.$router.push({
          path: `${name}/${id}`,
          // params: {
          //   id: this.course.id || this.course.targetId
          // }
        });
      }
    }
  }
</script>
