<template>
  <div class="classroom-task">
    <div class="classroom-task__item" v-for="course in courses" :key="course.id" @click="clickCourse(course)">
      <div class="task-banner">
        <img :src="course.courseSet.cover.small" alt="">
      </div>
      <div class="task-content">
        <p class="task-content__title text-overflow">{{ course.courseSet.title }}</p>
        <p class="task-content__plan text-overflow">
          {{ 'classroom.courses.plan'|trans }}：{{ course.displayedTitle }}
          <span>{{ 'classroom.courses.lesson_num'|trans({num:course.compulsoryTaskNum}) }}</span>
        </p>
        <div v-if="course.teachers[0]" class="clearfix task-content__teacher">
          <div class="pull-left teacher-info">
            <img :src="course.teachers[0].avatar.small" alt="">
            <span>{{ course.teachers[0].nickname }}</span>
          </div>
          <span class="pull-right price-info">{{ course.price }}</span>
        </div>
      </div>
    </div>
    <div v-if="page < page_count" class="learn-more" @click="loadMore"><a href="javascript:;">{{ 'site.local_more'|trans }}<i class="es-icon es-icon-chevronright"></i></a></div>
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
            clickCourse: function(course) {
                window.open('/my/course/'+course.id);
            }
        },
        filters: {
            trans(value, params) {
                if (!value) return '';
                return Translator.trans(value, params);
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