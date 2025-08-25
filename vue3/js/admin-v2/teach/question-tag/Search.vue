<script setup>
import {ref, watch} from 'vue';

const needReset = defineModel('needReset', { type: Boolean })
const emit = defineEmits(['search']);
const props = defineProps({
  isGroup: Boolean,
})

watch(needReset, () => {
  if (needReset) {
    onReset()
    needReset.value = false;
  }
})

const statusOptions = [
  {
    label: '启用',
    value: '1'
  },
  {
    label: '禁用',
    value: '0'
  },
];

const name = ref();
const status = ref();

function onReset() {
  name.value = null;
  status.value = null;
}

watch([name, status], () => {
  emit('search', {
    name: name.value,
    status: status.value
  })
})
</script>

<template>
  <div class="flex gap-9">
    <a-input
      v-model:value="name"
      :placeholder="isGroup ? '标签类型名称' : '标签名称'"
      style="width: 200px"
    />
    <a-select
      v-model:value="status"
      :options="statusOptions"
      placeholder="全部状态"
      style="width: 200px"
    />
    <a-button @click="onReset">重置</a-button>
  </div>
</template>

