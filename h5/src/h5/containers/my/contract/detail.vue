<template>
  <div v-html="contractDetail" class="my-contract-detail"></div>
</template>

<script>
import Api from '@/api'

export default {
  name: 'SignedContractDetail',
  data() {
    return {
      contractDetail: ''
    }
  },
  async created() {
    const { content } = await Api.getSignedContractDetail({
      query: { id: this.$route.params.id }
    })

    this.contractDetail = content
  }
}
</script>

<style scoped>
.my-contract-detail {
  position: fixed;
  top: 46px;
  right: 0;
  bottom: 0;
  left: 0;
  padding: 16px;
  overflow-y: auto;
}
</style>
