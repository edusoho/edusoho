<template>
  <div class="afterjoin-directory">
    <div v-if="!noItem">
      <swiper-directory
        v-if="chapterNum>0 || unitNum>0"
        :item="item"
        :slideIndex="slideIndex"
        @changeChapter="changeChapter"
        :hasChapter="hasChapter"
      ></swiper-directory>
      <div
        ref="wrapper"
        class="wrapper"
        v-if="item.length>0"
      >
        <div>
          <template v-if="chapterNum>0">
            <div v-for="(list, index) in item[slideIndex].children" :key="index">
              <util-directory :util="list" v-if="chapterNum>0"></util-directory>
              <lesson-directory v-bind="$attrs" v-on="$listeners" :lesson="list.children" :taskId="taskId"></lesson-directory>
            </div>
          </template>
          <lesson-directory v-else :lesson="item[slideIndex].children" :taskId="taskId"></lesson-directory>
        </div>
      </div>
    </div>

    <div v-else class="noneItem">
      <img src="static/images/none.png" class="nodata" />
      <p>暂时还没有课程哦...</p>
    </div>
  </div>
</template>
<script>
import swiperDirectory from "./swiper-directory.vue";
import utilDirectory from "./util-directory.vue";
import lessonDirectory from "./lesson-directory.vue";
import BScroll from "better-scroll";
import Api from "@/api";
import { mapState, mapMutations } from "vuex";
export default {
  name: "afterjoinDirectory",
  components: {
    swiperDirectory,
    utilDirectory,
    lessonDirectory
  },
  data() {
    return {
      scroll: "",
      item: [],
      level: 3,//目录层数
      chapterNum: 0, //章节数
      unitNum: 0, //节数
      noItem: false, //无数据
      currentChapter: 0, //章节数目的索引,
      currentUnit: 0, //章节数目的索引
      currentLesson: 0, //课时数目的索引
      slideIndex: 0, //顶部滑动的索引
      taskId: 3960
    };
  },
  computed: {
    ...mapState("course", {
      OptimizationCourseLessons: state => state.OptimizationCourseLessons
    }),
    hasChapter:function(){
        if(this.chapterNum==0){
          return false
        }
        return true
    }
  },
  watch: {
    OptimizationCourseLessons: {
      handler: "processItem",
      immediate: true
    }
  },
  methods: {
    //处理数据
    processItem() {
      let res = this.OptimizationCourseLessons;
      const options = {
        click: true
      };
      this.chapterNum = 0; //章节数
      this.unitNum = 0; //节数
      if (res.length == 1) {
        if (
          res[0].children.length == 1 &&
          res[0].children[0].children.length == 0
        ) {
          this.noItem = true;
          this.item = [];
        } else {
          this.noItem = false;

          this.level = res.length == 1 && res[0].isExist == 0 ? 2 : 3;
          if (this.level == 2) {
            console.log(res[0].children);
            this.item = res[0].children;
          } else {
            this.item = res;
          }
          this.mapChild(this.item);

          // //初始化BScroll，定位到指定目录
          this.$nextTick(() => {
            this.scroll = new BScroll(this.$refs.wrapper, options);
            this.scroll.scrollToElement(document.getElementById(this.taskId));
            this.scroll.refresh();
          });
        }
      } else if (res.length > 1) {
        this.noItem = false;
        this.item = res;
        this.mapChild(this.item);

        //初始化BScroll，定位到指定目录
        this.$nextTick(() => {
          this.scroll = new BScroll(this.$refs.wrapper, options);
          this.scroll.scrollToElement(document.getElementById(this.taskId));
          this.scroll.refresh();
        });
      }
    },
    //类型操作
    judgType(item, list, index) {
      if (item.type == "chapter") {
        //设置节课时任务默认数量
        this.currentChapter = index;
        item.chapterNum = 0;
        item.unitNum = 0;
        item.lessonNum = 0;
        item.tasksNum = 0;
        //实际章数
        if (item.isExist) {
          this.chapterNum = this.chapterNum + 1;
          this.computedNum(1, "chapterNum");
        }
        if (Array.isArray(item.children) && item.children.length > 0) {
          this.mapChild(item.children);
        }
      } else if (item.type == "unit") {
        this.currentUnit = index;
        item.unitNum = 0;
        item.lessonNum = 0;
        item.tasksNum = 0;
        //实际节数
        if (item.isExist) {
          this.unitNum = this.unitNum + 1;
          this.computedNum(1, "unitNum");
        }
        if (Array.isArray(item.children) && item.children.length > 0) {
          this.computedNum(item.children.length, "lessonNum");
          this.mapChild(item.children);
        }
      } else if (item.type == "lesson") {
        this.currentLesson = index;
        if (item.tasks != null) {
          this.computedNum(item.tasks.length - 1, "tasksNum");
          this.mapChild(item.tasks);
        }
      } else {
        //找到下一次学习对应的课时对应的滑动索引
        if (item.id == Number(this.taskId)) {
          this.slideIndex =
            this.level == 3 ? this.currentChapter : this.currentUnit;
        }
        //task下放入task中type=lesson的索引
        if (this.level == 3) {
          if (item.mode == "lesson") {
            this.$set(
              this.item[this.currentChapter].children[this.currentUnit]
                .children[this.currentLesson],
              "index",
              index
            );
          }
        } else {
          if (item.mode == "lesson") {
            this.$set(
              this.item[this.currentUnit].children[this.currentLesson],
              "index",
              index
            );
          }
        }
        //把任务所属的章和节塞入到任务数组中
        item.chapterIndex = this.currentChapter;
        item.unitIndex = this.currentUnit;
      }
    },
    //计算目录值
    computedNum(nums, types) {
      let current
      if (this.level == 3) {
        current=this.currentChapter
      } else {
        current=this.currentUnit
      }
      let num = this.item[current][types] + nums;
       this.$set(this.item[current], types, num);
    },
    //递归遍历目录
    mapChild(list) {
      list.map((item, index) => {
        this.judgType(item, list, index);
      });
    },
    //更改当前子数据
    changeChapter(slideIndex) {
      this.slideIndex = slideIndex;
    }
  }
};
</script>

