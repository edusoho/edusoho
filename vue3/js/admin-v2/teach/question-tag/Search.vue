<script setup>
import {ref, watch} from 'vue';

  const emit = defineEmits(['search']);

  const stateOptions = [
    {
      label: '启用',
      value: 'enable'
    },
    {
      label: '禁用',
      value: 'disable'
    },
  ];

  const name = ref();
  const state = ref();

  function onReset() {
    name.value = null;
    state.value = null;
  }

  watch([name, state], () => {
    emit('search', {
      name: name.value,
      state: state.value
    })
  })
</script>

<template>
  <div class="flex gap-9">
    <a-input v-model:value="name" placeholder="标签名称" style="width: 200px"/>
    <a-select v-model:value="state" :options="stateOptions" placeholder="状态" style="width: 200px"/>
    <a-button @click="onReset">重置</a-button>
  </div>
</template>

