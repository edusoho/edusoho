<script setup>
const emit = defineEmits(['ok'])
import {onMounted, ref} from 'vue';
import Api from '../../../api';

const modalVisible = defineModel();
const props = defineProps({
  id: {type: Number},
  mode: {
    type: String,
    default: 'search'
  }
});

const tagGroupTag = ref([])
const selectedTagIds = ref([]);

onMounted(async () => {
  tagGroupTag.value = await Api.questionTag.getTagGroupTag();
})

function selectAllTag() {
  selectedTagIds.value = tagGroupTag.value
    .filter(item => Array.isArray(item.tags) && item.tags.length > 0)
    .map(item => item.tags)
    .flat()
    .map(item => item.id);
}

function clearAllTag() {
  selectedTagIds.value = [];
}

function toggleTag(id) {
  const index = selectedTagIds.value.indexOf(id);
  if (index > -1) {
    selectedTagIds.value.splice(index, 1);
  } else {
    selectedTagIds.value.push(id);
  }
}

function closeModal() {
  modalVisible.value = false;
}

function onOk() {
  emit('ok', selectedTagIds.value);
  closeModal();
}

function onCancel() {
  selectedTagIds.value = [];
}
</script>

<template>
  <a-modal v-model:open="modalVisible"
           :title="mode === 'search' ? '筛选标签' : '设置标签'"
           width="800px"
           centered
           :okText="`确定${selectedTagIds.length > 0 ? ` (${selectedTagIds.length}) ` : ''}`"
           :onOk="onOk"
           :onCancel="onCancel"
  >
    <div class="py-16 flex flex-col gap-24 max-h-484 overflow-y-scroll">
      <div class="flex gap-8">
        <a-button type="primary" @click="selectAllTag">一键全选</a-button>
        <a-button @click="clearAllTag">一键清除</a-button>
      </div>
      <div class="flex flex-col gap-32" v-for="(item, index) in tagGroupTag" :key="index">
        <div class="flex gap-28">
          <div class="text-[16px] leading-[28px] font-normal text-right w-100 truncate shrink-0">{{ item.name }}</div>
          <div class="flex flex-wrap gap-16">
            <span
              v-for="(tag, index) in item.tags"
              :key="index"
              class="cursor-pointer text-nowrap w-fit text-[16px] leading-[20px] px-12 py-4 border border-solid rounded-[8px]"
              :class="{ 'border-[#D9D9D9] text-[rgba(0,0,0,0.65)]': !selectedTagIds.includes(tag.id), '--primary-color] text-white bg-[--primary-color]': selectedTagIds.includes(tag.id) }"
              @click="toggleTag(tag.id)"
            >
              {{ tag.name }}
            </span>
          </div>
        </div>
      </div>
    </div>
  </a-modal>
</template>
