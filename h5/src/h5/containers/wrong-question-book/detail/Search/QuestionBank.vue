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

      <!-- 考试筛选 -->
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

      <!-- 章节筛选 -->
      <template v-else>
        <div
          class="first-chapter"
          v-for="firstChapter in chapterData"
          :key="firstChapter.id"
        >
          <div class="exercise-search__item">
            <div
              class="exercise-search__name text-overflow"
              @click="onClickChange(firstChapter.id)"
            >
              <span class="first-chapter__bar">
                <span>
                  <span>{{ firstChapter.status ? '-' : '+' }}</span>
                </span>
              </span>
              {{ firstChapter.name }}
            </div>
            <div
              class="exercise-search__btn"
              @click="onClickSearch({ chapterId: firstChapter.id })"
            >
              查看错题
            </div>
          </div>

          <div
            v-if="firstChapter.children.length"
            :class="firstChapter.status ? '' : 'hidden-chapter-children'"
          >
            <div
              class="second-chapter"
              v-for="secondChapter in firstChapter.children"
              :key="secondChapter.id"
            >
              <div class="exercise-search__item">
                <div
                  class="exercise-search__name text-overflow"
                  @click="onClickChange(secondChapter.id)"
                >
                  <span class="second-chapter__bar">
                    <span>
                      <span>{{ secondChapter.status ? '-' : '+' }}</span>
                    </span>
                  </span>
                  {{ secondChapter.name }}
                </div>
                <div
                  class="exercise-search__btn"
                  @click="onClickSearch({ chapterId: secondChapter.id })"
                >
                  查看错题
                </div>
              </div>

              <div
                v-if="secondChapter.children.length"
                :class="secondChapter.status ? '' : 'hidden-chapter-children'"
              >
                <div
                  class="third-chapter"
                  v-for="thirdChapter in secondChapter.children"
                  :key="thirdChapter.id"
                >
                  <div class="exercise-search__item">
                    <div
                      class="exercise-search__name text-overflow"
                      @click="onClickChange(thirdChapter.id)"
                    >
                      <span class="third-chapter__bar">
                        <span></span>
                      </span>
                      {{ thirdChapter.name }}
                    </div>
                    <div
                      class="exercise-search__btn"
                      @click="onClickSearch({ chapterId: thirdChapter.id })"
                    >
                      查看错题
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </template>
    </div>
  </van-popup>
</template>

<script>
import _ from 'lodash';
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
        const { chapter, testpaper } = res;

        const loop = data => {
          _.forEach(data, item => {
            item.status = true;
            if (item.children) {
              loop(item.children);
            }
          });
        };

        loop(chapter);

        this.chapterData = chapter;
        this.testpaperData = testpaper;
      });
    },

    onClickSearch(params) {
      this.visible = false;
      this.$emit('on-search', params);
    },

    onClickChange(id) {
      const loop = (data, id) => {
        _.forEach(data, item => {
          if (item.id == id) {
            item.status = !item.status;
            return false;
          }
          if (item.children) {
            loop(item.children, id);
          }
        });
      };
      loop(this.chapterData, id);
    },
  },
};
</script>
