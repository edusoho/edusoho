<template>
  <div class="subject">
    <div class="subject-stem">
      <span class="subject-stem__order">{{ determine.id }}、</span>
      <div class="subject-stem__content" v-html="determine.stem"></div>
    </div>
    <van-radio-group v-model="radio">
      <van-radio class="subject-option subject-option--determine" name="1">
        <div class="subject-option__content">对</div>
        <i
        slot="icon"
        slot-scope="props"
        class="iconfont icon-yes subject-option__order"
        ></i>
      </van-radio>
      <van-radio class="subject-option subject-option--determine" name="2">
        <div class="subject-option__content">错</div>
        <i
        slot="icon"
        slot-scope="props"
        class="iconfont icon-no subject-option__order"
        ></i>
      </van-radio>
    </van-radio-group>
  </div>
</template>

<script>
export default {
  name: 'determine-type',
  data() {
    return {
      radio: '1',
      determine: {
        "id": "4",
        "type": "determine",
        "stem": "<p>测试单选卡夫卡的飞开口道福克斯宽度发发多少发的（）</p>\r\n",
        "score": "2.0",
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
      },
    }
  },
  methods: {
  }
}
</script>