<template>
  <div class="subject">
    <div class="subject-stem">
      <span class="serial-number">{{ number }}、</span>
      <div class="subject-stem__content" v-html="stem"></div>
    </div>

    <div class="material-title" v-if="itemdata.parentTitle">
      <span class="serial-number">问题{{itemdata.materialIndex}}：</span>
      <div v-html="itemdata.stem"></div>
    </div>

    <van-checkbox-group v-model="result" class="answer-paper" @change="choose()">
      <van-checkbox
        class="subject-option"
        v-for="(item, index) in itemdata.metas.choices"
        :key="index"
        :name="index"
      >
        <div class="subject-option__content" v-html="item"></div>
        <span
          slot="icon"
          slot-scope="props"
          class="subject-option__order subject-option__order--square"
        >{{ index|filterOrder }}</span>
      </van-checkbox>
    </van-checkbox-group>
  </div>
</template>

<script>
export default {
  name: "choice-type",
  data() {
    return {
      choice: {
        id: "5",
        type: "choice",
        stem: "<p>测试多选题</p>\r\n",
        score: "2.0",
        metas: {
          choices: [
            "<p>选项A</p>\n",
            "<p>选项B</p>\n",
            "<p>选项C</p>\n",
            "<p>选项D</p>\n"
          ]
        },
        categoryId: "0",
        difficulty: "normal",
        target: "course-20",
        courseId: "0",
        lessonId: "0",
        parentId: "0",
        subCount: "0",
        finishedTimes: "0",
        passedTimes: "0",
        createdUserId: "2",
        updatedUserId: "2",
        courseSetId: "20",
        seq: "2",
        missScore: "0.0"
      },
      result: this.answer
    };
  },
  props: {
    itemdata: {
      type: Object,
      default: () => {}
    },
    number: {
      type: Number,
      default: 1
    },
    answer:{
      type: Array,
      default: () => []
    },
  },
  computed: {
    stem: {
      get() {
        if (this.itemdata.parentTitle) {
          return this.itemdata.parentTitle.stem;
        } else {
          return this.itemdata.stem;
        }
      }
    }
  },
  filters: {
    filterOrder(index) {
      const arr = ["A", "B", "C", "D", "E", "F", "G", "H", "I", "J"];
      return arr[index];
    }
  },
  methods: {
    choose(name){
        this.$emit('choiceChoose',this.result,this.itemdata.id)
    }
  },
};
</script>