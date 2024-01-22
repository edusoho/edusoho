<template>
  <div class="mobile-category">
    <van-dropdown-menu>
      <van-dropdown-item :title="categoryTitle" @open="show = true" />
      <template v-for="(item, index) in dropdownData">
        <van-dropdown-item
          :key="index"
          v-model="item.value"
          :options="item.options"
          @change="search(1)"
        />
      </template>
    </van-dropdown-menu>
    <div class="class-list-mobile row" style="padding-left: 12px; padding-right: 12px;">
      <div class="col-md-4 col-sm-6">
        <div v-for="(item, index) in classroomList" :key="index" class="class-item class-item--tag" style="position: relative;">
          <div class="class-img">
            <span v-if="vipSetting.enabled && Number(item.vipLevelId)" class="tag-vip-free"></span>
            <a :href="'/classroom/'+item.id" target="_blank">
              <img
                :src="item.cover.large"
                :alt="item.title"
                class="img-responsive"
              />
              <h3>
                <span v-if="item.hasCertificate" class="certificate-tag" tabindex="0" role="button" data-container="body">{{ 'certificate'|trans }}</span>
                {{ item.title }}</h3>
              <div class="image-overlay"></div>
            </a>
          </div>
          <div class="class-serve">
            <ul class="list-unstyled clearfix">
              <li v-for="(itm, index) in services" :key="index" :class="getCodeToArr(item.service).includes(itm.value) ? 'active' : ''">
                <a
                  tabindex="0"
                  role="button"
                  data-container="body"
                  data-html="true"
                  title=""
                >
                  {{ itm.text }}
                </a>
              </li>
            </ul>
          </div>
          <span class="class-price">
            <span v-if="Number(item.price)" class="price"> {{ item.price }}{{ 'cny'|trans }} </span>
            <span v-else class="color-success"> {{ 'course.marketing_setup.preview.set_task.free'|trans }} </span>
          </span>
          <ul class="class-data clearfix">
            <li><i class="es-icon es-icon-book"></i>{{ item.courseNum }}</li>
            <li><i class="es-icon es-icon-people"></i>{{ Number(item.studentNum) + Number(item.auditorNum) }}</li>
            <li><i class="es-icon es-icon-textsms"></i>{{ item.threadNum }}</li>
          </ul>
        </div>
      </div>
    </div>
    <van-pagination
      v-if="total > 10"
      v-model="pageNum"
      @change="changePage"
      force-ellipses
      style="width: 200px; margin: 20px auto; justify-content: center;"
      :total-items="total"
      :show-page-size="5"
    >
      <template #prev-text>
        <van-icon name="arrow-left" />
      </template>
      <template #next-text>
        <van-icon name="arrow" />
      </template>
    </van-pagination>
    <van-popup v-model="show" round position="bottom">
      <van-cascader
        v-model="courseCategoriesValue"
        :title="'classroom.category.choose.text'|trans "
        :options="courseCategories"
        @close="show = false"
        @finish="onFinish"
      />
    </van-popup>
  </div>
</template>
<script>
import CATEGORY_DEFAULT from "../../course/explore/category-default-config.js";
import { More } from "common/vue/service";
export default {
  data() {
    return {
      services: [
        {text: Translator.trans("classroom.services.choose.train"), value: 'homeworkReview'},
        {text: Translator.trans("classroom.services.choose.try"), value: 'testpaperReview'},
        {text: Translator.trans("classroom.services.choose.ask"), value: 'teacherAnswer'},
        {text: Translator.trans("classroom.services.choose.suspected"), value: 'liveAnswer'},
        {text: Translator.trans("classroom.services.choose.dynamic"), value: 'event'},
        {text: Translator.trans("classroom.services.choose.industry"), value: 'workAdvise'},
      ],
      total: 0,
      pageNum: 1,
      show: false,
      categoryValue: "",
      courseCategoriesValue: "",
      categoryTitle: Translator.trans("category"),
      vipLevels: [],
      courseCategories: [],
      dropdownData: [],
      dataDefault: CATEGORY_DEFAULT.new_classroom_list,
      classroomList: [],
      vipSetting: {}
    };
  },
  async created() {
    await this.getVipSetting();

    await this.getLevelInfo();

    // 初始化班级分类
    this.initCourseCategories();

    // 初始化下拉筛选数据
    this.initDropdownData();
  },
  watch: {},
  computed: {},
  methods: {
    getCodeToArr(activeServices=[]) {
      return activeServices.map(service => service.code);
    },
    async getVipSetting() {
      const data = await More.getVip()
      this.vipSetting = data
    },
    isShowTag(item) {
      if (item.courseSet.type == "live") {
        return true;
      }

      if (item.courseSet.type == "reservation") {
        return true;
      }

      if (item.tryLookable == "1") {
        return true;
      }
      return false;
    },
    onFinish({ selectedOptions }) {
      this.show = false;
      this.categoryTitle = selectedOptions
        .map((option) => option.text)
        .join("/");
      this.categoryValue = selectedOptions[0].value;
      this.search(1);
    },
    changePage(page) {
      window.location.href =
        window.location.pathname +
        `?sort=${this.dataDefault[1].value}&vipLevelId=${this.dataDefault[0].value}&categoryId=${this.categoryValue}&page=${page}`;
    },
    getCategoryDescById(categories, categoryId) {
      if (!categories || categories.length === 0) return null;

      for (let i = 0; i < categories.length; i++) {
        const currentCategory = categories[i];

        if (currentCategory.value === categoryId) {
          return currentCategory.text;
        }

        const categoryText = this.getCategoryDescById(
          currentCategory.children,
          categoryId
        );

        if (categoryText) return categoryText;
      }

      return null;
    },
    async search(page) {
      if (page) {
        this.changePage(page);
      }

      let query = {
        sort: $('[name="sort"]').val(),
        categoryId: $('[name="categoryId"]').val(),
        offset: 10 * (parseInt($('[name="page"]').val()) - 1),
        limit: 10,
      };

      if (
        $('[name="vipLevelId"]').val() &&
        $('[name="vipLevelId"]').val() != "0"
      ) {
        query.vipLevelId = $('[name="vipLevelId"]').val();
      }

      const { data, paging } = await More.searchClassroom(query);
      this.classroomList = data;
      this.total = paging.total;

      this.pageNum = Number($('[name="page"]').val());
      console.log(data);
    },
    async getLevelInfo() {
      const data = await More.getVipLevels();
      this.vipLevels = data;
    },
    async initCourseCategories() {
      // 获取班级分类数据
      const res = await More.getClassroomCategories();
      this.courseCategories = this.initOptions({
        text: Translator.trans("site.btn.see_more"),
        value: "0",
        data: res,
      });
      const categoryId = $('[name="categoryId"]').val();

      if (categoryId && categoryId !== "0") {
        this.categoryTitle = this.getCategoryDescById(
          this.courseCategories,
          $('[name="categoryId"]').val()
        );
        this.categoryValue = categoryId
      }

      this.search();
    },
    async initDropdownData() {
      this.dataDefault[0].options = this.initOptions({
        text: Translator.trans("classroom.vip.category.text"),
        data: this.vipLevels,
      });

      if($('[name="vipLevelId"]').val()) {
        this.dataDefault[0].value = $('[name="vipLevelId"]').val()
      }
      this.dataDefault[1].value = $('[name="sort"]').val()
      this.dropdownData = this.dataDefault;
    },
    initOptions({ text, value = "0", data }) {
      const options = text ? [{ text, value }] : [];
      data.forEach((item) => {
        const optionItem = {
          text: item.name,
          value: item.id,
        };

        if (item.children && item.children.length > 0) {
          optionItem.children = this.initOptions({
            text: Translator.trans("site.btn.see_more"),
            value: item.id,
            data: item.children,
          });
        }

        options.push(optionItem);
      });

      return options;
    },
  },
};
</script>