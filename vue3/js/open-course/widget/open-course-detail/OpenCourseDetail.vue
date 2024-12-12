<script setup>
import AntConfigProvider from '../../../components/AntConfigProvider.vue';
import {nextTick, reactive, ref} from 'vue';
import { Empty } from 'ant-design-vue';
import Api from '../../../../api';
const simpleImage = Empty.PRESENTED_IMAGE_SIMPLE;
import { ClockCircleOutlined } from '@ant-design/icons-vue';
import {formatDate, open} from '../../../common';
import { AlignLeftOutlined } from '@ant-design/icons-vue';
import ThreadShowWidget from 'app/js/thread/thread-show';

const props = defineProps({
  course: {required: true},
  as: {required: true}
})

const activeKey = ref('catalogue');

const lessons = reactive({
  loading: false,
  data: [],
});
const commentTemplate = ref();

async function fetchLessons() {
  lessons.loading = true;
  lessons.data = await Api.openCourse.fetchLessons(props.course.id);
  lessons.loading = false;
}
fetchLessons();

async function getCommentTemplate() {
  commentTemplate.value = await Api.openCourse.getCommentTemplate(props.course.id, { as: props.as })
  nextTick(() => {
    new ThreadShowWidget({
      element: '#open-course-comment',
    });
  });

}
getCommentTemplate();

function isReplayAvailable(lesson) {
  return (lesson.replayStatus === 'generated' || lesson.replayStatus === 'videoGenerated') && lesson.status === 'published';
}
function isLessonAvailable(lesson) {
  return (
    (lesson.progressStatus === 'live' || lesson.replayStatus === 'generated' || lesson.replayStatus === 'videoGenerated') &&
    lesson.status === 'published'
  );
}
function getLessonStatus(lesson) {
  if (lesson.status === 'unpublished') {
    return '敬请期待';
  }
  if (lesson.replayStatus === 'generated' || lesson.replayStatus === 'videoGenerated') {
    return '回放';
  }
  switch (lesson.progressStatus) {
  case 'live':
    return '直播中';
  case 'created':
    return '未开始';
  default:
    return '已结束';
  }
}
function isLessonDisabled(lesson) {
  if (lesson.status === 'unpublished') {
    return true;
  }
  return lesson.progressStatus === 'closed' && (lesson.replayEnable === '0' || lesson.replayStatus === 'ungenerated');
}

function entryLesson(lesson) {
  if (lesson.replayStatus === 'videoGenerated') {
    open(`/open/course/${props.course.id}/lesson/${lesson.id}/player?referer=${location.pathname}`);
    return;
  }
  if (lesson.progressStatus === 'live' || lesson.progressStatus === 'created') {
    open(`/open/course/${props.course.id}/lesson/${lesson.id}/live_entry`);
    return;
  }
  if (lesson.replayStatus === 'generated') {
    open(`/open/course/${props.course.id}/lesson/${lesson.id}/live_replay_entry`);
  }
}
</script>

<template>
  <AntConfigProvider>
    <div class="px-32">
      <a-tabs v-model:activeKey="activeKey" centered class="open-course-detail-tabs">
        <a-tab-pane key="intro" tab="简介">
          <div v-if="props.course.about" v-html="props.course.about" class="mt-24 mb-24"></div>
          <div v-else class="mt-48 mb-100">
            <a-empty :image="simpleImage" description="暂无简介"/>
          </div>
        </a-tab-pane>
        <a-tab-pane key="catalogue" tab="目录">
          <a-spin :spinning="lessons.loading" tip="加载中..." class="w-full">
            <div v-if="lessons.data.length" v-for="(lesson, index) in lessons.data" :key="lesson.id">
              <div class="flex justify-between my-24">
                <div class="flex flex-col">
                  <div class="flex items-center mb-12">
                    <div class="text-16 text-[#37393D] font-normal max-w-320 truncate w-fit">{{ lesson.title }}</div>
                  </div>
                  <div class="flex items-center mr-16">
                    <AlignLeftOutlined v-if="lesson.progressStatus === 'live' && lesson.status === 'published'" rotate="270" class="text-[--primary-color] mr-4 w-16"/>
                    <div v-else class="w-5 h-5 mr-4" :class="{ 'bg-[#87898F]': !isReplayAvailable(lesson), 'bg-[--primary-color]': isReplayAvailable(lesson) }" style="border-radius: 9999px;"></div>
                    <div class="text-14 mr-16 font-normal" :class="{ 'text-[#87898F]': !isLessonAvailable(lesson), 'text-[--primary-color]': isLessonAvailable(lesson) }">{{ getLessonStatus(lesson) }}</div>
                    <div v-if="lesson.type === 'liveOpen'" class="flex items-center">
                      <ClockCircleOutlined class="text-[#87898F] mr-4 w-16"/>
                      <div class="text-14 font-normal text-[#87898F]">{{ formatDate(lesson.startTime, 'YYYY/MM/DD HH:mm') }}</div>
                    </div>
                  </div>
                </div>
                <div class="flex items-center">
                  <a-button @click.stop="entryLesson(lesson)" type="primary" ghost :disabled="isLessonDisabled(lesson)">{{ lesson.status === 'unpublished' ? '敬请期待' : lesson.progressStatus === 'closed' ? '查看回放' : '查看直播' }}</a-button>
                </div>
              </div>
              <a-divider v-if="index + 1 !== lessons.data.length"/>
            </div>
            <div v-else class="mt-48 mb-100">
              <a-empty :image="simpleImage" description="暂无内容"/>
            </div>
          </a-spin>
        </a-tab-pane>
        <a-tab-pane key="comment" tab="评论" force-render>
          <div v-html="commentTemplate" class="mt-24"></div>
        </a-tab-pane>
      </a-tabs>
    </div>
  </AntConfigProvider>
</template>

<style lang="less">
.open-course-detail-tabs {
  .ant-tabs-nav {
    margin-bottom: 0;
  }
  .es-section {
    border: 0;
    margin-bottom: 0;
    padding: 0;
  }
}
</style>
