<template>
  <div class="e-course">
    <div class="clearfix" @click="onClick">
      <div class="e-course__left pull-left relative">
        <img v-lazy="imgSrc" :class="imgClass" />
        <div v-if="course.videoMaxLevel === '2k'" class="absolute left-0 bottom-0 px-8 text-white text-12 font-medium bg-black bg-opacity-80" style="padding-top: 2px; padding-bottom: 2px; line-height: 20px; border-top-right-radius: 12px;">2K 优享</div>
        <div v-if="course.videoMaxLevel === '4k'" class="absolute left-0 bottom-0 px-8 text-[#492F0B] text-12 font-medium bg-gradient-to-l from-[#F7D27B] to-[#FCEABE]" style="padding-top: 2px; padding-bottom: 2px; line-height: 20px; border-top-right-radius: 12px;">4K 臻享</div>
      </div>
      <div class="e-course__right pull-left">
        <div
          v-if="type === 'confirmOrder'"
          class="e-course__title course-confirm-title"
        >
          {{ title }}
        </div>
        <div v-else>
          <div class="e-course__title line-clamp-2">{{ title }}</div>
          <div v-if="typeList === 'classroom_list'" class="e-course__count">
            {{ $t('e.totalOfTwoCourses', { number: course.courseNum }) }}
          </div>
          <div
            v-if="typeList === 'course_list'"
            class="e-course__project"
          >
            <span class="text-overflow" style="max-width: 192px;" v-if="teachPlan">{{ teachPlan }}</span>
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
    <div v-if="course.lastLearnTask" class="e-course-last_learn_task-container">
      <img class="icon" src="static/images/course/last-learn-task-icon.svg" alt="">
      <div class="text">{{ course.lastLearnTask.title }}</div>
    </div>
  </div>
</template>

<script>
import switchBox from './e-course-switch-box.vue';
import { closedToast } from '@/utils/on-status.js';

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
    classroom: {
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
      const isOrder = this.type === 'order';
      if(!isOrder && !parseInt(this.course?.canLearn)) {
        return closedToast('course');
      }

      if (!this.feedback) {
        return;
      }
      if (this.typeList === 'vip') return;
      const id = this.course.id || this.order.targetId;
      if (e.target.tagName === 'SPAN') {

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
          query: {
            lastLearnTaskId: this.course.lastLearnTask.id,
            lastLearnTaskType: this.course.lastLearnTask.type,
          },
        });
      }
    },
  },
};
</script>
