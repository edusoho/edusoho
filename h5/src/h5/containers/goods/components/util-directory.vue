<template>
  <div v-if="util.isExist==1" class="util-directory text-overflow">
    第{{ util.number }}节：{{ util.title }}
  </div>
</template>
<script>
export default {
  name: 'UtilDirectory',
  props: {
    util: {
      type: Object,
      default: () => {}
    }
  }
}
</script>
