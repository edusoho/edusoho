<template>
  <div class="e-course">
    <div class="clearfix" @click="onClick">
      <div class="e-course__left pull-left">
        <img v-lazy="imgSrc" :class="imgClass" />
      </div>
      <div class="e-course__right pull-left">
        <div
          v-if="type === 'confirmOrder'"
          class="e-course__title course-confirm-title"
        >
          {{ title }}
        </div>
        <div v-else>
          <div class="e-course__title text-overflow">{{ title }}</div>
          <div v-if="typeList === 'classroom_list'" class="e-course__count">
            {{ $t('e.totalOfTwoCourses', { number: course.courseNum }) }}
          </div>
          <div
            v-if="typeList === 'course_list'"
            class="e-course__project text-overflow"
          >
            <span v-if="teachPlan">{{ teachPlan }}</span>
          </div>
        </div>
        <switchBox
          :type="type"
          :course="course"
          :order="order"
          :student-num="course.studentNum"
          :published-task-num="course.publishedTaskNum"
        />
      </div>
    </div>
  </div>
</template>

<script>
import switchBox from './e-course-switch-box.vue';

export default {
  components: {
    switchBox,
  },
  props: {
    course: {
      type: Object,
      default() {
        return {};
      },
    },
    order: {
      type: Object,
      default() {
        return {};
      },
    },
    type: {
      type: String,
      default: 'price',
    },
    feedback: {
      type: Boolean,
      default: true,
    },
    typeList: {
      type: String,
      default: 'course_list',
    },
    duration: {
      type: [Number, String],
      default: 0,
    },
  },
  data() {
    return {
      pathName: this.$route.name,
    };
  },
  computed: {
    imgSrc() {
      if (this.typeList === 'classroom_list') {
        return this.course.cover.middle;
      }
      const courseSet = this.course.courseSet;
      const imageSrc = courseSet
        ? courseSet.cover.middle
        : this.order.cover.middle;
      return imageSrc || '';
    },
    title() {
      if (this.typeList === 'classroom_list') {
        return this.course.title;
      }
      return (
        this.course.courseSetTitle ||
        (this.course.courseSet ? this.course.courseSet.title : '') ||
        this.order.title
      );
    },
    teachPlan() {
      if (this.course.title) {
        return this.course.title;
      } else {
        return false;
      }
    },
    imgClass() {
      if (this.typeList === 'vip') return 'e-vip__img';
      return 'e-course__img';
    },
  },
  watch: {
    course: {
      handler(course) {
        // 小程序后台替换图片协议
        const courseSet = course.courseSet;
        const mpSettingPath =
          this.pathName === 'miniprogramSetting' && courseSet;

        if (!mpSettingPath) {
          return;
        }
        const keys = Object.keys(courseSet.cover);
        for (let i = 0; i < keys.length; i++) {
          courseSet.cover[keys[i]] = courseSet.cover[keys[i]].replace(
            /^(\/\/)|(http:\/\/)/,
            'https://',
          );
        }
      },
      immediate: true,
    },
  },
  methods: {
    onClick(e) {
      if (!this.feedback) {
        return;
      }
      if (this.typeList === 'vip') return;
      const isOrder = this.type === 'order';
      const id = this.course.id || this.order.targetId;
      if (e.target.tagName === 'SPAN') {
        console.log(e.target.tagName);
        return;
      }

      if (isOrder) {
        if (this.typeList === 'course' || this.typeList === 'classroom') {
          this.$router.push({
            path: `/goods/${this.order.goodsId}/show`,
            query: {
              targetId: this.order.targetId,
              type: this.typeList + '_list',
            },
          });
        } else {
          location.href = this.order.targetUrl;
        }
        return;
      }

      if (this.typeList === 'course') {
        return;
      }

      if (this.typeList === 'classroom_list') {
        this.$router.push({
          path: `/classroom/${id}`,
        });
      }

      if (this.typeList === 'course_list') {
        this.$router.push({
          path: `/course/${id}`,
        });
      }
    },
  },
};
</script>
