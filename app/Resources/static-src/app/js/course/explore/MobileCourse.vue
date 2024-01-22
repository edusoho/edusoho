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
    <div class="course-list course-list-new">
      <div class="row" style="padding: 10px;">
        <div
          v-for="(item, index) in courseList"
          :key="index"
          class="col-lg-3 col-md-4 col-xs-6 course-item-wrap"
        >
          <div class="course-item">
            <span v-if="Number(item.vipLevelId)" class="tag-vip-free"></span>
            <div class="course-img">
              <a :href="'/course/'+item.id" target="_blank">
                <span v-if="item.courseSet.discountId > 0 && item.courseSet.discount == 0" class="tag-discount free"></span>
                <span v-if="item.courseSet.discountId > 0 && item.courseSet.discount != 0" class="tag-discount"></span>
                <div v-if="isShowTag(item)" class="course-tag clearfix">
                  <span v-if="item.courseSet.type == 'live'" class="pull-right">
                    <span class="cd-mr8">
                      直播课程<span class="course-tag__dot"></span>
                    </span>
                  </span>
                  <span v-if="item.courseSet.type == 'reservation'" class="pull-right">
                    <span class="cd-mr8">
                      预约课程<span class="course-tag__dot"></span>
                    </span>
                  </span>
                  <span v-if="item.tryLookable == '1'"><i class="es-icon es-icon-video color-white"></i>试看</span>

                </div>

                <img
                  :src="item.courseSet.cover.large"
                  :alt="item.courseSetTitle"
                  class="img-responsive"
                />
              </a>
            </div>
            <div class="course-info">
              <div class="title">
                <a v-if="item.hasCertificate"
                  class="certificate-tag"
                  >证</a
                >
                <a
                  class="link-darker"
                  :href="'/course/'+item.id"
                  target="_blank"
                  :title="item.courseSetTitle"
                >
                  {{ item.courseSetTitle }}
                </a>
              </div>
              <div class="metas clearfix">
                <span class="num">
                  <i class="es-icon es-icon-people"></i>{{ item.studentNum }}
                </span>

                <span class="comment">
                  <i class="es-icon es-icon-textsms"></i>{{ item.ratingNum }}
                </span>

                <span class="course-price-widget">
                  <span v-if="Number(item.price)" class="price"> {{ item.price }}元 </span>
                  <span v-else class="free">免费</span>
                </span>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
    <van-pagination 
      v-if="total>10"
      v-model="pageNum" 
      @change="changePage"
      force-ellipses 
      style="width: 200px; justify-content: center;" 
      :total-items="total" 
      :show-page-size="5">
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
        title="请选择课程分类"
        :options="courseCategories"
        @close="show = false"
        @finish="onFinish"
      />
    </van-popup>
  </div>
</template>
<script>
import CATEGORY_DEFAULT from "./category-default-config.js";
import { More } from "common/vue/service";
export default {
  data() {
    return {
      total: 0,
      pageNum: 1,
      show: false,
      categoryValue: '',
      courseCategoriesValue: "",
      categoryTitle: "分类",
      vipLevels: [],
      courseCategories: [],
      dropdownData: [],
      dataDefault: CATEGORY_DEFAULT.new_course_list,
      courseList: [],
      vipSetting: {}
    }
  },
  async created() {

    await this.getVipSetting();

    await this.getLevelInfo();

    // 初始化课程分类
    this.initCourseCategories();

    // 初始化下拉筛选数据
    this.initDropdownData();

  },
  watch: {
  },
  computed: {
  },
  methods: {
    async getVipSetting() {
      const data = await More.getVip()
      this.vipSetting = data
    },
    isShowTag(item) {
      if (item.courseSet.type == 'live') {
        return true
      }

      if (item.courseSet.type == 'reservation') {
        return true
      }

      if (item.tryLookable == '1') {
        return true
      }
      return false;
    },
    onFinish({ selectedOptions }) {
      this.show = false;
      this.categoryTitle = selectedOptions.map((option) => option.text).join("/");
      this.categoryValue = selectedOptions[0].value
      this.search(1)
    },
    changePage (page) {
      window.location.href = window.location.pathname+`?type=${this.dataDefault[0].value}&sort=${this.dataDefault[2].value}&vipLevelId=${this.dataDefault[1].value}&categoryId=${this.categoryValue}&page=${page}`
    },
    getCategoryDescById(categories, categoryId) {
      if (!categories || categories.length === 0) return null

      for (let i = 0; i < categories.length; i++) {
        const currentCategory = categories[i]

        if (currentCategory.value === categoryId) {
          return currentCategory.text
        }

        const categoryText = this.getCategoryDescById(currentCategory.children, categoryId)

        if (categoryText) return categoryText
      }

      return null
    },
    async search(page) {
      if(page) {
        this.changePage(page)
      }

      let query = {
        type: $('[name="type"]').val(),
        sort: $('[name="sort"]').val(),
        categoryId: $('[name="categoryId"]').val(),
        offset: 10 * (parseInt($('[name="page"]').val())-1),
        limit: 10
      }

      if ($('[name="vipLevelId"]').val() && $('[name="vipLevelId"]').val() != '0') {
        query.vipLevelId = $('[name="vipLevelId"]').val()
      }

      const { data, paging } = await More.searchCourse(query)
      this.courseList = data
      this.total = paging.total

      this.pageNum = Number($('[name="page"]').val())
      console.log(data);
    },
    async getLevelInfo() {
      const data = await More.getVipLevels();
      this.vipLevels = data;
    },
    async initCourseCategories() {
      // 获取课程分类数据
      const res = await More.getCourseCategories();
      this.courseCategories = this.initOptions({
        text: "全部",
        value: "0",
        data: res,
      });
      const categoryId = $('[name="categoryId"]').val();

      if (categoryId && categoryId !== '0') {
        this.categoryTitle = this.getCategoryDescById(this.courseCategories, $('[name="categoryId"]').val())
        this.categoryValue = categoryId
      }

      this.search()
    },
    async initDropdownData() {
      this.dataDefault[1].options = this.initOptions({
        text: "会员课程",
        data: this.vipLevels,
      });

      this.dataDefault[0].value = $('[name="type"]').val()

      if($('[name="vipLevelId"]').val()) {
        this.dataDefault[1].value = $('[name="vipLevelId"]').val()
      }
      this.dataDefault[2].value = $('[name="sort"]').val()
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
            text: "全部",
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