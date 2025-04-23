<script setup>
import { DownOutlined, UpOutlined } from '@ant-design/icons-vue';
import {ref} from 'vue';
import ChapterListButton from './ChapterListButton.vue';

const props = defineProps({
  chapter: {type: Object, default: {}},
  records: {type: Object, default: {}},
  isLast: {type: Boolean, default: false},
  previewAs: {type: String, default: null},
  member: {type: Object, default: {}},
  exercise: {type: Object, default: {}},
  moduleId: {type: Number, default: null},
  selectedChapterId: {type: String, default: null},
});

const isUnfold = ref(true);

const emit = defineEmits(['selectChapter']);
const selectedChapterId = ref(null);
function selectChapter(chapterId) {
  selectedChapterId.value = chapterId;
  emit('selectChapter', chapterId);
}
</script>

<template>
  <div class="flex flex-col">
    <div class="flex items-center justify-between rounded-6 p-12 cursor-pointer" :class="{'bg-[#FAFAFA]': props.chapter.depth == 1}" @click="isUnfold = !isUnfold">
      <div class="flex items-center">
        <UpOutlined v-if="isUnfold" class="mr-12 text-12 text-[#5E6166]" :class="{'opacity-0': props.chapter.children.length === 0}"/>
        <DownOutlined v-if="!isUnfold" class="mr-12 text-12 text-[#5E6166]" :class="{'opacity-0': props.chapter.children.length === 0}"/>
        <div class="max-w-165 sm:max-w-320 truncate text-14 leading-22 text-[#37393D]" :class="{'font-medium': props.chapter.depth == 1, 'pl-16': props.chapter.depth == 3, 'text-[#5E6166]': props.chapter.depth == 3, 'text-[--primary-color]': props.chapter.id === props.selectedChapterId}">{{ props.chapter.name }}</div>
      </div>
      <chapter-list-button
        :chapter="props.chapter"
        :preview-as="props.previewAs"
        :member="props.member"
        :record="props.records[props.chapter.id]"
        :exercise="props.exercise"
        :module-id="props.moduleId"
        @select-chapter="selectChapter"
      >
      </chapter-list-button>
    </div>
    <div v-if="props.chapter.depth == 3 && !isLast" class="border border-solid border-t-0 border-[#F2F3F5] my-8 ml-44 mr-12"></div>
    <div v-else class="mb-8"></div>
    <div v-show="isUnfold">
      <div v-for="(item, index) in props.chapter.children" :key="item.id" :ref="item.id">
        <chapter-list-section
          :chapter="item"
          :records="props.records"
          :is-last="index + 1 === props.chapter.children.length"
          :preview-as="props.previewAs"
          :member="props.member"
          :exercise="props.exercise"
          :module-id="props.moduleId"
          :selected-chapter-id="props.selectedChapterId"
          @select-chapter="selectChapter"
        >
        </chapter-list-section>
      </div>
    </div>
  </div>
</template>
