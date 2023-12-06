<template>
  <div class="classroom-task">
    <div class="classroom-task__item" v-for="course in courses" :key="course.id" @click="clickCourse(course)">
      <div class="task-banner">
        <img :src="course.courseSet.cover.middle" alt="">
      </div>
      <div class="task-content">
        <p class="task-content__title text-overflow">{{ course.courseSet.title | removeHtml }}</p>
        <p class="task-content__plan text-overflow">
          {{ 'classroom.courses.plan'|trans }}：{{ course.displayedTitle }}
          <span>{{ 'classroom.courses.lesson_num'|trans({num:course.compulsoryTaskNum}) }}</span>
        </p>
        <div class="clearfix task-content__teacher">
          <div v-if="course.teachers[0]" class="pull-left teacher-info">
            <img :src="course.teachers[0].avatar.small" alt="">
            <span>{{ course.teachers[0].nickname }}</span>
          </div>
          <span v-if="course.originPrice2.currency === 'coin'" class="pull-right coin-info">{{ course.originPrice2.coinAmount }}{{ course.originPrice2.coinName }}</span>
          <span v-if="course.originPrice2.currency === 'RMB'" class="pull-right price-info">{{ course.originPrice2.amount }}</span>
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
        mounted() { 
          console.log(this.classroomCourses);
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
              if(course.canLearn == '0') {
                return this.$message.error(Translator.trans('goods.show_page.tab.classroom.closed_tip'));
              }
                
              return window.open('/course/'+course.id);
            }
        },
        filters: {
            trans(value, params) {
                if (!value) return '';
                return Translator.trans(value, params);
            },
            removeHtml(input) {
                return input && input.replace(/<(?:.|\n)*?>/gm, '')
                    .replace(/(&rdquo;)/g, '\"')
                    .replace(/&ldquo;/g, '\"')
                    .replace(/&mdash;/g, '-')
                    .replace(/&nbsp;/g, '')
                    .replace(/&amp;/g, '&')
                    .replace(/&gt;/g, '>')
                    .replace(/&lt;/g, '<')
                    .replace(/<[\w\s"':=\/]*/, '');
          }

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