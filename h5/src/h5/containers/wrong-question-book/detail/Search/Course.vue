<template>
  <van-popup
    v-model="visible"
    round
    position="bottom"
    class="wrong-question-search"
  >
    <van-nav-bar
      title="筛选"
      @click-left="onClickReset"
      @click-right="onClickSearch"
    >
      <template #left>
        <span class="search-reset">重置</span>
      </template>
      <template #right>
        <span class="search-btn">查看错题</span>
      </template>
    </van-nav-bar>

    <van-divider style="margin-top: 4px;" />

    <div class="search-sort">
      <div class="search-sort__title">排序</div>
      <div class="search-sort__btns">
        <div
          :class="['sort-btn', { active: sortType === 'default' }]"
          @click="onClickSort('default')"
        >
          综合排序
        </div>
        <div
          :class="['sort-btn', { active: sortType === 'DESC' }]"
          @click="onClickSort('DESC')"
        >
          由高至低
        </div>
        <div
          :class="['sort-btn', { active: sortType === 'ASC' }]"
          @click="onClickSort('ASC')"
        >
          由低至高
        </div>
      </div>
    </div>
  </van-popup>
</template>

<script>
export default {
  name: 'CourseSearch',

  props: {
    show: {
      type: Boolean,
      required: true,
    },
  },

  data() {
    return {
      visible: this.show,
      sortType: 'default',
    };
  },

  watch: {
    show(value) {
      if (value) this.visible = value;
    },

    visible(value) {
      if (!value) this.$emit('hidden-search');
    },
  },

  methods: {
    onClickReset() {
      console.log('onClickReset');
    },

    onClickSearch() {
      this.visible = false;
      console.log('onClickSearch');
    },

    onClickSort(value) {
      this.sortType = value;
    },
  },
};
</script>
