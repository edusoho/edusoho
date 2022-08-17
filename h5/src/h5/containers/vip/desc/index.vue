<template>
  <div v-if="vipDetail" class="p-16" v-html="vipDetail.level.description">
  </div>
</template> 

<script>
import Api from '@/api'

export default {
  name: 'vipDesc',

  data() {
    return {
      vipDetail: null
    }
  },

  async created() {
    this.vipDetail = await Api.getVipDetail({
      query: {
        levelId: this.$route.params.id
      }
    })
  }
}
</script>
