<template>
  <div class="input-code">
    <div class="schoolName text-overflow">{{ settingsName }}</div>
    <div class="input-code__field">
      <van-field
        v-model="code"
        :error-message="errorMessage"
        center
        clearable
        placeholder="请输入16位卡密"
        @input="handleCode"
      />
    </div>
    <van-button
      :class="['submit', {
        'active': code.length === 19
      }]"
      :disabled="code.length !== 19"
      :style="{'display': currentHeight < defaultHeight ? 'none' : 'block'}"
      type="default"
      @click="submit"
    >
      立即充值
    </van-button>
  </div>
</template>

<script>
import Api from '@/api'
import { mapState } from 'vuex'
import { Toast } from 'vant'
export default {
  name: 'EntityCard',
  data() {
    return {
      code: '',
      errorMessage: '',
      defaultHeight: window.innerHeight,
      currentHeight: window.innerHeight
    }
  },
  computed: {
    ...mapState({
      settingsName: state => state.settings.name
    })
  },
  mounted() {
    window.onresize = () => {
      this.currentHeight = window.innerHeight
    }
  },
  methods: {
    handleCode(value) {
      value = value.replace(/\W/g, '')
        .replace(/....(?!$)/g, '$& ')
      this.code = value.slice(0, 19)
    },
    submit() {
      if (this.code.length === 19) {
        const password = this.code.replace(/\s/g, '')
        Api.getMoneyCardByPassword({
          query: { password }
        })
          .then(res => {
            this.$router.push(`/moneycard/receive/${password}`)
          })
          .catch(err => {
            Toast.fail(err.message)
            this.errorMessage = err.message
          })
      }
    }
  }
}
</script>

<style scoped>

</style>
