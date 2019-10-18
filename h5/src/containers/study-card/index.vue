<template>
  <div class="e-study-card">
    <router-view></router-view>
  </div>
</template>

<script>
  import Api from '@/api';

  export default {
    name: 'index',
    created() {
      Api.setCoin()
        .then(res => {
          localStorage.setItem('coin', res.name);
        })
        .catch(err => console.log(err))
    }
  };
</script>

<style scoped>

</style>
