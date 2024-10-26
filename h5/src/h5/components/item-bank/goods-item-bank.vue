<script>
export default {
  props: {
    item: {
      type: Object,
      required: true
    }
  },
  methods: {
    integerPart(num) {
      return num.split('.')[0];
    },
    decimalPart(num) {
      return num.split('.')[1];
    },
    goItemBankExercise(id) {
      this.$router.push({ name: 'item_bank_exercise', params: { id: id } });
    },
  }
}
</script>

<template>
<div class="flex border border-[#F2F3F5] border-solid" style="border-radius: 8px;" @click="goItemBankExercise(item.itemBankExerciseId)">
  <!--  <img class="h-96" :src="item.cover.middle" alt="" style="border-radius: 8px;">-->
  <img class="h-96" src="./item-bank-icon.jpg" alt="" style="border-radius: 8px;">
  <div class="flex flex-col w-full px-12 justify-center">
    <div>
      <div class="text-16 font-medium truncate mb-8" style="color: #37393D; max-width: 140px">{{ item.itemBankExercise.title }}</div>
      <div class="flex justify-between">
        <div class="flex flex-col">
          <div class="text-12 font-normal mb-4" style="color: #919399;">章节练习：<span style="color: #37393D;">{{ item.chapterExerciseNum }}</span></div>
          <div class="text-12 font-normal" style="color: #919399;">试卷练习：<span style="color: #37393D;">{{ item.assessmentNum }}</span></div>
        </div>
        <div class="flex items-end text-14 font-semibold" style="color: #FF7E56; line-height: 16px"><span class="text-12 font-normal mr-2">¥</span>{{ `${integerPart(item.itemBankExercise.price)}.` }}<span class="text-12" style="line-height: 14px">{{ decimalPart(item.itemBankExercise.price) }}</span></div>
      </div>
    </div>
  </div>
</div>
</template>
