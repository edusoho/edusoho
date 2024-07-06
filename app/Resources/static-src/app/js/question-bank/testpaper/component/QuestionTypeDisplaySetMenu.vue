<script>
import Draggable from 'vuedraggable';

export default {
  name: 'QuestionTypeDisplaySetMenu',
  props: {
    defaultQuestionAllTypes: undefined
  },
  watch: {
    defaultQuestionAllTypes: {
      immediate: true,
      handler(val) {
        if (!val) {
          return;
        }
        this.questionAllTypes = val
      }
    }
  },
  components: {Draggable},
  data() {
    return {
      questionDisplayTypes: [],
      questionAllTypes: [
        {
          type: "single_choice",
          name: "单选题",
          checked: true,
          score: 2,
        },
        {
          type: "choice",
          name: "多选题",
          checked: true,
          score: 2,
        },
        {
          type: "essay",
          name: "问答题",
          checked: true,
          score: 2,
        },
        {
          type: "uncertain_choice",
          name: "不定项",
          checked: true,
          score: 2,
        },
        {
          type: "determine",
          name: "判断题",
          checked: true,
          score: 2,
        },
        {
          type: "fill",
          name: "填空题",
          checked: true,
          score: 2,
        },
        {
          type: "material",
          name: "材料题",
          checked: true,
          score: 2,
        },
      ],
    }
  },
  methods: {
    renderQuestionTypeTable() {
      let displayTypes = [];
      for (const type of this.questionAllTypes) {
        if (type.checked) {
          displayTypes.push(type);
        }
      }
      this.questionDisplayTypes = displayTypes;
    },
    onMenuVisibleChange(visible) {
      if (!visible) {
        this.renderQuestionTypeTable();
        this.$emit('updateDisplayQuestionType',this.questionAllTypes, this.questionDisplayTypes);
      }
    },
  },
  mounted() {
    this.renderQuestionTypeTable();
    this.$emit('updateDisplayQuestionType',this.questionAllTypes, this.questionDisplayTypes);
  }
};
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
      <draggable v-model="questionAllTypes" handle=".question-type-setting-menu-item-label-icon"
                 drag-class="question-type-setting-menu-item-drag">
        <transition-group>
          <a-menu-item v-for="questionType in questionAllTypes" :key="questionType.type"
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
