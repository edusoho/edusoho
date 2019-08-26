<template>
  <div class="afterjoin-directory">
    <div v-if="lessonNum>0">
      <swiper-directory
        v-if="chapterNum>0 || unitNum>0"
        :item="item"
        :slideIndex="slideIndex"
        @changeChapter="changeChapter"
        :hasChapter="hasChapter"
      ></swiper-directory>
      <div style="overflow:scroll" id="lesson-directory" v-if="item.length>0">
          <template v-if="chapterNum>0">
            <div v-for="(list, index) in item[slideIndex].children" :key="index" class="pd-bo">
              <util-directory :util="list"></util-directory>
              <lesson-directory
                v-bind="$attrs"
                v-on="$listeners"
                :lesson="list.children"
                :taskId="taskId"
                :taskNumber="item[slideIndex].lessonNum"
                :unitNum="item[slideIndex].unitNum"
              ></lesson-directory>
            </div>
          </template>
          <div v-else class="pd-bo">
            <lesson-directory 
            :lesson="item[slideIndex].children" 
            :taskId="taskId" 
            :taskNumber="item[slideIndex].lessonNum" 
            :unitNum="item[slideIndex].unitNum"
            v-bind="$attrs"
            v-on="$listeners"></lesson-directory>
          </div>
      </div>
    </div>

    <div v-if="nodata && lessonNum==0" class="noneItem">
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
import { connect } from "net";
const options = {
  click: true,
  taps: true
};
export default {
  name: "afterjoinDirectory",
  components: {
    swiperDirectory,
    utilDirectory,
    lessonDirectory
  },
  props:['isFixed'],
  data() {
    return {
      scroll: "",
      item: [],
      level: 3, //目录层数
      chapterNum: 0, //章节数
      unitNum: 0, //节数
      lessonNum: 0, //课时数
      noItem: false, //无数据
      currentChapter: 0, //章节数目的索引,
      currentUnit: 0, //章节数目的索引
      currentLesson: 0, //课时数目的索引
      slideIndex: 0, //顶部滑动的索引
      taskId: -1,
      nodata:false
    };
  },
  computed: {
    ...mapState("course", {
      nextStudy: state => state.nextStudy,
      selectedPlanId: state => state.selectedPlanId,
      OptimizationCourseLessons: state => state.OptimizationCourseLessons
    }),
    hasChapter: function() {
      if (this.chapterNum == 0) {
        return false;
      }
      return true;
    }
  },
  watch: {
    nextStudy: {
      handler: "getNextStudy",
      immediate: true,
    },
    selectedPlanId: {
      handler: "processItem",
      immediate: true,
      deep:true,
    }
  },
  methods: {
    getNextStudy() {
      if (this.nextStudy.nextTask) {
        this.taskId = Number(this.nextStudy.nextTask.id);
        // if (this.scroll) {
        //   this.scroll.scrollToElement(document.getElementById(this.taskId));
        //   this.scroll.refresh();
        // }
      }
    },
    //处理数据
    processItem(val){
      let res = this.OptimizationCourseLessons;
      if(res.length==0){
         this.nodata=true;
         this.lessonNum=0;
        return
      }
      this.nodata=false;
      const that = this;
      this.chapterNum = 0; //章节数
      this.unitNum = 0; //节数
      this.lessonNum = 0; //课时数
      if (res.length == 1) {
        this.noItem = false;
        this.level = res.length == 1 && res[0].isExist == 0 ? 2 : 3;
        if (this.level == 2) {
          this.item = res[0].children;
        } else {
          this.item = res;
        }
        this.mapChild(this.item);
        this.$nextTick(() => {
          that.newScroll();
        });
      } else if (res.length > 1) {
        this.noItem = false;
        this.item = res;
        this.mapChild(this.item);
        this.$nextTick(() => {
          that.newScroll();
        });
      }
      this.nodata=true;
    },
    //定位到指定目录
    newScroll() {
      const NARTAB=44;
      const PROCESSBAR=document.getElementById("progress-bar");
      const SWIPER = document.getElementById("swiper-directory");
      const PROCESSHEIGHT =  PROCESSBAR == null ? 0 : PROCESSBAR.offsetHeight;
      const SWIPERHEIGHT =  SWIPER == null ? 0 : SWIPER.offsetHeight;
      // 52 有待更改改，测试数据
      let scrolltop=document.getElementById(this.taskId).offsetTop-
                    PROCESSHEIGHT-NARTAB-SWIPERHEIGHT-52;
      if(scrolltop<document.documentElement.clientWidth){
        return
      }
      window.scrollTo({
        top: scrolltop
      })
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
        //实际课时数
        if (item.isExist) {
          this.lessonNum = this.lessonNum + 1;
        }
        if (item.tasks != null) {
          this.computedNum(item.tasks.length - 1, "tasksNum");
          this.mapChild(item.tasks);
        }
      } else {
        //找到下一次学习对应的课时对应的滑动索引
        if (item.id == this.taskId) {
          this.slideIndex =
            this.level == 3 ? this.currentChapter : this.currentUnit;
        }
        //task下放入task中type=lesson的索引
        if (this.level == 3) {
          //非默认教学计划
          if(item.mode==null){
            this.$set(
              this.item[this.currentChapter].children[this.currentUnit]
                .children[this.currentLesson],
              "index",
              0
            );
          }else if (item.mode == "lesson") {
            this.$set(
              this.item[this.currentChapter].children[this.currentUnit]
                .children[this.currentLesson],
              "index",
              index
            );
          }
        } else {
          if(item.mode==null){
            this.$set(
              this.item[this.currentUnit].children[this.currentLesson],
              "index",
              0
            );
          }else if (item.mode == "lesson") {
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
      let current;
      if (this.level == 3) {
        current = this.currentChapter;
      } else {
        current = this.currentUnit;
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

