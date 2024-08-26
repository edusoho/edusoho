<script>
import Api from '@/api'

export default {
  name: 'ContractDetail',
  data() {
    return {
      id: this.$route.params.id,
      goodsKey: this.$route.params.goodsKey,
      contractContent: ''
    }
  },
  created() {
    this.getContractDetail()
  },
  methods: {
    async getContractDetail() {
      const res = await Api.getSignContractTemplate({
        query: { id: this.id, goodsKey: this.goodsKey }
      })

      this.contractContent = res.content
    },
    routerBack() {
      this.$router.replace({ name: 'signContract', prams: { id: this.id, goodsKey: this.goodsKey } })
    }
  }
}
</script>

<template>
<div class="contract-detail">
  <div class="py-16 px-32" v-html="contractContent"></div>
</div>
</template>

<style lang="scss" scoped>
.contract-detail {
  position: fixed;
  top: 46px;
  right: 0;
  bottom: 0;
  left: 0;
  overflow-y: auto;
}
</style>
