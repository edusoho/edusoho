<script>
export default {
  props: {
    item: {type: Object},
  },
  methods: {
    goItemBankExercise(id) {
      this.$router.push({ name: 'item_bank_exercise', params: { id: id } });
    },
  }
}
</script>

<template>
  <div class="flex items-center w-full p-12 rounded-6" style="background-color: #FAFAFA; border-radius: 6px;" @click="goItemBankExercise(item.itemBankExercise.id)">
    <img class="mr-8 flex-none" style="width: 18px" src="static/images/itemBankExercise/order-item-bank-icon.png" alt="">
    <div class="text-14 w-full font-normal truncate" style="color: #5E6166">{{ item.itemBankExercise.title }}</div>
    <div class="flex-none whitespace-nowrap ml-24">
      <div v-if="item.itemBankExercise.price === '0.00'" class="text-16 font-semibold" style="color: #FF7E56">免费</div>
      <div v-else class="flex items-center space-x-8">
        <div class="text-12 font-normal line-through mt-4" style="color: #FF7E56">{{ `¥${item.itemBankExercise.price}` }}</div>
        <div class="text-16 font-semibold" style="color: #FF7E56">免费</div>
      </div>
    </div>
  </div>
</template>
