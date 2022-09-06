<template>
  <layout
    :active="moduleType === currentModuleType"
    :is-first="isFirst"
    :is-last="isLast"
    :preview="preview"
    :validator-result="validatorResult"
    @event-actions="handleClickAction"
  >
    <div class="clearfix" style="margin: 0 16px 8px;">
      <div class="information__title pull-left text-overflow">{{ 'decorate.information' | trans }}</div>
      <div class="information__more pull-right">{{ 'site.btn.see_more' | trans }}<a-icon type="right" /></div>
    </div>

    <div class="information">
      <div class="information__item clearfix" v-if="currentData[0]">
        <div class="number" style="background-color: #F53F3F;">1</div>
        <div class="content text-overflow">{{ currentData[0].body | filterHtml }}</div>
      </div>
      <div class="information__item clearfix" v-if="currentData[1]">
        <div class="number" style="background-color: #FF900E;">2</div>
        <div class="content text-overflow">{{ currentData[1].body | filterHtml }}</div>
      </div>
      <div class="information__item clearfix" v-if="currentData[2]">
        <div class="number" style="background-color: #F9CC45;">3</div>
        <div class="content text-overflow">{{ currentData[2].body | filterHtml }}</div>
      </div>
    </div>
  </layout>
</template>

<script>
import _ from 'lodash';
import { Information } from 'common/vue/service/index.js';
import moduleMixin from '../moduleMixin';

export default {
  name: 'Information',

  mixins: [moduleMixin],

  filters: {
    filterHtml(body) {
      return body.replace(/<\/?.+?>/g, "")
    }
  },

  data() {
    return {
      currentData: {}
    }
  },

  created() {
    this.fetchData();
  },

  methods: {
    async fetchData() {
      const result = await Information.search()

      if (Array.isArray(result) && result.length > 0) {
        this.currentData = result
      }
    }
  }
}
</script>

<style lang="less" scoped>
  .information {
    margin: 0 16px;
    padding: 0 12px;
    background-color: #fff;
    border-radius: 6px;

    &__item:not(:last-child) {
      border-bottom: 1px solid #F2F3F5;
    }

    &__item {
      padding: 12px 0;

      .number {
        float: left;
        width: 18px;
        height: 18px;
        margin-top: 2px;
        margin-right: 8px;
        text-align: center;
        line-height: 18px;
        color: #fff;
        font-weight: 500;
        font-size: 14px;
        border-radius: 4px;
      }

      .content {
        float: left;
        width: 266px;
        line-height: 22px;
        color: #1D2129;
        font-size: 14px;
      }
    }
  }

  .information__title {
    position: relative;
    max-width: 60%;
    height: 24px;
    font-size: 16px;
    font-weight: 500;
    color: #333;
    line-height: 24px;
  }

  .information__more {
    margin-top: 4px;
    font-size: 12px;
    color: #999;
    line-height: 16px;
  }
</style>
