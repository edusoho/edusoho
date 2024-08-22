<template>
  <router-view v-slot="{ Component }">
    <keep-alive>
      <component :is="Component" :key="$route.fullPath"/>
    </keep-alive>
  </router-view>
</template>

<script setup>
</script>
