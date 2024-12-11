<script setup>

import {reactive} from 'vue';
import Api from '../../../../../api';
import AntConfigProvider from '../../../../components/AntConfigProvider.vue';
import {AlignLeftOutlined, ClockCircleOutlined} from '@ant-design/icons-vue';
import {formatDate, goto} from '../../../../common';

const props = defineProps({
  course: {required: true},
})

const lessons = reactive({
  loading: false,
  data: [],
});

async function fetchLessons() {
  lessons.loading = true;
  lessons.data = await Api.openCourse.fetchLessons(props.course.id);
  lessons.loading = false;
}
fetchLessons();

function viewLesson(courseId, id, status) {
  if (status === 'unpublished') return;
  goto(`/open/course/${courseId}/lesson/${id}/learn?as=preview&previewType=wx`);
}
</script>

<template>
  <AntConfigProvider>
    <div class="flex flex-col">
      <div class="px-24 py-8 text-16 text-[#37393D] font-medium">目录</div>
      <a-divider style="margin: 0"/>
      <div class="p-8">
        <div v-for="(lesson, index) in lessons.data" :key="lesson.id">
          <div class="flex flex-col px-16 py-8 rounded-6 active:bg-[rgba(61,205,127,0.05)]" @click="viewLesson(props.course.id, lesson.id, lesson.status)">
            <div class="w-full truncate text-14 text-[#37393D] font-normal mb-8">{{ lesson.title }}</div>
            <div class="flex items-center">
              <AlignLeftOutlined v-if="lesson.progressStatus === 'live' && lesson.status === 'published'" rotate="270" class="text-[--primary-color] mr-4 w-16"/>
              <div v-else class="w-5 h-5 mr-4" :class="{ 'bg-[#87898F]': lesson.status === 'unpublished' || lesson.replayStatus !== 'generated', 'bg-[--primary-color]': lesson.replayStatus === 'generated' && lesson.status === 'published' }" style="border-radius: 9999px;"></div>
              <div class="text-14 mr-12 font-normal" :class="{ 'text-[#87898F]': lesson.progressStatus !== 'live' && lesson.status === 'unpublished', 'text-[--primary-color]': (lesson.progressStatus === 'live' || lesson.replayStatus === 'generated') && lesson.status === 'published' }">{{ lesson.status === 'unpublished' ? '敬请期待' : lesson.replayStatus === 'generated'? '回放' : lesson.progressStatus === 'live' ? '直播中' : lesson.progressStatus === 'created' ? '未开始' : '已结束' }}</div>
              <ClockCircleOutlined class="text-[#87898F] mr-4 w-16"/>
              <div class="text-14 font-normal text-[#87898F]">{{ formatDate(lesson.startTime, 'YYYY/MM/DD HH:mm') }}</div>
            </div>
          </div>
          <a-divider v-if="index + 1 !== lessons.data.length" style="margin: 8px 0"/>
        </div>
      </div>
    </div>
  </AntConfigProvider>
</template>

<style lang="less">

</style>
