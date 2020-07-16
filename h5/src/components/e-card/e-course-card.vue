<template>
  <div class="e-course-card">
    <div class="live-list__item">
      <!-- course -->
      <div
        class="live-item__top"
        @click="toCourse()"
        v-html="course.top.html"
      ></div>
      <!-- task -->
      <div class="live-item__content">
        <div class="live-content__left">
          <div
            class="live-content__subtitle"
            v-if="course.content.left.subTitle.isShow"
            v-html="course.content.left.subTitle.subhtml"
          />
          <div class="live-content__title">
            <i :class="['iconfont', iconfont(course.type)]"></i>
            <span v-html="course.content.left.subTitle.html" />
            <div
              class="live-content__dec"
              v-if="course.content.left.dec.isShow"
            >
              <span v-html="course.content.left.dec.html" />
            </div>
          </div>
        </div>
        <div class="live-content__right">
          <div
            class="live-btn live-btn--start"
            v-if="course.content.right.isShow"
            @click="toTask()"
          >
            继续学习
          </div>
          <div class="live-btn live-btn--none" v-else>暂不支持</div>
        </div>
      </div>
      <!-- classroom -->
      <div
        class="live-item__bottom van-hairline--top"
        v-if="course.bottom.isShow"
        @click="toClassroom()"
      >
        <span v-html="course.bottom.html" />
      </div>
    </div>
  </div>
</template>

<script>
const itemBank = [
  'item_bank_chapter_exercise',
  'item_bank_assessment_exercise',
];
export default {
  name: 'e-course-card',
  props: {
    course: {
      type: Object,
      default: () => {},
    },
  },
  methods: {
    // 任务图标(缺少下载)
    iconfont(type) {
      switch (type) {
        case 'audio':
          return 'icon-yinpin';
        case 'doc':
          return 'icon-wendang';
        case 'exercise':
          return 'icon-lianxi';
        case 'flash':
          return 'icon-flash';
        case 'homework':
          return 'icon-zuoye';
        case 'live':
          return 'icon-zhibo';
        case 'ppt':
          return 'icon-ppt';
        case 'discuss':
          return 'icon-taolun';
        case 'testpaper':
          return 'icon-kaoshi';
        case 'text':
          return 'icon-tuwen';
        case 'video':
          return 'icon-shipin';
        case 'download':
          return 'icon-xiazai';
        default:
          return '';
      }
    },
    toClassroom() {
      this.$emit('toClassroom', this.course.link.classroomId);
    },
    toTask() {
      let task = {
        id: this.course.link.taskId,
        type: this.course.type,
        courseId: this.course.link.courseId,
      };
      if (itemBank.includes(this.course.type)) {
        task = this.course.link;
      }

      this.$emit('toTask', task);
    },
    toCourse() {
      if (itemBank.includes(this.course.type)) {
        this.toItemBank();
        return;
      }
      this.$emit('toCourse', this.course.link.courseId);
    },
    toItemBank() {
      this.$emit('toItemBank', this.course.link.answerRecord);
    },
  },
};
</script>
