<template>
  <div class="testpaper-title">
    <div class="testpaper-title-left">
      <div
        v-for="item in list"
        :key="item.id"
        :class="{ active: activeIndex == item.id }"
        @click="activeIndex = item.id"
      >
        <span>
          <img :src="item.img" alt="" v-if="item.id == 1">
          {{ item.name }}
        </span>
      </div>
    </div>
    <div class="testpaper-title-right">
      <button class="testpaper-title-right-create" @click="showModal">
        创建试卷
      </button>
      <button class="testpaper-title-right-import">导入固定卷</button>
      
      <div class="testpaper-create-modal">
        <a-modal
          v-model="isShowModal"
          title="创建试卷"
          cancelText="取消"
          okText="创建"
          @ok="handleOk"
        >
          <p class="create-modal-type">选择类型</p>
          <ul class="create-mode-type-list">
            <li
              v-for="data in testTypeList"
              :key="data.id"
              :class="{ active: activeTestTypeIndex == data.id }"
              @click="activeTestTypeIndex = data.id"
            >
              <img :src="data.img" alt="" class="list-image" />
              <img :src="data.activeImg" alt="" class="list-active-image" />
              <span>{{ data.title }}</span>
              <img
                src="/static-dist/app/img/question-bank/select-image.png"
                alt=""
                class="select-image"
              />
              <p>{{ data.text }}</p>
            </li>
          </ul>
        </a-modal>
      </div>
    </div>
  </div>
</template>
<script>
export default {
  data() {
    return {
      list: [
        {
          id: 0,
          name: "试卷列表",
        },
        {
          id: 1,
          name: "个性卷",
          img: "/static-dist/app/img/question-bank/testpaperAi.png",
        },
      ],
      activeIndex: 0,
      activeTestTypeIndex: 0,
      isShowModal: false,
      activeImg: false,
      testTypeList: [
        {
          id: 0,
          img: "/static-dist/app/img/question-bank/fixed-image.png",
          activeImg:
            "/static-dist/app/img/question-bank/active-fixed-image.png",
          title: "固定卷-手动组卷",
          text: "手动选择题目组成固定内容的试卷",
        },
        {
          id: 1,
          img: "/static-dist/app/img/question-bank/fixed-image.png",
          activeImg:
            "/static-dist/app/img/question-bank/active-fixed-image.png",
          title: "固定卷-智能组卷",
          text: "按题型和分类抽题组成固定内容的试卷",
        },
        {
          id: 2,
          img: "/static-dist/app/img/question-bank/random-image.png",
          activeImg:
            "/static-dist/app/img/question-bank/active-random-image.png",
          title: "随机卷",
          text: "根据出题规则随机生成试卷",
        },
        {
          id: 3,
          img: "/static-dist/app/img/question-bank/ai-image.png",
          activeImg: "/static-dist/app/img/question-bank/active-ai-image.png",
          title: "AI个性卷",
          text: "不提前生成试卷，根据学员的答题情况AI判断生成，适合学员有一定刷题练习量后使用",
        },
      ],
    };
  },
  methods: {
    showModal() {
      this.isShowModal = true;
    },
    handleOk() {
      this.isShowModal = false;
      this.$router.push({
        name: "testPaperCreate"
      });
    },
  },
};
</script>