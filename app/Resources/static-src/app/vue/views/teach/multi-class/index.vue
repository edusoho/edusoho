<template>
  <div>
     <a-button type="danger">
      Danger
    </a-button>
  </div>
</template>


<script>
import { MultiClass } from 'common/vue/service/index.js';

export default {
  name: 'ClassCourse',

  created() {
    console.log(MultiClass)
  }
}
</script>
