<template>
  <div class="e-vip-item" @click="handleClick">
    <img class="vip-background" :src="item.background"/>
    <span class="name">{{ item.name }}</span>
    <span class="explain">{{ item.freeCourseNum }}门课程 {{ item.freeClassroomNum }}门班级</span>
  </div>
</template>

<script>

export default {

  name: 'e-vip-item',
  props: {
    item: {
      type: Object,
      default: () => {
        return {}
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
      });
    }
  }
}
</script>
