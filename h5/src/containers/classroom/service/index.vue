<template>
  <div class="course-detail__service">
    <span>承诺服务：</span>
    <template v-for="service in services">
      <span class="course-detail__service-icon">{{ service.shortName }}</span>
    </template>
  </div>
</template>

<script>
export default {
  name: 'service',
  props: ['services'],
}
</script>
