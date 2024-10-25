<script>
import Api from '@/api';
import ItemBankItem from '@/components/item-bank/item-bank-item.vue';

export default {
  data() {
    return {
      bindItemBankList: [],
    }
  },
  components: {
    ItemBankItem,
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
      <item-bank-item :item="item"/>
    </div>
  </div>
</template>
