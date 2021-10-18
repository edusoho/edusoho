<template>
  <div class="cd-main">
    <div class="cd-main__heading">
      <div class="cd-main__title"><slot name="title" /></div>
    </div>
    <div class="cd-main__body">
      <slot />
    </div>
  </div>
</template>

<script>
// 用于课程管理页，原有 twig 的 main_body 部分
export default {
  name: 'CourseManageLayout'
}
</script>
