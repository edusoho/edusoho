<script>

import TestpaperTypeTag from '../../../common/src/TestpaperTypeTag.vue';
import {Testpaper} from 'common/vue/service';

export default {
  components: {TestpaperTypeTag},
  props: {
    itemBankId: null,
    id: null,
  },
  data() {
    return {
      paper: null
    }
  },
  async beforeMount() {
    this.paper = await Testpaper.get(this.id);
    console.log(paper);
  }
};
</script>
<template>
  <div class="test-create">
    <div class="test-create-title">
      <div class="test-create-title-left">
        <div class="test-create-title-left-back" @click="back">
          <span class="test-create-title-left-back-img">
            <img src="/static-dist/app/img/question-bank/back-image.png" alt=""/>
          </span>
          <span class="test-create-title-left-back-text">返回</span>
        </div>
        <i></i>
        <testpaper-type-tag :type="paper.type"/>
        <span>{{ paper.name }}</span>
      </div>
      <div class="test-create-title-right">
        <span class="test-create-title-right-item">
          <span class="test-create-title-right-text">试卷</span>
          <span class="test-create-title-right-number">0</span>
        </span>
        <i></i>
        <span class="test-create-title-right-item">
          <span class="test-create-title-right-text">总分</span>
          <span class="test-create-title-right-number">0.0</span>
        </span>
      </div>
    </div>
  </div>
</template>
