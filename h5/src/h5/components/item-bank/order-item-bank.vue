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
  <div class="flex justify-between p-12 rounded-6" style="background-color: #FAFAFA; border-radius: 6px;">
    <div class="flex items-center">
      <img class="mr-8" style="width: 18px" src="static/images/itemBankExercise/order-item-bank-icon.png" alt="">
      <div class="text-14 font-normal w-112 truncate cursor-pointer" style="color: #5E6166" @click="goItemBankExercise(item.itemBankExercise.id)">{{ item.itemBankExercise.title }}</div>
    </div>
    <div>
      <div v-if="item.itemBankExercise.price === '0.00'" class="text-16 font-semibold" style="color: #FF7E56">免费</div>
      <div v-else class="flex items-center space-x-8">
        <div class="text-12 font-semibold" style="color: #FF7E56">¥<span class="text-16 ml-2">0.</span>00</div>
        <div class="text-12 font-normal line-through mt-4" style="color: #FF7E56">{{ `¥${item.itemBankExercise.price}` }}</div>
      </div>
    </div>
  </div>
</template>
