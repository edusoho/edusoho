<template>
  <layout
    :active="moduleType === currentModuleType"
    :is-first="isFirst"
    :is-last="isLast"
    :preview="preview"
    :validator-result="validatorResult"
    @event-actions="handleClickAction"
  >
    <div class="item-bank-list" v-if="currentItemBank">
      <div class="clearfix">
        <div class="item-bank-list__title pull-left text-overflow">{{ moduleData.title }}</div>
        <div class="item-bank-list__more pull-right">{{ 'site.btn.see_more' | trans }}<a-icon type="right" /></div>
      </div>

      <div class="clearfix mt16">
        <div class="pull-left current-item-bank">
          <img :src="currentItemBank.cover ? currentItemBank.cover.middle : ''" />
          <div class="item-bank-info">
            <div class="title text-overflow">{{ currentItemBank.title }}</div>
            <div class="clearfix">
              <div class="pull-left price">￥{{ currentItemBank.price }}</div>
              <div class="pull-right student-num">{{ currentItemBank.studentNum }}{{ 'decorate.islearning' | trans }}</div>
            </div>
          </div>
        </div>
        <div class="pull-left item-bank-container ml8">
          <div v-for="(item, index) in list.slice(0, 3)" 
            class="text-overflow item-bank-container__item"
            :class="{ 'current': currentIndex === index, 'mb8': index < 2 }">
            {{ item.title }}
          </div>
        </div>
      </div>
    </div>
  </layout>
</template>

<script>
import _ from 'lodash';
import { ItemBankExercises } from 'common/vue/service/index.js';
import moduleMixin from '../moduleMixin';

export default {
  name: 'ItemBankExercise',

  mixins: [moduleMixin],

  computed: {
    currentItemBank() {
      return this.list[this.currentIndex];
    }
  },

  data() {
    return {
      list: [],
      currentIndex: 0
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
    max-width: 60%;
    height: 24px;
    font-size: 16px;
    font-weight: 500;
    color: #333;
    line-height: 24px;
  }

  &__more {
    margin-top: 4px;
    font-size: 12px;
    color: #999;
    line-height: 16px;
  }

  .current-item-bank {
    width: 164px;
    height: 156px;
    border-radius: 6px;

    > img {
      width: 164px;
      height: 94px;
      border-radius: 6px 6px 0 0;
    }

    .item-bank-info {
      width: 164px;
      height: 62px;
      padding: 8px;
      border-radius: 0 0 6px 6px;
      background-color: #fff;

      .title {
        margin-bottom: 4px;
        font-weight: 500;
        font-size: 14px;
        color: #1D2129;
      }

      .price {
        line-height: 20px;
        font-size: 12px;
        font-weight: 500;
        color: #FF7A34;
      }

      .student-num {
        font-weight: 400;
        font-size: 12px;
        line-height: 20px;
        color: #86909C;
      }
    }
  }

  .item-bank-container {
    &__item {
      width: 162px;
      height: 46px;
      padding: 0 12px;
      font-size: 14px;
      font-weight: 400;
      line-height: 46px;
      color: #1D2129;
      background-color: #E7F7EE;
      border-radius: 6px;

      &.current {
        font-weight: 500;
        color: #fff;
        background-color: #3DCD7F;
      }
    }
  }
}
</style>

