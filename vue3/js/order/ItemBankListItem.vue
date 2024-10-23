<script setup>
const props = defineProps({
  item: {required: true},
})

function toItemBankExercisePage(exerciseId) {
  window.location.href = `/item_bank_exercise/${exerciseId}`
}
</script>

<template>
  <div class="flex justify-between p-12 bg-[#FAFAFA] rounded-6">
    <div class="flex items-center">
      <img class="w-18 mr-8" src="../../img/item-bank/item-bank-gift-icon.png" alt="">
      <div class="text-14 text-[#5E6166] font-normal max-w-400 truncate cursor-pointer hover:text-[#46C37B]" @click="toItemBankExercisePage(item.itemBankExercise.id)">{{ item.itemBankExercise.title }}</div>
    </div>
    <div>
      <div v-if="item.itemBankExercise.price === '0.00'" class="text-16 text-[#FF7E56] font-semibold">免费</div>
      <div v-else class="flex items-center space-x-8">
        <div class="text-12 text-[#FF7E56] font-semibold">¥<span class="text-16 ml-2">0.</span>00</div>
        <div class="text-12 text-[#FFA58A] font-normal line-through mt-4">{{ `¥${item.itemBankExercise.price}` }}</div>
      </div>
    </div>
  </div>
</template>
