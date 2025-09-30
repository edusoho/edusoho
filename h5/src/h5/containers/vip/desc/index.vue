<template>
  <div v-if="vipDetail" class="h-full p-16" >
    <div v-if="vipDetail.level.description" v-html="vipDetail.level.description"></div>
    <div v-else class="flex flex-col items-center justify-center h-full" style="margin-bottom: 40px;">
      <img style="width: 120px;" src="static/images/list-empty.png" srcset="static/images/list-empty@2x.png" alt="">
      <div class="mt-12 text-text-3 text-14">专属介绍制作中...</div>
    </div>
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
