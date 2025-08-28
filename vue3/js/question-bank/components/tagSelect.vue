<script setup>
const emit = defineEmits(['ok'])
import {onMounted, ref, watch} from 'vue';
import Api from '../../../api';

const modalVisible = defineModel();
const props = defineProps({
  params: {
    type: Object,
  }
});

const tagGroupTag = ref([])
const relationTagIds = ref([]);
const selectedTagIds = ref([]);

onMounted(async () => {
  tagGroupTag.value = await Api.questionTag.getTagGroupTag();
})

watch(modalVisible, async () => {
  if (modalVisible.value && props.params.mode === 'set' && props.params.id) {
    const relationTags = await Api.questionTag.getTagRelationTags(props.params.id)
    relationTags.forEach(item => {
      item.tags.forEach(tag => {
        relationTagIds.value.push(tag.id);
      })
    })
    selectedTagIds.value = [...relationTagIds.value];
  }
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

function compareWithSort(arr1, arr2) {
  if (arr1.length !== arr2.length) return false;
  const sorted1 = [...arr1].sort();
  const sorted2 = [...arr2].sort();
  return sorted1.every((value, index) => value === sorted2[index]);
}

function closeModal() {
  selectedTagIds.value = [];
  relationTagIds.value = [];
  modalVisible.value = false;
}

async function onOk() {
  if (props.params.mode === 'filter') {
    emit('ok', selectedTagIds.value);
  } else if (props.params.mode === 'set') {
    if (compareWithSort(selectedTagIds.value, relationTagIds.value)) {
      closeModal();
      return;
    }
    const params = {
      itemIds: props.params.ids ? props.params.ids : [props.params.id],
      tagIds: selectedTagIds.value
    }
    await Api.questionTag.setTagRelation(params)
    window.emitter.emit('set-tag-success')
  }
  closeModal();
}

function onCancel() {
  closeModal();
}
</script>

<template>
  <a-modal v-model:open="modalVisible"
           :title="params.mode === 'filter' ? '筛选标签' : '设置标签'"
           width="800px"
           centered
           :okText="`确定${selectedTagIds.length > 0 ? ` (${selectedTagIds.length}) ` : ''}`"
           :onOk="onOk"
           :onCancel="onCancel"
  >
    <div class="flex flex-col">
      <div class="flex gap-8 py-16">
        <a-button type="primary" @click="selectAllTag">一键全选</a-button>
        <a-button @click="clearAllTag">一键清除</a-button>
      </div>
      <div class="flex flex-col gap-32 max-h-420 overflow-y-scroll">
        <div class="flex flex-col gap-32" v-for="(item, index) in tagGroupTag" :key="index">
          <div class="flex flex-col gap-8">
            <div class="text-[16px] leading-[28px] font-normal">{{ item.name }}</div>
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
    </div>
  </a-modal>
</template>
