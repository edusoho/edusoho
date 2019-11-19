<template>
  <e-panel title="教师介绍">
    <template v-for="item in teacherInfo">
      <div class="teacher-content">
        <img
          :src="item.avatar.large"
          class="avatar-img">
        <div class="teacher-cell">
          <span class="dark font-family-PFM">{{ item.nickname }}</span>
          <span v-show="item.title" class="text-content">{{ item.title }}</span>
        </div>
      </div>
    </template>
  </e-panel>
</template>
<script>
export default {
  name: 'Teacher',
  props: ['teacherInfo']
}
</script>

