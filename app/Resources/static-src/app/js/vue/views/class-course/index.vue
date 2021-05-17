<template>
  <div>
    <svg-icon icon="hbs-eye" />
    test
  </div>
</template>


<script>
import User from 'common/vue/api/index.js';
export default {
  name: 'ClassCourse',

  created() {
    User.User.test().then(res => {
      console.log(res);
    });
  }
}
</script>
