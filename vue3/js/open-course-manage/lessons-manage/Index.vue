<script setup>
import AntConfigProvider from '../../components/AntConfigProvider.vue';
import {ref, h, reactive, computed, createVNode} from 'vue';
import { PlusCircleOutlined, EditOutlined, EyeOutlined, SendOutlined, CloseCircleOutlined, DeleteOutlined, ExclamationCircleOutlined, HolderOutlined, ShareAltOutlined } from '@ant-design/icons-vue';
import {Empty, message, Modal} from 'ant-design-vue';
const simpleImage = Empty.PRESENTED_IMAGE_SIMPLE;
import draggable from 'vuedraggable';
import Api from '../../../api';
import {open} from '../../common';
import AddLessonDrawer from './AddLessonDrawer.vue';
import ShareModal from './ShareModal.vue';

const props = defineProps({
  course: {required: true},
})

const lessons = reactive({
  loading: false,
  data: [],
  ids: [],
});

async function fetchLessons() {
  lessons.loading = true;
  lessons.data = await Api.openCourse.fetchLessons(props.course.id);
  lessons.ids = lessons.data.map(item => item.id);
  lessons.loading = false;
}
fetchLessons();

const drawerType = ref();
const editId = ref();

const isDrawerOpen = computed(() => {
  return drawerType.value === 'liveOpen' || drawerType.value === 'replay';
});

async function changeLessonSort() {
  const newIds = lessons.data.map(item => item.id);
  if (lessons.ids.every((value, index) => value === newIds[index])) {
    return;
  }
  await Api.openCourse.changeLessonSort(props.course.id, { ids: newIds });
  await fetchLessons();
}

function editLesson(lessonType, id = null) {
  if (lessons.data.length >= 300 && !id) {
    message.error('最多可添加300个课时');
    return;
  }
  drawerType.value = lessonType;
  editId.value = id;
}

function deleteLesson(id) {
  Modal.confirm({
    title: '确定要删除该课时吗？',
    icon: createVNode(ExclamationCircleOutlined),
    centered: true,
    okText: '删除',
    cancelText: '取消',
    async onOk() {
      await Api.openCourse.deleteLesson(props.course.id, id);
      message.success('删除成功');
      await fetchLessons();
    }
  });
}

async function publishLesson(id) {
  await Api.openCourse.publishLesson(props.course.id, id);
  message.success('发布成功');
  await fetchLessons();
}

async function unpublishLesson(id) {
  await Api.openCourse.unpublishLesson(props.course.id, id);
  message.success('取消发布成功');
  await fetchLessons();
}

function viewLesson(id) {
  open(`/open/course/${props.course.id}/lesson/${id}/learn?as=preview`)
}

const shareModalVisible = ref(false);
const shareUrl = ref();
function shareLesson(lesson) {
  shareModalVisible.value = true;
  const urlOrigin = location.origin;
  if (lesson.replayStatus === 'videoGenerated') {
    shareUrl.value = `${urlOrigin}/open/course/${props.course.id}/lesson/${lesson.id}/player?referer=${location.pathname}`;
    return;
  }
  if (lesson.progressStatus !== 'closed') {
    shareUrl.value = `${urlOrigin}/open/course/${props.course.id}/lesson/${lesson.id}/live_entry`;
    return;
  }
  if (lesson.progressStatus === 'closed' && lesson.replayEnable === '1' && lesson.replayStatus === 'generated') {
    shareUrl.value = `${urlOrigin}/open/course/${props.course.id}/lesson/${lesson.id}/live_replay_entry`;
    return;
  }
  shareUrl.value = `${urlOrigin}/open/course/${props.course.id}/lesson/${lesson.id}/learn`;
}

function onDrawerSave() {
  fetchLessons()
}
function onDrawerCancel() {
  drawerType.value = null
  editId.value = null
}

function onModalCancel() {
  shareUrl.value = null;
}

function onModalOk() {
  shareUrl.value = null;
}
</script>
<template>
  <AntConfigProvider>
    <div class="flex flex-col py-24 px-32">
      <div class="flex justify-between items-center">
        <div class="text-16 font-medium text-black text-opacity-88">课时管理</div>
        <div class="flex space-x-20">
          <a-button type="primary" ghost :icon="h(PlusCircleOutlined)" @click="editLesson('replay')">添加回放</a-button>
          <a-button type="primary" :icon="h(PlusCircleOutlined)" @click="editLesson('liveOpen')">添加直播</a-button>
        </div>
      </div>
      <a-spin :spinning="lessons.loading" tip="加载中...">
        <div v-if="lessons.data.length === 0">
          <a-empty :image="simpleImage" class="mt-140"/>
        </div>
        <div v-else-if="lessons.data.length > 0" class="mt-20">
          <draggable
            v-model="lessons.data"
            item-key="id"
            @end="changeLessonSort"
          >
            <template #item="{element, index}">
              <div class="flex items-center justify-between mb-12 bg-[#FAFAFA] rounded-8 px-24 py-20 cursor-all-scroll">
                <div class="flex items-center space-x-12 relative">
                  <HolderOutlined class="w-16 text-[#919399]"/>
                  <div v-if="element.status === 'unpublished'" class="absolute h-fit -top-20 -left-36 px-8 py-3 leading-10 text-10 text-white font-normal bg-[#87898F] rounded-tl-8 rounded-br-8">未发布</div>
                  <div v-if="element.type === 'liveOpen'" class="px-8 h-22 leading-20 text-12 text-[#46C37B] font-medium rounded-4 border border-solid border-[#46C37B] bg-[rgba(70,195,123,0.05)]">直播</div>
                  <div v-if="element.type === 'replay'" class="px-8 h-22 leading-20 text-12 text-[#FF7D00] font-medium rounded-4 border border-solid border-[#FF7D00] bg-[rgba(255,125,0,0.05)]">回放</div>
                  <div class="text-14 font-normal text-black">{{`课时 ${index + 1} ：${element.title}（${Math.floor(Number(element.length / 60))}:${(Number(element.length) % 60) < 10 ? `0${Number(element.length) % 60}` : Number(element.length) % 60 }）` }}</div>
                </div>
                <div class="flex items-center space-x-20 text-[--primary-color] text-14 font-normal">
                  <div v-if="element.editable" @click="editLesson(element.type, element.id)" class="flex items-center cursor-pointer"><EditOutlined class="mr-4"/>编辑</div>
                  <div @click="viewLesson(element.id)" class="flex items-center cursor-pointer"><EyeOutlined class="mr-4"/>预览</div>
                  <div v-if="element.status === 'unpublished'" @click="publishLesson(element.id)" class="flex items-center cursor-pointer"><SendOutlined class="mr-4"/>发布</div>
                  <div v-if="element.status === 'published'" @click="unpublishLesson(element.id)" class="flex items-center cursor-pointer"><CloseCircleOutlined class="mr-4"/>取消发布</div>
                  <div v-if="element.status === 'unpublished'" @click="deleteLesson(element.id)" class="flex items-center cursor-pointer"><DeleteOutlined class="mr-4"/>删除</div>
                  <div v-if="element.status === 'published'" @click="shareLesson(element)" class="flex items-center cursor-pointer"><ShareAltOutlined class="mr-4"/>分享</div>
                </div>
              </div>
            </template>
          </draggable>
        </div>
      </a-spin>
    </div>
    <AddLessonDrawer
      v-model="isDrawerOpen"
      :drawer-type="drawerType"
      :edit-id="editId"
      :course-id="course.id"
      @save="onDrawerSave"
      @cancel="onDrawerCancel"
    />
    <ShareModal
      v-model="shareModalVisible"
      :share-url="shareUrl"
      @ok="onModalOk"
      @cancel="onModalCancel"
    />
  </AntConfigProvider>
</template>

<style lang="less">
.open-course-lesson-replay-id-field {
  .ant-form-item-explain-success {
    color: #f53f3f
  }
}
</style>
