<script>
import Api from '@/api';
import GoodsItemBank from '@/components/item-bank/goods-item-bank.vue';

export default {
  data() {
    return {
      bindItemBankList: [],
    }
  },
  components: {
    GoodsItemBank,
  },
  methods: {
    async getBindItemBank() {
      this.bindItemBankList = await Api.getBindItemBank({
        params: {
          bindType: this.$route.query.bindType,
          bindId: this.$route.query.bindId,
        }
      })
    }
  },
  created() {
    this.getBindItemBank();
  },
}
</script>

<template>
  <div class="flex flex-col gap-12 p-16">
    <div v-for="item in bindItemBankList" :key="item.id">
      <goods-item-bank :item="item"/>
    </div>
  </div>
</template>
