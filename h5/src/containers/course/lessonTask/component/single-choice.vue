<template>
  <div class="subject">
    <div class="subject-stem">
      <span class="subject-stem__order">{{ singleChoice.id }}、</span>
      <div class="subject-stem__content" v-html="singleChoice.stem"></div>
    </div>
    <van-radio-group v-model="radio">
      <van-radio class="subject-option"
        v-for="(item, index) in singleChoice.metas.choices"
        :key="item"
        :name="item"
      >
        <div class="subject-option__content" v-html="item"></div>
        <span
          slot="icon"
          slot-scope="props"
          class="subject-option__order"
        >{{ index|filterOrder }}</span>
      </van-radio>
    </van-radio-group>
  </div>
</template>

<script>
export default {
  name: 'single_choice',
  data() {
    return {
      singleChoice: {
        "id": "4",
        "type": "single_choice",
        "stem": "<p>测试单选卡夫卡的飞开口道福克斯宽度发发多少发的（）</p>\r\n",
        "score": "2.0",
        "metas": {
            "choices": [
                "<p>选择铁马冰河入梦来选择铁马冰河入梦来选择铁马冰河入梦来选择铁马冰河入梦来</p>\r\n",
                "<p>选择家祭无忘告乃翁</p>\r\n",
                "<p>选项日照香炉生紫烟</p>\r\n",
                "<p>选项葡萄美酒夜光杯</p>\r\n",
            ]
        },
        "categoryId": "0",
        "difficulty": "normal",
        "target": "course-20",
        "courseId": "0",
        "lessonId": "0",
        "parentId": "0",
        "subCount": "0",
        "finishedTimes": "0",
        "passedTimes": "0",
        "createdUserId": "2",
        "updatedUserId": "2",
        "courseSetId": "20",
        "seq": "1",
        "missScore": "0.0"
      },
      result: [],
    }
  },
  methods: {
  },
  filters: {
    filterOrder(index) {
      const arr = ['A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J'];
      return arr[index];
    },
  },

}
</script>