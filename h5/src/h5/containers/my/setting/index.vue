<script>
import {Dialog} from 'vant';
import * as types from '@/store/mutation-types';

export default {
  data() {
    return {
      settings: [
        {
          name: 'setting.changePassword',
          routerName: ''
        },
        {
          name: 'setting.language',
          routerName: 'settingLang'
        },
      ],
    };
  },
  methods: {
    logout() {
      Dialog.confirm({
        title: this.$t('setting.dropOut'),
        message: this.$t('setting.dropOutCancelConfirm'),
        confirmButtonText: this.$t('btn.confirm'),
        cancelButtonText: this.$t('btn.cancel')
      }).then(() => {
        this.$store.commit(types.USER_LOGIN, {
          token: '',
          user: {},
        });
        window.localStorage.setItem('mobile_bind_skip', '0');
        this.$router.push({
          name: 'my',
        });
      });
    },
    goTo(routerName) {
      this.$router.push({
        name: routerName,
      });
    }
  }
}
</script>

<template>
  <div class="setting-container">
    <div class="setting-item-container">
      <div
        v-for="(item, index) in settings"
        class="setting-item"
        @click="goTo(item.routerName)"
      >
        <div>{{ $t(item.name) }}</div>
        <img src="static/images/setting/right-arrow.svg" alt=""/>
      </div>
    </div>
    <div class="log-out-btn" @click="logout">{{ $t('btn.dropOut') }}</div>
  </div>
</template>
