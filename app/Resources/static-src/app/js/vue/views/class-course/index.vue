<template>
  <div>
    <svg-icon icon="hbs-eye" />
  </div>
</template>


<script>
import User from '../../service/index';
export default {
  name: 'ClassCourse',

  created() {
    User.User.test().then(res => {
      console.log(res);
    });
  }
}
</script>
