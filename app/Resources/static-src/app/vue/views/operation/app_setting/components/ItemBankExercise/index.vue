<template>
  <layout
    :active="moduleType === currentModuleType"
    :is-first="isFirst"
    :is-last="isLast"
    @event-actions="handleClickAction"
  >
    <div class="item-bank-list">
      <div class="clearfix">
        <div class="item-bank-list__title pull-left text-overflow">{{ moduleData.title }}</div>
        <div class="item-bank-list__more pull-right">查看更多<a-icon type="right" /></div>
      </div>

      <div :class="{ clearfix: moduleData.displayStyle === 'distichous' }">
        <component
          :is="currentComponent"
          v-for="item in list"
          :key="item.id"
          :item="item"
        />
      </div>
    </div>
  </layout>
</template>

<script>
import _ from 'lodash';
import { ItemBankExercises } from 'common/vue/service/index.js';
import moduleMixin from '../moduleMixin';
import ColumnItem from './ColumnItem.vue';
import RowItem from './RowItem.vue';

export default {
  name: 'ItemBankExercise',

  mixins: [moduleMixin],

  computed: {
    currentComponent() {
      const { displayStyle } = this.moduleData;
      return displayStyle === 'distichous' ? ColumnItem : RowItem;
    }
  },

  data() {
    return {
      list: []
    }
  },

  mounted() {
    this.fetchItemBank();
  },

  watch: {
    moduleData: {
      handler: function() {
        this.fetchItemBank();
      },
      deep: true
    }
  },

  methods: {
    async fetchItemBank() {
      const { sort, limit, lastDays, categoryId } = this.moduleData;
      const params = {
        params: {
          sort,
          limit,
          lastDays,
          categoryId
        }
      };
      const { data } = await ItemBankExercises.search(params);
      this.list = data;
    }
  }
}
</script>

<style lang="less" scoped>
.item-bank-list {
  padding-right: 16px;
  padding-left: 16px;

  .item-bank-list__title {
    position: relative;
    padding-left: 10px;
    max-width: 60%;
    height: 24px;
    font-size: 16px;
    font-weight: 500;
    color: #333;
    line-height: 24px;

    &::before {
      content: "";
      position: absolute;
      top: 6px;
      left: 0;
      width: 4px;
      height: 12px;
      background: #03c777;
      border-radius: 1px;
    }
  }

  &__more {
    margin-top: 4px;
    font-size: 12px;
    color: #999;
    line-height: 16px;
  }
}
</style>

