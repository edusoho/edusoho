<template>
  <div>
    <svg-icon icon="hbs-eye" />
    test
  </div>
</template>


<script>
import User from 'common/vue/service/index.js';
export default {
  name: 'ClassCourse',

  created() {
    console.log(User);
    User.User.test().then(res => {
      console.log(res);
    });
  }
}
</script>
