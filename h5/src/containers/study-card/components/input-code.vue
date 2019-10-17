<template>
  <div class="input-code">
    <div class="schoolName text-overflow">{{settingsName}}</div>
    <div class="input-code__field">
      <van-field
          v-model="code"
          center
          clearable
          placeholder="请输入16位卡密"
          @input="handleCode"
          :error-message="errorMessage"
      >
      </van-field>
    </div>
    <van-button
        type="default"
        :class="['submit', {
          'active': code.length === 19
        }]"
        @click="submit"
        :disabled="code.length !== 19"
    >
      立即充值
    </van-button>
  </div>
</template>

<script>
  import Api from '@/api';
  import { mapState } from 'vuex';

  export default {
    name: 'entity-card',
    data() {
      return {
        code: '',
        errorMessage: ''
      };
    },
    computed: {
      ...mapState({
        settingsName: state => state.settings.name
      })
    },
    methods: {
      handleCode(value) {
        value = value.replace(/\W/g, '')
          .replace(/....(?!$)/g, '$& ');
        this.code = value.slice(0, 19);
      },
      submit() {
        if (this.code.length === 19) {
          const password = this.code.replace(/\s/g, '');
          Api.getMoneyCardByPassword({
            query: { password }
          })
            .then(res => {
              this.$router.push(`/moneycard/receive/${password}`);
            })
            .catch(err => {
              this.errorMessage = err.message;
            });
        }
      }
    },
  };
</script>

<style scoped>

</style>
