<template>
  <router-view v-slot="{ Component }">
    <keep-alive>
      <component :is="Component" :key="$route.fullPath"/>
    </keep-alive>
  </router-view>
</template>

<script setup>
import { ref, provide } from 'vue';

const currentWriteSignature = ref('')

provide('currentWriteSignature', currentWriteSignature)
</script>
