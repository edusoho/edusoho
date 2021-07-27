<template>
  <div class="e-vip-item" @click="handleClick">
    <img v-lazy="item.background" class="vip-background" />
    <span class="name text-overflow">{{ item.name }}</span>
    <span class="explain">
      {{ $t('e.courseClass', { numberCourse: item.freeCourseNum, numberClass: item.freeClassroomNum }) }}
    </span>
  </div>
</template>

<script>
export default {
  name: 'EVipItem',
  props: {
    item: {
      type: Object,
      default: () => {
        return {};
      },
    },
    feedback: {
      type: Boolean,
      default: true,
    },
  },
  methods: {
    handleClick() {
      if (!this.feedback) return;
      this.$router.push({
        path: '/vip',
        query: {
          id: this.item.id,
        },
      });
    },
  },
};
</script>
