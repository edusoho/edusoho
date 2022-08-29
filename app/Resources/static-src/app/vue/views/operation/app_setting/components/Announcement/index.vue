<template>
  <layout
    :active="moduleType === currentModuleType"
    :is-first="isFirst"
    :is-last="isLast"
    :preview="preview"
    :validator-result="validatorResult"
    @event-actions="handleClickAction"
  >
    <div class="announcement">
      <div class="announcement__tips">
        <img style="height: 20px;" src="/static-dist/app/img/admin-v2/icon_announcement.png" srcset="/static-dist/app/img/admin-v2/icon_announcement.png" />
      </div>
      <div class="text-overflow announcement__content">{{ currentData.content || '暂无公告' }}</div>
    </div>
  </layout>
</template>

<script>
import _ from 'lodash';
import { Announcement } from 'common/vue/service/index.js';
import moduleMixin from '../moduleMixin';

export default {
  name: 'Announcement',

  mixins: [moduleMixin],

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
      const result = await Announcement.search()

      if (Array.isArray(result) && result.length > 0) {
        this.currentData = result[0]
        this.currentData.content = this.currentData.content.replace(/<\/?.+?>/g, "")
      }
    }
  }
}
</script>

<style lang="less" scoped>
  .announcement {
    display: flex;
    align-items: center;
    height: 40px;
    margin: 0 16px;
    padding: 0 12px;
    line-height: 40px;
    background-color: #fff;
    border-radius: 58px;

    &__tips {
      position: relative;
      padding-right: 13px;

      .word {
        font-family: 'YouSheBiaoTiHei';
        font-weight: 400;
        font-size: 18px;
        line-height: 20px;
      }

      &::after {
        content: '';
        position: absolute;
        top: 14px;
        right: 5px;
        width: 1px;
        height: 14px;
        background-color: #f2f3f5;
      }
    }

    &__content {
      position: relative;

      &::after {
        
      }
    }
  }
</style>
