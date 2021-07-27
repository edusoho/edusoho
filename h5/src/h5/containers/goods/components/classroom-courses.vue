<template>
  <div class="class-catalog">
    <div
      class="class-catalog__item clearfix"
      v-for="course in courses"
      :key="course.id"
      @click="gotoCourse(course)"
    >
      <div class="item-img pull-left">
        <img :src="course.courseSet.cover.small" alt="" />
      </div>
      <div class="item-info pull-left">
        <p class="item-info__title text-overflow">
          {{ course.courseSet.title }}
        </p>
        <p
          class="item-info__price"
          v-if="course.originPrice2.currency === 'coin'"
        >
          {{ course.originPrice2.coinAmount }}{{ course.originPrice2.coinName }}
        </p>
        <p
          class="item-info__price"
          v-if="course.originPrice2.currency === 'RMB'"
        >
          ￥{{ course.originPrice2.amount }}
        </p>
        <p class="item-info__plan clearfix">
          <span class="pull-left item-info__plan-mw text-overflow">{{
            course.title
          }}</span>
          <span class="pull-right">共{{ course.compulsoryTaskNum }}{{ $t('goods.lesson') }}</span>
        </p>
      </div>
    </div>
    <div v-if="page < page_count" class="load-more__footer" @click="loadMore">
      {{ $t('goods.more') }}
    </div>
    <div v-if="page >= page_count" class="load-more__footer">
      {{ $t('goods.noMore') }}
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
    gotoCourse: function(course) {
      this.$router.push({
        path: `/course/${course.id}`,
      });
    },
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
