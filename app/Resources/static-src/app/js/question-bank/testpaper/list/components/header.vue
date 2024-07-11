<template>
  <div class="test-paper-list-header">
    <div class="testpaper-title-left">
      <div
        v-for="tab in tabList"
        :key="tab.code"
        :class="{ active: activeTab === tab.code }"
        @click="activeTab = tab.code"
      >
        <span>
          <img :src="tab.img" alt="" v-if="tab.img">
          {{ tab.name }}
        </span>
      </div>
    </div>
    <div class="testpaper-title-right">
      <button class="testpaper-title-right-create" @click="showCreatePaperModal">
        创建试卷
      </button>
      <button v-if="activeTab === 'all'" class="testpaper-title-right-import">导入固定卷</button>
    </div>

    <a-modal
      :width="492"
      class="create-test-paper-modal"
      v-model="modalVisible"
      title="创建试卷"
      cancelText="取消"
      okText="进入编辑"
      @ok="handleOk"
    >
      <div v-if="activeTab === 'all'" class="create-test-paper-modal-body">
        <div class="create-test-paper-modal-body-label">
          <span class="create-test-paper-modal-body-label-placeholder"></span>
          <span class="create-test-paper-modal-body-label-text">选择类型</span>
        </div>
        <div class="create-test-paper-modal-body-list">
          <div
            v-for="type in typeList"
            :key="type.code"
            class="create-test-paper-modal-body-list-item"
            :class="{ active: chooseType === type.code }"
            @click="chooseType = type.code"
          >
            <div class="create-test-paper-modal-body-list-item-content">
              <div class="create-test-paper-modal-body-list-item-content-title">
                <img :src="type.img" alt=""
                     class="create-test-paper-modal-body-list-item-content-title-icon list-image"/>
                <img :src="type.activeImg" alt=""
                     class="create-test-paper-modal-body-list-item-content-title-icon list-active-image"/>
                <span class="create-test-paper-modal-body-list-item-content-title-text">{{ type.title }}</span>
              </div>
              <div class="create-test-paper-modal-body-list-item-content-description">{{ type.description }}</div>
            </div>
            <img
              src="/static-dist/app/img/question-bank/select-image.png"
              alt=""
              class="create-test-paper-modal-body-list-item-checked"
            />
          </div>
        </div>
      </div>
      <div v-else-if="activeTab === 'ai_personality'" class="create-test-paper-modal-body flex-col">
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
              <span class="max-num">{{ testpaperName ? testpaperName.length : 0 }}/50</span>
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
      tabList: [
        {
          name: '试卷列表',
          code: 'all'
        },
        {
          name: '个性卷',
          code: 'ai_personality',
          img: '/static-dist/app/img/question-bank/testpaperAi.png',
        },
      ],
      activeTab: 'all',
      typeList: [
        {
          code: 'fixed-manual',
          img: '/static-dist/app/img/question-bank/fixed-image.png',
          activeImg:
            '/static-dist/app/img/question-bank/active-fixed-image.png',
          title: '固定卷-手动组卷',
          description: '学员答题时拿到相同的试卷，适合无防作弊要求的课程考试和按固定题目答题的的题库模拟考试',
        },
        {
          code: 'fixed-auto',
          img: '/static-dist/app/img/question-bank/fixed-image.png',
          activeImg:
            '/static-dist/app/img/question-bank/active-fixed-image.png',
          title: '固定卷-智能组卷',
          description: '可按题型分类抽题，学员答题时拿到相同的试卷',
        },
        {
          code: 'random',
          img: '/static-dist/app/img/question-bank/random-image.png',
          activeImg:
            '/static-dist/app/img/question-bank/active-random-image.png',
          title: '随机卷',
          description: '学员答题时拿到不同的试卷，适合有防作弊要求的课程考试和随机答题的题库模拟考试',
        },
        {
          code: 'personal',
          img: '/static-dist/app/img/question-bank/ai-image.png',
          activeImg: '/static-dist/app/img/question-bank/active-ai-image.png',
          title: 'AI个性卷',
          description: '不提前生成试卷，根据学员的答题情况AI判断生成，适合学员有一定刷题练习量后使用',
        },
      ],
      chooseType: 'fixed-manual',
      modalVisible: false,
      form: this.$form.createForm(this, {name: 'create-ai-paper'}),
      testpaperName: undefined,
    };
  },
  methods: {
    showCreatePaperModal() {
      this.modalVisible = true;
    },
    handleOk() {
      this.modalVisible = false;
      if (this.activeTab === 'all') {
        this.routeByChooseType();
      } else if (this.activeTab === 'ai_personality') {
        this.$router.push({
          name: 'create',
          query: {type: 'ai_personality', name: this.testpaperName}
        });
      }
    },
    routeByChooseType() {
      if (this.chooseType === 'fixed-manual') {
        window.location.href = document.getElementById('fixedTestPaperCreatePath').value;
      } else if (this.chooseType === 'fixed-auto') {
        window.location.href = document.getElementById('fixedAutoTestPaperCreatePath').value;
      } else if (this.chooseType === 'random') {
        this.$router.push({name: 'create'});
      } else {
        this.$router.push({name: 'create', query: {type: 'ai_personality'}});
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
    activeTab: function (val) {
      this.$emit('changeTab', val);
    }
  },
  mounted() {
    const type = this.$route.query.type;
    if (type === 'ai_personality') {
      this.activeTab = 'ai_personality';
    } else {
      this.activeTab = 'all';
    }
  }
}
</script>
