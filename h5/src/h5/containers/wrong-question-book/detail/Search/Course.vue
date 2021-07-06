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

    <div class="search-checked">
      <div class="search-checked__item">
        <div class="checked-title">题目来源</div>
        <div class="checked-result">选择题目来源</div>
        <div class="checked-active"></div>
      </div>
      <div class="search-checked__item">
        <div class="checked-title">任务名称</div>
        <div class="checked-result">选择任务名称</div>
        <div class="checked-active"></div>
      </div>
    </div>

    <div class="search-select">
      <div class="search-select__toolbar">
        {{ pickerTitle }}
        <div class="search-select__confirm">确定</div>
      </div>

      <van-picker :columns="columns" @change="onChange" />
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
      pickerTitle: '选择题目来源',
      columns: ['杭州', '宁波', '温州', '绍兴', '湖州', '嘉兴', '金华', '衢州'],
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

    onChange(picker, value, index) {
      console.log(value, index);
    },
  },
};
</script>
