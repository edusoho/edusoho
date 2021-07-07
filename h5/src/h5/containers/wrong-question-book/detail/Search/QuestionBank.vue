<template>
  <van-popup
    v-model="visible"
    position="right"
    :style="{
      width: '80%',
      height: '100%',
      background: '#f5f5f5',
      borderRadius: '10px 0 0 10px',
    }"
  >
    <div class="exercise-search">
      <div class="exercise-search__title">题目来源</div>
      <template v-if="exerciseMediaType === 'testpaper'">
        <div
          class="exercise-search__item"
          v-for="(testpaper, index) in testpaperData"
          :key="index"
        >
          <div class="exercise-search__name text-overflow">
            {{ testpaper.assessmentName }}
          </div>
          <div
            class="exercise-search__btn"
            @click="onClickSearch({ testpaperId: testpaper.assessmentId })"
          >
            查看错题
          </div>
        </div>
      </template>
      <template v-else>
        fsd
      </template>
    </div>
  </van-popup>
</template>

<script>
import Api from '@/api';

export default {
  name: 'QuestionBankSearch',

  props: {
    show: {
      type: Boolean,
      required: true,
    },

    poolId: {
      type: String,
      required: true,
    },

    exerciseMediaType: {
      type: String,
      required: true,
    },
  },

  data() {
    return {
      visible: this.show,
      chapterData: [],
      testpaperData: [],
    };
  },

  watch: {
    show(value) {
      if (value) this.visible = value;
    },

    visible(value) {
      if (!value) this.$emit('hidden-search');
    },
  },

  created() {
    this.fetchCondition();
  },

  methods: {
    fetchCondition() {
      Api.getWrongQuestionCondition({
        query: {
          poolId: this.poolId,
        },
        params: {
          exerciseMediaType: this.exerciseMediaType,
        },
      }).then(res => {
        this.chapterData = res.chapter;
        this.testpaperData = res.testpaper;
      });
    },

    onClickSearch(params) {
      this.visible = false;
      this.$emit('on-search', params);
    },
  },
};
</script>
