<template>
  <div class="input-code">
    <div class="schoolName text-overflow">第一网校/xxxxasdasdasdasdasdasd按顺序</div>
    <van-field
        v-model="code"
        center
        clearable
        placeholder="请输入16位卡密"
        class="input-code__field"
        @input="handleCode"
    >
    </van-field>
    <van-button
        type="default"
        :class="['submit', {
          'active': code.length === 19
        }]"
        @click="submit"
    >
      立即充值
    </van-button>
  </div>
</template>

<script>
  export default {
    name: 'entity-card',
    data() {
      return {
        code: ''
      };
    },
    methods: {
      handleCode(value) {
        value = value.replace(/\W/g, '')
          .replace(/....(?!$)/g, '$& ');
        this.code = value.slice(0, 19);
      },
      submit() {
        if (this.code.length === 19) {
          // 这边的逻辑之后改
          this.$router.push('/valid_card');
        }
      }
    },
  };
</script>

<style scoped>

</style>
