<template>
  <div class="app-header">
    <img v-show="topHeader" src="/static-dist/app/img/vue/app_head.png" />
    <div class="app-header__search clearfix" :class="{ mt16: !topHeader }">
      <div class="school-title pull-left text-overflow">{{ site.name }}</div>
      <a-input class="pull-right" :placeholder="'app.header.search' | trans">
        <a-icon slot="prefix" type="search" />
      </a-input>
    </div>
  </div>
</template>

<script>
import { Setting } from 'common/vue/service';

export default {
  props: {
    topHeader: {
      type: Boolean,
      default: true
    }
  },

  data() {
    return {
      site: {}
    }
  },

  mounted() {
    this.getSchoolName();
  },

  methods: {
    async getSchoolName() {
      this.site = await Setting.get('site');
    }
  }
}
</script>

<style lang="less" scoped>
.app-header {
  padding-bottom: 12px;
  width: 100%;
  background-color: #fff;

  img {
    width: 100%;
  }

  &__search {
    padding: 0 16px;

    .school-title {
      font-size: 18px;
      color: #333;
      font-weight: 600;
      line-height: 28px;
      max-width: 45%;
    }

    /deep/ .ant-input-affix-wrapper {
      width: 184px;
      .ant-input {
        height: 28px;
        border-radius: 24px;
      }
    }
  }
}
</style>
