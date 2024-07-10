<template>
  <div class="test-paper-list-header">
    <div class="testpaper-title-left">
      <div
        v-for="item in list"
        :key="item.code"
        :class="{ active: activeIndex === item.code }"
        @click="activeIndex = item.code"
      >
        <span>
          <img :src="item.img" alt="" v-if="item.img">
          {{ item.name }}
        </span>
      </div>
    </div>
    <div class="testpaper-title-right">
      <button class="testpaper-title-right-create" @click="showCreatePaperModal">
        创建试卷
      </button>
      <button v-if="activeIndex === 'all'" class="testpaper-title-right-import">导入固定卷</button>
    </div>

    <a-modal
      :width="488"
      class="create-test-paper-modal"
      v-model="isShowModal"
      title="创建试卷"
      cancelText="取消"
      :okText="activeIndex === 'all' ? '创建' : '进入编辑'"
      @ok="handleOk"
    >
      <div v-if="activeIndex === 'all'" class="create-test-paper-modal-body">
        <div class="create-test-paper-modal-body-label">
          <span class="create-test-paper-modal-body-label-placeholder"></span>
          <span class="create-test-paper-modal-body-label-text">选择类型</span>
        </div>
        <div class="create-test-paper-modal-body-list">
          <div
            v-for="data in testTypeList"
            :key="data.id"
            class="create-test-paper-modal-body-list-item"
            :class="{ active: activeTestTypeIndex === data.id }"
            @click="activeTestTypeIndex = data.id"
          >
            <div class="create-test-paper-modal-body-list-item-content">
              <div class="create-test-paper-modal-body-list-item-content-title">
                <img :src="data.img" alt=""
                     class="create-test-paper-modal-body-list-item-content-title-icon list-image"/>
                <img :src="data.activeImg" alt=""
                     class="create-test-paper-modal-body-list-item-content-title-icon list-active-image"/>
                <span class="create-test-paper-modal-body-list-item-content-title-text">{{ data.title }}</span>
              </div>
              <div class="create-test-paper-modal-body-list-item-content-description">{{ data.text }}</div>
            </div>
            <img
              src="/static-dist/app/img/question-bank/select-image.png"
              alt=""
              class="create-test-paper-modal-body-list-item-checked"
            />
          </div>
        </div>
      </div>
      <div v-else-if="activeIndex === 'ai_personality'" class="create-test-paper-modal-body flex-col">
        <img src="/static-dist/app/img/question-bank/create-ai-paper.png" alt=""/>
        <a-form :form="form" layout="horizontal">
          <div class="test-paper-save-form-item">
            <div class="test-paper-save-form-item-label">
              <span class="test-paper-save-form-item-label-required">*</span>
              <span class="test-paper-save-form-item-label-text">试卷名称</span>
            </div>
            <a-form-item>
              <a-input
                placeholder="请输入试卷名称"
                @change="handleChangeName"
                v-decorator="[
                'testpaperName',
                { initialValue: testpaperName, rules: [{ required: true, message: '请输入试卷名称' }] },
              ]"
              />
              <span class="max-num">{{testpaperName ? testpaperName.length : 0}}/50</span>
            </a-form-item>
          </div>
        </a-form>
      </div>
    </a-modal>
  </div>
</template>

<script>
export default {
  data() {
    return {
      list: [
        {
          id: 0,
          name: '试卷列表',
          code: 'all'
        },
        {
          id: 1,
          name: '个性卷',
          code: 'ai_personality',
          img: '/static-dist/app/img/question-bank/testpaperAi.png',
        },
      ],
      activeIndex: 'all',
      activeTestTypeIndex: 0,
      isShowModal: false,
      testTypeList: [
        {
          id: 0,
          img: '/static-dist/app/img/question-bank/fixed-image.png',
          activeImg:
            '/static-dist/app/img/question-bank/active-fixed-image.png',
          title: '固定卷-手动组卷',
          text: '手动选择题目组成固定内容的试卷',
        },
        {
          id: 1,
          img: '/static-dist/app/img/question-bank/fixed-image.png',
          activeImg:
            '/static-dist/app/img/question-bank/active-fixed-image.png',
          title: '固定卷-智能组卷',
          text: '按题型和分类抽题组成固定内容的试卷',
        },
        {
          id: 2,
          img: '/static-dist/app/img/question-bank/random-image.png',
          activeImg:
            '/static-dist/app/img/question-bank/active-random-image.png',
          title: '随机卷',
          text: '根据出题规则随机生成试卷',
        },
        {
          id: 3,
          img: '/static-dist/app/img/question-bank/ai-image.png',
          activeImg: '/static-dist/app/img/question-bank/active-ai-image.png',
          title: 'AI个性卷',
          text: '不提前生成试卷，根据学员的答题情况AI判断生成，适合学员有一定刷题练习量后使用',
        },
      ],
      form: this.$form.createForm(this, {name: 'create-ai-paper'}),
      testpaperName: undefined,
    };
  },
  methods: {
    showCreatePaperModal() {
      this.isShowModal = true;
    },
    handleOk() {
      this.isShowModal = false;
      if (this.activeIndex === 'all') {
        this.$router.push({
          name: 'create'
        });
      } else if (this.activeIndex === 'ai_personality') {
        this.$router.push({
          name: 'create', query: {type: 'ai_personality', name: this.testpaperName}
        });
      }
    },
    handleChangeName(value) {
      this.testpaperName = value.target.value;
      this.form.setFieldsValue({
        testpaperName: value,
      });
    },
  },
  watch: {
    activeIndex: function (val) {
      this.$emit('changeTab', val);
    }
  },
  mounted() {
    const type = this.$route.query.type;
    if (type === 'ai_personality') {
      this.activeIndex = 'ai_personality';
    } else {
      this.activeIndex = 'all';
    }
  }
};
</script>
