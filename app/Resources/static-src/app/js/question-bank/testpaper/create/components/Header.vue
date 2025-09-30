<template>
  <a-affix :offset-top="0">
    <div class="test-paper-save-header">
      <div class="test-paper-save-header-left">
        <div class="test-paper-save-header-left-back" @click="backConfirm">
        <span class="test-paper-save-header-left-back-img">
          <img src="/static-dist/app/img/question-bank/back-image.png" alt=""/>
        </span>
          <span class="test-paper-save-header-left-back-text">返回</span>
        </div>
        <i></i>
        <test-paper-type-tag :type="type"/>
      </div>
      <div class="test-paper-save-header-right">
      <span class="test-paper-save-header-right-item">
        <span class="test-paper-save-header-right-text">试题</span>
        <span class="test-paper-save-header-right-number">{{ paperQuestionCount }}</span>
      </span>
        <i></i>
        <span class="test-paper-save-header-right-item">
        <span class="test-paper-save-header-right-text">总分</span>
        <span class="test-paper-save-header-right-number">{{ paperScore }}</span>
      </span>
      </div>
    </div>
  </a-affix>
</template>

<script>
import TestPaperTypeTag from '../../TestPaperTypeTag';

export default {
  components: {
    TestPaperTypeTag,
  },
  props: {
    type: null,
    paperQuestionCount: undefined,
    paperScore: undefined,
  },
  methods: {
    backConfirm() {
      this.$emit('back-confirm');
    },
  },
}
</script>
