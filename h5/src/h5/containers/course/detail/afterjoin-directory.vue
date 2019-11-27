<template>
  <div class="afterjoin-directory">
    <div v-if="lessonNum>0">
      <swiper-directory
        v-if="chapterNum>0 || unitNum>0"
        id="swiper-directory"
        :item="item"
        :slide-index="slideIndex"
        :has-chapter="hasChapter"
        @changeChapter="changeChapter"
      />
      <div v-if="item.length>0" id="lesson-directory">
        <template v-if="chapterNum>0">
          <div v-for="(list, index) in item[slideIndex].children" :key="index" class="pd-bo">
            <util-directory :util="list" />
            <lesson-directory
              :lesson="list.children"
              :task-id="taskId"
              :task-number="item[slideIndex].lessonNum"
              :unit-num="item[slideIndex].unitNum"
              v-bind="$attrs"
              v-on="$listeners"
            />
          </div>
        </template>
        <div v-else class="pd-bo">
          <lesson-directory
            :lesson="item[slideIndex].children"
            :task-id="taskId"
            :task-number="item[slideIndex].lessonNum"
            :unit-num="item[slideIndex].unitNum"
            v-bind="$attrs"
            v-on="$listeners"
          />
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
import Api from "@/api";
import { mapState, mapMutations } from "vuex";
export default {
  name: "AfterjoinDirectory",
  components: {
    swiperDirectory,
    utilDirectory,
    lessonDirectory
  },
  data() {
    return {
      scroll: "",
      item: [],
      level: 3, // 目录层数
      chapterNum: 0, // 章节数
      unitNum: 0, // 节数
      lessonNum: 0, // 课时数
      currentChapter: 0, // 章节数目的索引,
      currentUnit: 0, // 章节数目的索引
      currentLesson: 0, // 课时数目的索引
      slideIndex: 0, // 顶部滑动的索引
      taskId: null,
      nodata: false
    };
  },
  computed: {
    ...mapState("course", {
      nextStudy: state => state.nextStudy,
      selectedPlanId: state => state.selectedPlanId,
      OptimizationCourseLessons: state => state.OptimizationCourseLessons
    }),
    hasChapter: function() {
      return this.chapterNum === 0;
    }
  },
  watch: {
    nextStudy: {
      handler: "getNextStudy",
      immediate: true
    },
    selectedPlanId: {
      handler: "processItem",
      immediate: true,
      deep: true
    }
  },
  methods: {
    getNextStudy() {
      if (this.nextStudy.nextTask) {
        this.taskId = Number(this.nextStudy.nextTask.id);
      }
    },
    // 处理数据
    processItem(val) {
      const res = this.OptimizationCourseLessons;
      this.resetData();
      if (!res.length) {
        this.nodata = true;
        return;
      }
      this.nodata = false;
      this.setItems(res);
      this.mapChild(this.item);
      this.startScroll();
    },
    resetData() {
      this.chapterNum = 0; // 章节数
      this.unitNum = 0; // 节数
      this.lessonNum = 0; // 课时数
    },
    setItems(res) {
      this.level = res.length === 1 && res[0].isExist == 0 ? 2 : 3;
      if (res.length === 1) {
        this.item = this.level === 2 ? res[0].children : res;
      } else if (res.length > 1) {
        this.item = res;
      }
    },
    // 递归遍历目录
    mapChild(list) {
      list.map((item, index) => {
        if (item.type === "chapter") {
          this.formatChapter(item, list, index);
        } else if (item.type === "unit") {
          this.formatUnit(item, list, index);
        } else if (item.type === "lesson") {
          this.formatLesson(item, list, index);
        } else {
          this.formatTask(item, list, index);
        }
      });
    },
    startScroll() {
      this.$nextTick(() => {
        if (!this.taskId) {
          return;
        }
        const NARTAB = 44;
        const PROCESSBAR = document.getElementById("progress-bar");
        const SWIPER = document.getElementById("swiper-directory");
        const TASK = document.getElementById(this.taskId);
        const PROCESSHEIGHT = !PROCESSBAR ? 0 : PROCESSBAR.offsetHeight;
        const SWIPERHEIGHT = !SWIPER ? 0 : SWIPER.offsetHeight;
        const TASKTOP = !TASK ? 0 : TASK.offsetTop;
        const scrolltop = TASKTOP - PROCESSHEIGHT - NARTAB - SWIPERHEIGHT;
        if (scrolltop < document.documentElement.clientWidth) {
          return;
        }
        window.scrollTo({
          top: scrolltop
        });
      });
    },
    formatChapter(chapter, list, index) {
      // 设置节课时任务默认数量
      this.currentChapter = index;
      chapter.chapterNum = 0;
      chapter.unitNum = 0;
      chapter.lessonNum = 0;
      chapter.tasksNum = 0;
      // 实际章数
      if (chapter.isExist) {
        this.chapterNum += 1;
        //当前章节下统计章数量
        this.computedNum(1, "chapterNum");
      }
      if (Array.isArray(chapter.children) && chapter.children.length > 0) {
        this.mapChild(chapter.children);
      }
    },
    formatUnit(unit, list, index) {
      this.currentUnit = index;
      unit.unitNum = 0;
      unit.lessonNum = 0;
      unit.tasksNum = 0;
      // 实际节数
      if (unit.isExist) {
        this.unitNum += 1;
        //当前章节下统计节数量
        this.computedNum(1, "unitNum");
      }
      if (Array.isArray(unit.children) && unit.children.length > 0) {
        //当前章节下统计课时数量
        this.computedNum(unit.children.length, "lessonNum");
        this.mapChild(unit.children);
      }
    },
    formatLesson(lesson, list, index) {
      this.currentLesson = index;
      // 实际课时数
      if (lesson.isExist) {
        this.lessonNum += 1;
      }
      if (lesson.tasks) {
        //当前章节下统计任务数量
        this.computedNum(lesson.tasks.length - 1, "tasksNum");
        this.mapChild(lesson.tasks);
      }
    },
    formatTask(task, list, index) {
      // 找到下一次学习对应的课时对应的滑动索引
      if (Number(task.id) === this.taskId) {
        this.slideIndex =
          this.level == 3 ? this.currentChapter : this.currentUnit;
      }
      this.getMainTask(task, index);
      // 把任务所属的章和节塞入到任务数组中,便于查找
      task.chapterIndex = this.currentChapter;
      task.unitIndex = this.currentUnit;
    },
    // task下放入task中type=lesson的索引
    getMainTask(task, index) {
      // 非默认教学计划 是0  默认计划是index
      index = task.mode ? index : 0;
      if (this.level === 3) {
        this.$set(
          this.item[this.currentChapter].children[this.currentUnit].children[
            this.currentLesson
          ],
          "index",
          index
        );
      } else {
        this.$set(
          this.item[this.currentUnit].children[this.currentLesson],
          "index",
          index
        );
      }
    },
    // 计算目录值
    computedNum(nums, types) {
      const current = this.level === 3 ? this.currentChapter : this.currentUnit;
      const num = this.item[current][types] + nums;
      this.$set(this.item[current], types, num);
    },
    // 更改当前子数据
    changeChapter(slideIndex) {
      this.slideIndex = slideIndex;
    }
  }
};
</script>

