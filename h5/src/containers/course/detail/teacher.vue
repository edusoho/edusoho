<template>
  <e-panel title="教师介绍">
    <img src="https://mp1.cg-dev.cn/files/default/2018/02-09/121922a8077c938304.jpg" 
      alt="" class="teacher-avatar-img">
    <div class="teacher-cell">
      <span class="dark font-family-PFM">{{teacherInfo.nickname}}</span>
      <span class="text-content">{{teacherInfo.title || '暂无简介'}}</span>
    </div>
  </e-panel>
</template>
<script>
export default {
  name: 'teacher',
  props: ['teacherInfo']
}
</script>

