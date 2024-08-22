<template>
  <div v-html="contractDetail" class="p-16"></div>
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
