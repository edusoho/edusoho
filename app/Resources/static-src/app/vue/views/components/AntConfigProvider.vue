<template>
  <config-provider :locale="locale">
    <template #renderEmpty>
      <div style="position: relative; text-align: center">
        <img style="height: 120px;" src="/static-dist/app/img/vue/search_empty.png" alt="暂无数据">
        <p style="position: absolute; bottom: 10px; left: 50%; transform: translateX(-50%); color: #333;">暂无数据</p>
      </div>
    </template>

    <keep-alive v-if="$route.meta.keepAlive">
      <router-view></router-view>
    </keep-alive>
    <router-view v-else></router-view>
  </config-provider>
</template>

<script>
import zhCN from '@codeages/design-vue/lib/locale-provider/zh_CN';
import { ConfigProvider } from '@codeages/design-vue';

export default {
  name: 'AntConfigProvider',

  components: {
    ConfigProvider
  },

  data() {
    return {
      locale: zhCN
    }
  }
}
</script>
