<template>
  <div class="e-study-card">
    <router-view/>
  </div>
</template>

<script>
import Api from '@/api'

export default {
  name: 'Index',
  created() {
    document.title = '学习卡充值'
  }
}
</script>

<style scoped>

</style>
