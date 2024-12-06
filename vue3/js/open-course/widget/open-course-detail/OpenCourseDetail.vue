<script setup>
import AntConfigProvider from '../../../components/AntConfigProvider.vue';
import {reactive, ref} from 'vue';
import { Empty } from 'ant-design-vue';
import Api from '../../../../api';
const simpleImage = Empty.PRESENTED_IMAGE_SIMPLE;
import { ClockCircleOutlined } from '@ant-design/icons-vue';
import {formatDate, goto} from '../../../common';

const props = defineProps({
  course: {required: true},
  as: {required: true}
})

const activeKey = ref('intro');

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
}
getCommentTemplate();

function viewLesson(courseId, id) {
  goto(`/open/course/${courseId}/lesson/${id}/learn?as=preview`)
}

</script>

<template>
  <AntConfigProvider>
    <div class="px-32">
      <a-tabs v-model:activeKey="activeKey" centered class="open-course-detail-tabs">
        <a-tab-pane key="intro" tab="简介">
          <div v-if="props.course.about" v-html="props.course.about" class="mt-24 mb-24"></div>
          <div v-else class="mt-32 mb-190">
            <a-empty :image="simpleImage" description="暂无简介"/>
          </div>
        </a-tab-pane>
        <a-tab-pane key="catalogue" tab="目录">
          <a-spin :spinning="lessons.loading" tip="加载中..." class="mt-140">
            <div v-for="(lesson, index) in lessons.data" :key="lesson.id">
              <div class="flex justify-between my-24">
                <div class="flex flex-col">
                  <div class="mb-12 text-16 text-[#37393D] font-normal">{{ lesson.title }}</div>
                  <div class="flex items-center mr-16">
                    <div class="w-7 h-7 mr-4" :class="{ 'bg-[#87898F]': lesson.progressStatus !== 'live', 'bg-[--primary-color]': lesson.progressStatus === 'live' }" style="border-radius: 9999px;"></div>
                    <div class="text-14 mr-16 font-normal" :class="{ 'text-[#87898F]': lesson.progressStatus !== 'live', 'text-[--primary-color]': lesson.progressStatus === 'live' || lesson.replayStatus === 'generated' }">{{ lesson.replayStatus === 'generated'? '回放' : lesson.progressStatus === 'live' ? '进行中' : lesson.progressStatus === 'created' ? '未开始' : '已结束' }}</div>
                    <ClockCircleOutlined class="text-[#87898F] mr-4 w-16"/>
                    <div class="text-14 font-normal text-[#87898F]">{{ formatDate(lesson.startTime, 'YYYY/MM/DD HH:mm') }}</div>
                  </div>
                </div>
                <div class="flex items-center">
                  <a-button @click="viewLesson(props.course.id, lesson.id)" type="primary" ghost :disabled="lesson.progressStatus === 'created' || lesson.generated !== 'generated'">{{ lesson.progressStatus === 'closed' ? '查看回放' : '查看直播' }}</a-button>
                </div>
              </div>
              <a-divider v-if="index + 1 !== lessons.data.length"/>
            </div>
          </a-spin>
        </a-tab-pane>
        <a-tab-pane key="comment" tab="评论">
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
