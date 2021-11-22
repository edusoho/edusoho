<template>
  <div class="course-manage">
    <div class="course-manage__header">
      <div class="course-manage__title">
        <slot name="title" />
      </div>
    </div>
    <div class="course-manage__body">
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

<style lang="less" scoped>
.course-manage {
  margin-left: 200px;
  background: #fff;
  border-radius: 0 4px 4px 0;

  @media (max-width: 991px) {
    margin-left: 0;
    border-radius: 4px;
  }

  &__header {
    padding: 24px;
    box-shadow: inset 0 -1px 0 0 rgba(0, 0, 0, 0.08)
  }

  &__title {
    font-size: 16px;
    color: rgba(0, 0, 0, 0.88);
    line-height: 1;
    margin: 0 auto;
    font-weight: 500;
  }

  &__body {
    padding: 24px;
    min-height: 800px;
  }
}
</style>
