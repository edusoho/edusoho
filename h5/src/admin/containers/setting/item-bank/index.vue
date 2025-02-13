<template>
  <module-frame
    :is-active="isActive"
    :is-incomplete="isIncomplete"
    container-class="setting-course"
  >

    <div slot="preview" class="find-page__part">
      <e-item-bank
        :itembank="copyModuleData.data"
        :feedback="false"
        show-mode="admin"
      />
    </div>

    <div slot="setting">
      <e-suggest v-if="moduleData.tips" :suggest="moduleData.tips"></e-suggest>
      <header class="title">{{ $t('questionBankList.questionBankSettingList') }}</header>
      <div class="clearfix default-allocate__content">
        <!-- 列表名称 -->
        <setting-cell :title="$t('questionBankList.listName')" left-class="required-option">
          <el-input
            v-model="copyModuleData.data.title"
            size="mini"
            max-length="15"
            :placeholder="$t('questionBankList.pleaseEnterTheNameOfTheList')"
            clearable
          />
        </setting-cell>
        <!-- 题库分类 -->
        <setting-cell :title="$t('questionBankList.questionBank') + $t('questionBankList.classification')">
          <el-cascader
            v-show="sourceType === 'condition'"
            :options="itemBankCategories"
            :props="cascaderProps"
            v-model="categoryTempId"
            size="mini"
            :placeholder="$t('questionBankList.pleaseEnterTheNameOfTheList')"
            filterable
            change-on-select
          />
          <div v-show="sourceType === 'custom'" class="required-option">
            <el-button size="mini" @click="openModal"
              >{{ $t('questionBankList.choose') }}{{ $t('questionBankList.questionBank') }}</el-button
            >
          </div>
        </setting-cell>

        <draggable
          v-show="sourceType === 'custom' && copyModuleData.data.items.length"
          v-model="copyModuleData.data.items"
          class="default-draggable__list"
        >
          <div
            v-for="(courseItem, index) in copyModuleData.data.items"
            :key="index"
            class="default-draggable__item"
          >
            <div class="default-draggable__title text-overflow">
              {{ courseItem.displayedTitle || courseItem.title }}
            </div>
            <i class="h5-icon h5-icon-cuowu1 default-draggable__icon-delete"
              @click="deleteCourse(index)"
            />
          </div>
        </draggable>

        <!-- 排列顺序 -->
        <setting-cell v-show="sourceType === 'condition'" :title="$t('questionBankList.sortOrder2')">
          <div class="section-right__item pull-left">
            <el-select v-model="sort" placeholder="顺序" size="mini">
              <el-option
                v-for="item in sortOptions"
                :key="item.value"
                :label="item.label"
                :value="item.value"
              />
            </el-select>
          </div>
          <div class="section-right__item pull-right">
            <el-select v-model="lastDays" placeholder="时间区间" size="mini">
              <el-option
                v-for="item in dateOptions"
                :key="item.value"
                :label="item.label"
                :value="item.value"
              />
            </el-select>
          </div>
        </setting-cell>

        <!-- 显示个数 -->
        <div style="margin-top: 12px; color: #86909C; font-size: 14px;">最多显示3个题库</div>
      </div>
    </div>
  </module-frame>
</template>
<script>
import draggable from 'vuedraggable';
import itemBank from '&/components/e-item-bank/e-item-bank';
import moduleFrame from '../module-frame';
import settingCell from '../module-frame/setting-cell';
import { mapState, mapActions } from 'vuex';
import treeDigger from 'admin/utils/tree-digger';
import pathName2Portal from 'admin/config/api-portal-config';
import suggest from '&/components/e-suggest/e-suggest.vue';

export default {
  components: {
    'e-item-bank': itemBank,
    draggable,
    moduleFrame,
    settingCell,
    'e-suggest': suggest,
  },
  props: {
    active: {
      type: Boolean,
      default: false,
    },
    moduleData: {
      type: Object,
      default: () => {},
    },
    incomplete: {
      type: Boolean,
      default: false,
    },
  },
  data() {
    return {
      modalVisible: false,
      type: this.moduleData.type,
      layoutOptions: [
        {
          value: 'row',
          label: this.$t('questionBankList.rowByColumn')
        },
        {
          value: 'distichous',
          label: this.$t('questionBankList.oneRowAndTwoColumn')
        },
      ],
      sortOptions: [
        {
          value: '-studentNum',
          label: this.$t('questionBankList.joinMost')
        },
        {
          value: '-createdTime',
          label: this.$t('questionBankList.recentlyCreated')
        },
        {
          value: '-rating',
          label: this.$t('questionBankList.highestScore')
        },
        {
          value: 'recommendedSeq',
          label: this.$t('questionBankList.recommend')
        },
      ],
      cascaderProps: {
        label: 'name',
        value: 'id',
      },
      pathName: this.$route.name,
      categoryTempId: this.moduleData.data.categoryIdArray || ['0'],
      categoryDiggered: false,
      dateOptions: [
        {
          value: '7',
          label: this.$t('questionBankList.last7Days')
        },
        {
          value: '30',
          label: this.$t('questionBankList.last30Days')
        },
        {
          value: '90',
          label: this.$t('questionBankList.last90Days')
        },
        {
          value: '0',
          label: this.$t('questionBankList.allHistory')
        },
      ],
    };
  },
  computed: {
    ...mapState(['courseCategories', 'classCategories', 'itemBankCategories']),
    isActive: {
      get() {
        return this.active;
      },
      set() {},
    },
    uiStyle: {
      get() {
        if (
          this.$route.name === 'miniprogramSetting' ||
          this.$route.query.from === 'miniprogramSetting'
        ) {
          return 'old';
        } else {
          return 'new';
        }
      },
    },
    isIncomplete: {
      get() {
        return this.incomplete;
      },
      set() {},
    },
    copyModuleData: {
      get() {
        return this.moduleData;
      },
      set() {
        console.log('changed copyModuleData');
      },
    },
    showDateOptions() {
      const isNewCreated =
        this.moduleData.data.sort === this.sortOptions[1].value;
      const isRecommend =
        this.moduleData.data.sort === this.sortOptions[3].value;

      if (isNewCreated || isRecommend) {
        // 如果是 最新创建 或 推荐课程 时间区间为所有
        // eslint-disable-next-line vue/no-side-effects-in-computed-properties
        this.moduleData.data.lastDays = '0';
      }
      return !isNewCreated && !isRecommend;
    },
    sourceType: {
      get() {
        return this.copyModuleData.data.sourceType;
      },
      set(value) {
        this.copyModuleData.data.sourceType = value;
      },
    },
    sort: {
      get() {
        return this.copyModuleData.data.sort;
      },
      set(value) {
        this.copyModuleData.data.sort = value;
        this.fetchItemBankList();
      },
    },
    lastDays: {
      get() {
        return this.copyModuleData.data.lastDays.toString();
      },
      set(value) {
        this.copyModuleData.data.lastDays = value;
        this.fetchItemBankList();
      },
    },
    portal() {
      return pathName2Portal[this.pathName];
    },
  },
  watch: {
    copyModuleData: {
      handler(data) {
        this.$emit('updateModule', data);
      },
      deep: true,
    },
    categoryTempId: {
      handler(value) {
        if (!value.length) {
          return;
        }
        const endIndex = value.length - 1;
        // 多级分类需要拿到最后等级的id
        this.moduleData.data.categoryIdArray = value;
        this.moduleData.data.categoryId = value[endIndex];
        this.fetchItemBankList();
      },
    },
    courseCategories: {
      handler(tree) {
        if (!tree || this.categoryDiggered) return;
        let categoryExist = false;
        treeDigger(tree, (children, id) => {
          if (id) {
            categoryExist = id == this.categoryTempId;
          }
          return children;
        });
        this.categoryDiggered = true;

        if (categoryExist) return true;
        // this.categoryTempId = ['0'];
      },
      immediate: true,
    },
    itemBankCategories: {
      handler(tree) {
        if (!tree || this.categoryDiggered) return;
        let categoryExist = false;
        treeDigger(tree, (children, id) => {
          if (id) {
            categoryExist = id == this.categoryTempId;
          }
          return children;
        });
        this.categoryDiggered = true;

        if (categoryExist) return true;
      },
      immediate: true,
    },
    classCategories: {
      handler(tree) {
        if (!tree || this.categoryDiggered) return;

        let categoryExist = false;
        treeDigger(tree, (children, id) => {
          if (id) {
            categoryExist = id == this.categoryTempId;
          }
          return children;
        });
        this.categoryDiggered = true;

        if (categoryExist) return;
        this.categoryTempId = ['0'];
      },
      immediate: true,
    },
  },
  created() {
    this.fetchItemBankList();
  },
  methods: {
    ...mapActions(['getItemBankList']),
    getUpdatedCourses(courses) {
      this.copyModuleData.data.items = courses;
    },
    modalVisibleHandler(visible) {
      this.modalVisible = visible;
    },
    openModal() {
      this.modalVisible = true;
    },
    // 删除自定义课程
    deleteCourse(index) {
      this.copyModuleData.data.items.splice(index, 1);
    },
    fetchItemBankList() {
      if (this.sourceType === 'custom') return;

      this.getItemBankList({
        limit: 3,
        sort: this.sort,
        lastDays: this.lastDays,
        categoryId: this.categoryTempId.at(-1)
      })
        .then(res => {
          this.moduleData.data.items = res || [];
        })
        .catch(err => {
          this.$message({
            message: err.message,
            type: 'error',
          });
        });
    },
  },
};
</script>
