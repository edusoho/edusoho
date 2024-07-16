<script>
import Draggable from 'vuedraggable';
import _ from 'lodash';

export default {
  name: 'QuestionTypeDisplaySetMenu',
  props: {
    questionTypeDisplaySettings: undefined,
    settingKey: undefined,
  },
  components: {Draggable},
  data() {
    return {
      questionTypeDisplaySetting: [],
    }
  },
  methods: {
    onMenuVisibleChange(visible) {
      if (visible) {
        this.questionTypeDisplaySetting = _.cloneDeep(this.questionTypeDisplaySettings[this.settingKey]);
      } else {
        this.$emit('updateQuestionTypeDisplaySetting', this.settingKey, _.cloneDeep(this.questionTypeDisplaySetting));
      }
    },
  },
}
</script>

<template>
  <a-dropdown :trigger="['click']" placement="bottomRight" @visibleChange="onMenuVisibleChange">
    <div class="question-type-display-setting">
      <img
        src="/static-dist/app/img/question-bank/question-type-show-image.png"
        alt=""
      />
      <span>题型展示设置</span>
    </div>
    <a-menu slot="overlay" class="question-type-setting-menu">
      <draggable v-model="questionTypeDisplaySetting" handle=".question-type-setting-menu-item-label-icon"
                 drag-class="question-type-setting-menu-item-drag">
        <transition-group>
          <a-menu-item v-for="questionType in questionTypeDisplaySetting" :key="questionType.type"
                       class="question-type-setting-menu-item">
            <span class="question-type-setting-menu-item-label">
              <img
                class="question-type-setting-menu-item-label-icon"
                src="/static-dist/app/img/question-bank/question-type-drag.png"
                alt=""
              />
              <span class="question-type-setting-menu-item-label-text">{{ questionType.name }}</span>
            </span>
            <a-switch v-model:checked="questionType.checked"
                      class="question-type-setting-menu-item-switch"></a-switch>
          </a-menu-item>
        </transition-group>
      </draggable>
    </a-menu>
  </a-dropdown>
</template>
