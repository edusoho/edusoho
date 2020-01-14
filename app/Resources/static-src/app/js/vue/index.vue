
<div id="app" class="test-app">
  {{ message }}
</div>;

<script>
import Vue from 'vue';
var app = new Vue({
  el: '#app',
  data: {
    message: 'Hello Vue!'
  }
})
</script>

<style>
  .test-app {
    color: red;
  }
</style>