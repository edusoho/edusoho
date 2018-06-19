<template>
  <e-panel title="教师介绍">
    <template v-for="item in teacherInfo">
      <div class="teacher-content">
        <img :src="item.avatar.large" 
        class="teacher-avatar-img">
        <div class="teacher-cell">
          <span class="dark font-family-PFM">{{item.nickname}}</span>
          <span class="text-content">{{item.title || '暂无简介'}}</span>
        </div>
      </div>
    </template>
  </e-panel>
</template>
<script>
export default {
  name: 'teacher',
  props: ['teacherInfo'],
  created() {
    console.log('teacherInfo', this.teacherInfo)
  }
}
</script>

