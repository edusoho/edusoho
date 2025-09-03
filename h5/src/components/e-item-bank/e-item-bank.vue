<template>
  <div style="padding: 10px 16px;">
    <div class="flex justify-between">
      <div class="font-bold text-text-5 text-16">
        {{ itembank.title }}
      </div>
      <div class="flex items-center text-text-3 text-14" @click="jumpToAll">
        <div>{{ $t('enter.all') }}</div>
        <div class="ml-4 all-icon"></div>
      </div>
    </div>
    <div class="flex mt-8">
      <div v-if="currentItem" class="flex-1" style="width: 50%;background-color: #fff; border-radius: 6px;" @click="jumpToCurrentItem">
        <img :src="currentItem.cover.middle" style="width: 100%; border-radius: 6px 6px 0 0; ">
        <div class="flex flex-col justify-between p-8" style="height: 62px;">
          <div class="font-bold text-14 text-overflow" style="width: 124px;">{{ currentItem.title }}</div>
          <div class="flex items-end justify-between">
            <div class="font-bold text-14" style="color: #FF7A34;"><span v-if="currentItem.hidePrice !== '1'">￥{{ currentItem.price }}</span></div>
            <div class="text-text-3 text-12">{{ $t('e.personStudying', { number: currentItem.studentNum }) }}</div>
          </div>
        </div>
      </div>
      <div class="flex flex-col justify-between flex-1 ml-8" style="width: 50%;height: 162px;">
        <div
          v-for="(item, index) in itembank.items.slice(0, 3)"
          :key="item.id"
          class="p-12 text-overflow text-14"
          style="height: 46px;border-radius: 6px;"
          :style="{ backgroundColor: currentIndex === index ? '#3dcd7f' : '#e7f7ee', color: currentIndex === index ? '#fff' : '#1d2129' }"
          @click="switchCurrentItem(index)"
        >{{ item.title }}</div>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  name: 'ItemBank',
  props: ['itembank', 'showMode'],
  data() {
    return {
      currentIndex: 0
    }
  },
  computed: {
    currentItem() {
      return this.itembank.items[this.currentIndex]
    }
  },
  methods: {
    switchCurrentItem(index) {
      if (this.showMode === 'admin') return

      this.currentIndex = index
    },
    jumpToAll() {
      if (this.showMode === 'admin') return

      this.$router.push({
        name: 'more_itembank',
        query: {
          categoryId: '0'
        },
      });
    },
    jumpToCurrentItem() {
      if (this.showMode === 'admin') return

      this.$router.push({
        path: `/item_bank_exercise/${this.currentItem.id}`,
        query: {
          targetId: this.currentItem.id,
          type: 'item_bank_exercise',
        },
      });
    }
  }
}
</script>

<style lang="scss" scoped>
  .all-icon {
    width: 6px;
    height: 6px;
    border-top: 1px solid #86909c;
    border-right: 1px solid #86909c;
    transform: rotate(45deg);
  }
</style>
