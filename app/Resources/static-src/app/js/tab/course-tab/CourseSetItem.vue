<template>
  <div class="my-course-item cd-mb16 clearfix">
    <a class="my-course-item__link relative" :href="'/course/' + courseSet.defaultCourseId">
      <img :src="courseSet.cover.middle || '/static-dist/app/img/vue/goods/empty-course.png'" :alt="courseSet.title" class="my-course-item__picture">
    </a>
    <div class="my-course-item__info">
      <div class="my-course-item__title text-overflow">
        <a class="cd-link-major text-16" :href="'/course/' + courseSet.defaultCourseId">
          {{ courseSet.title }}
        </a>
      </div>
    </div>
    <div class="my-course-item__btn">
      <a class="btn cd-btn cd-btn-primary" :href="'/course/' + courseSet.defaultCourseId" target="_blank">查看课程</a>
    </div>
  </div>
</template>
<script>
export default {
  props: {
    courseSet: {
      type: Object,
      default: () => ({})
    }
  }
}
</script>
