<template>
  <div class="class-catalog">
    <div
      class="class-catalog__item clearfix"
      v-for="course in courses"
      :key="course.id"
    >
      <div class="item-img pull-left">
        <img :src="course.courseSet.cover.small" alt="" />
      </div>
      <div class="item-info pull-left">
        <p class="item-info__title text-overflow">
          {{ course.courseSet.title }}
        </p>
        <p class="item-info__price">￥{{ course.price }}</p>
        <p class="item-info__plan clearfix">
          <span class="pull-left">{{ course.displayedTitle }}</span>
          <span class="pull-right">共{{ course.compulsoryTaskNum }}课时</span>
        </p>
      </div>
    </div>
    <div v-if="page < page_count" class="load-more__footer" @click="loadMore">
      点击查看更多
    </div>
    <div v-if="page >= page_count" class="load-more__footer">
      没有更多了
    </div>
  </div>
</template>

<script>
const PAGE_NUM = 5;
export default {
  data() {
    return {
      page: 1, // 当前分页
      page_count: '', // 总分页
      courses: [],
    };
  },
  props: {
    /**
     * 作假分页，无须请求远程
     */
    classroomCourses: {
      type: Array,
      default: () => [],
    },
  },
  methods: {
    loadMore: function() {
      const start = this.page * PAGE_NUM;
      this.courses = this.courses.concat(
        this.classroomCourses.slice(start, start + PAGE_NUM),
      );
      this.page += 1;
    },
  },
  watch: {
    classroomCourses: {
      immediate: true,
      handler(val) {
        this.page_count = Math.ceil(val.length / PAGE_NUM);
        this.courses = val.slice(0, PAGE_NUM);
      },
    },
  },
};
</script>
