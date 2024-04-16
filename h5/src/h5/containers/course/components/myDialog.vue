<template>
  <div class="btn">
    <div class="dialog">
      <div class="message">将为您跳转至App进行学习</div>
      <div class="footer">
        <button @click="cancel()">取消</button>
        <a :href="openAppUrl">继续</a>
      </div>
    </div>
  </div>
</template>
<script>
export default {
  props: ['openAppUrl'],
  methods: {
    cancel() {
      this.$emit("cancel")
    }
  }
}
</script>
<style>
.btn {
  width: 100%;
  height: 100%;
  position: fixed;
  top: 0;
  left: 0;
  background-color: rgba(0, 0, 0, 0.3);
}

.dialog {
  width: 311px;
  height: 118px;
  position: fixed;
  top: 50%;
  left: 50%;
  transform: translate(-50%, -50%);
  background-color: #fff;
  font-size: 16px;
  display: flex;
  flex-direction: column;
  border-radius: 16px;
}

.message {
  flex: 1;
  text-align: center;
  line-height: 70px;
  color: #37393D;
}

.footer {
  height: 48px;
  display: flex;
}

button {
  width: 160px;
  height: 48px;
  color: #5E6166;
  background-color: #fff;
  border-top: 1px solid #ebedf0;
  background-color: none;
  font-size: 16px;
  border-radius: 0px 0px 0px 16px;
}

a {
  color: #00BE63;
  width: 160px;
  height: 48px;
  text-align: center;
  line-height: 46px;
  background-color: none;
  border-top: 1px solid #ebedf0;
  border-left: 1px solid #ebedf0;
  text-decoration: none;
  font-size: 16px;
  display: block;
  border-radius: 0px 0px 16px 0px;
}
</style>