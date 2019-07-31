<template>
  <e-panel title="课程目录" class="directory" :hidde-title="hiddeTitle">
    <!-- 暂无学习任务 -->
    <div v-if="courseLessons.length == 0" class="empty">暂无学习任务</div>
    <div class="directory-list" v-else>
      <div class="directory-list__item" v-for="(item, chapterIndex) in chapters" :key="chapterIndex">
        <div class="directory-list__item-chapter"
          @click="item.show = !item.show"
          v-if="item.type === 'chapter'">
          <span class="text-overflow">第{{ item.number }}{{ courseSettings.chapter_name }}：{{ item.title }}</span>
          <i :class="[ item.show ? 'icon-packup': 'icon-unfold']"></i>
        </div>

        <div :class="['directory-list__item-unit',
          {'unit-show': item.show || (item.show && tasks[chapterIndex][0].type === 'lesson')}]"
          v-for="(lesson, lessonIndex) in tasks[chapterIndex]" :key="lessonIndex">

          <div class="lesson-cell__unit" v-if="lesson.type === 'unit'">
            <span class="lesson-cell__unit-title text-overflow">第{{ lesson.number }}{{ courseSettings.part_name }}：{{ lesson.title }}</span>
            <i :class="[ unitShow[`${chapterIndex}-${lessonIndex}`] ? 'icon-packup': 'icon-unfold']" @click="lessonToggle(chapterIndex, lessonIndex)"></i>
          </div>

       <div class="lesson-cell__hour text-overflow" v-if="lesson.type === 'lesson'"
            :class="{'lesson-show': unitShow[lesson.show]|| (tasks[chapterIndex][0].type === 'lesson')}">
            <div v-if="lesson.tasks.length > 1">
              <div class="lesson-cell__lesson text-overflow">
                <i class="h5-icon h5-icon-dot color-primary text-18"></i>
                <span>{{ Number(lesson.isOptional) ? '选修 ' : '课时 ' }} {{ Number(lesson.isOptional) ? ' ' : `${lesson.number - optionalMap[lesson.number]}：` }}{{ lesson.title }}</span>
              </div>
              <div :class="['box', 'show-box']"
                v-for="(task, taskIndex) in lesson.tasks" :key="taskIndex">
                <div class="lesson-cell">
                  <span class="lesson-cell__number pull-left" v-if="!Number(lesson.isOptional)">{{ filterNumber(task, taskIndex) }}</span>
                  <div :class="['lesson-cell__content', lesson.tasks[taskIndex].type === 'live' ? 'pr10' : '']" @click="lessonCellClick(task)">
                    <div class="lesson-cell__text">
                      <span class="text-overflow">{{ task.title }}</span>
                      <span v-if="lesson.tasks[taskIndex].type === 'live' && lesson.tasks[taskIndex].status === 'published'" :class="[liveClass(lesson.tasks[taskIndex]), 'live-text', 'ml5']">{{ lesson.tasks[taskIndex] | liveStatusText }}</span>
                    </div>
                    <span class="lesson-cell-last__text">{{ task | taskType }}{{ task | filterTask }}</span>
                  </div>
                  <div v-if="!details.member" :class="['lesson-cell__status', details.member ? '' : task.tagStatus]">
                    {{ filterTaskStatus(task) }}
                  </div>
                </div>
              </div>
            </div>

            <div v-if="lesson.tasks.length === 1">
              <div class="lesson-cell__lesson text-overflow" @click="lessonCellClick(lesson.tasks[0])">
                <i class="h5-icon h5-icon-dot color-primary text-18 pull-left"></i>
                <div class="lesson-cell__text ">
                  <span class="pl3 text-overflow">{{ Number(lesson.isOptional) ? '选修 ' : '课时 ' }} {{ Number(lesson.isOptional) ? ' ' : `${lesson.number - optionalMap[lesson.number]}：` }}{{ lesson.tasks[0].title }}</span>
                  <span v-if="lesson.tasks[0].status === 'published' && lesson.tasks[0].type === 'live'" :class="[liveClass(lesson.tasks[0]), 'live-text', 'ml5']">{{ lesson.tasks[0] | liveStatusText }}</span>
                </div>
                <div class="lesson-cell">
                  <span class="lesson-cell__number">{{ filterNumber(lesson.tasks[0], 0, true) }}</span>
                  <div class="lesson-cell__content pl3">
                    <span class="lesson-cell-last__text">{{ lesson.tasks[0] | taskType }}{{ lesson.tasks[0] | filterTask }}</span>
                  </div>
                  <div v-if="!details.member" :class="['lesson-cell__status', details.member ? '' : lesson.tasks[0].tagStatus]">
                    {{ filterTaskStatus(lesson.tasks[0]) }}
                  </div>
                </div>
              </div>
            </div>
          </div>

        </div>
      </div>
    </div>
  </e-panel>
</template>
<script>
  import { mapState, mapMutations } from 'vuex';
  import { Dialog, Toast } from 'vant';
  import { formatCompleteTime } from '@/utils/date-toolkit';
  import * as types from '@/store/mutation-types';
  import redirectMixin from '@/mixins/saveRedirect';
  import Api from '@/api';

  export default {
    mixins: [redirectMixin],
    props: {
      hiddeTitle: {
        type: Boolean,
        default: false
      },
      errorMsg: {
        type: String,
        default: '',
      }
    },
    data() {
      return {
        directoryArray: [],
        chapters: [],
        tasks: [],
        unit: [],
        optionalMap: [],
        unitShow: {},
        firstLesson: ''
      }
    },
    computed: {
      ...mapState('course', {
        details: state => state.details,
        joinStatus: state => state.joinStatus,
        courseLessons: state => state.courseLessons,
        selectedPlanId: state => state.selectedPlanId,
      }),
      ...mapState(['courseSettings', 'user']),
      currentCourseType() {
        return Number(this.details.parentId) ? '班级' : '课程'
      },
      liveClass() {
        return (lesson) => {
          const now = new Date().getTime();
          const startTimeStamp = new Date(lesson.startTime * 1000);
          const endTimeStamp = new Date(lesson.endTime * 1000);
          if (now <= startTimeStamp) {
            return 'grey-medium';
          }
          if (now > endTimeStamp) {
            if (lesson.activity.replayStatus === 'ungenerated') {
              return 'live-done';
            }
            return 'live-replay';
          }
          return 'living';
        }
      }
    },
    watch: {
      selectedPlanId: {
        deep:true,
        immediate: true,
        handler(v) {
          console.log(v)
          if (!this.courseLessons.length) return;
          let task = 0;
          let unit = 0;
          let chapter = 0;
          this.directoryArray = this.courseLessons.map(item => { //后续可考虑 getTasks 方法的遍历可以合并成一个？
            if (!this.firstLesson) {
              this.firstLesson = item.type;
            }
            task ++;
            this.$set(item, 'show', true);
            if (item.type === 'chapter') {
              chapter ++;
              task = 0;
            }
            if (item.type === 'unit') {
              unit = task - 1;
            }
            if (item.type === 'lesson') {
              const chaptersIndex = this.firstLesson === 'chapter'
                ? Math.max(chapter - 1, 0) : Math.max(chapter, 0);
              const unitIndex = unit;
              this.$set(item, 'show', `${chaptersIndex}-${unitIndex}`);
            }

            return item;
          })

          this.getTasks(this.directoryArray);
        }
      }
    },
    methods: {
      ...mapMutations('course', {
        setSourceType: types.SET_SOURCETYPE
      }),
      lessonToggle(chapterIndex, lessonIndex) {
        const index = `${chapterIndex}-${lessonIndex}`;
        this.$set(this.unitShow, index, !this.unitShow[index]);
      },
      filterNumber(task, index, single) {
        if (single) {
          return '';
        }
        return task.isOptional === '1' ? '选修' : (index + 1);
      },
      getTasks (data) {
        let temp = [];
        let optionalNum = 0;
        let lessonIndex = 0;
        this.chapters = [];
        this.tasks = [];
        this.unit = [];
        this.optionalMap = [];

        data.forEach(item => {
          if (item.type == 'lesson') {
            lessonIndex ++;
            optionalNum = Number(item.isOptional) ? (++optionalNum) : optionalNum;
            this.optionalMap[lessonIndex] = optionalNum;
            item.tasks.forEach(task => {
              task['tagStatus'] = this.getCurrentStatus(task);
            })
          }

          if (item.type !== 'chapter') {
            if (item.type === 'unit') {
              const chaptersIndex = this.firstLesson === 'chapter'
                ? this.chapters.length - 1 : this.chapters.length;
              const unitIndex = temp.length;
              this.$set(this.unitShow, `${chaptersIndex}-${unitIndex}`, true);
              this.unit.push(item);
            }
            temp.push(item);
          } else {
            if (temp.length > 0) {
              this.tasks.push([].concat(temp));
              temp = [];
            } else if (this.chapters.length > 0) {
              this.tasks.push([]);
            }

            this.chapters.push(item);
          }

        })

        if (!this.unit.length || this.firstLesson !== 'chapter') {
          this.$set(this.unitShow, `${0}-${0}`, true);
        }

        const last = data.length - 1;

        if (data[last].type !== 'chapter') {
          this.tasks.push(temp);
        }

        if (data[0].type !== 'chapter') {
          this.chapters.unshift({ show: true });
        }
      },
      getCurrentStatus (task) {
        if (Number(task.isFree)) {
          return 'is-free';
        }
        if (Number(this.details.tryLookable)
          && task.type === 'video'
          && task.activity.mediaStorage) {
          return 'is-tryLook';
        }
        return '';
      },
      filterTaskStatus (task){
        if (!this.details.member && task.tagStatus === 'is-free') {
          return '免费';
        }
        if (!this.details.member && task.tagStatus === 'is-tryLook') {
          return '试看';
        }
        return '';
      },
      lessonCellClick (task) {
        // 课程错误和未发布状态，不允许学习任务
        if (this.errorMsg || task.status === 'create') {
          this.$emit('showDialog');
          return;
        }

        const details = this.details;

        !details.allowAnonymousPreview && this.$router.push({
          name: 'login',
          query: {
            redirect: this.redirect,
          }
        });
        if (!this.joinStatus
          && ['is-tryLook', 'is-free'].includes(task.tagStatus)) {
        // trylook and free video click
          switch (task.type) {
            case 'video':
            case 'audio':
              this.$router.push({
                name: 'course_try',
              })

              this.setSourceType({
                sourceType: task.type,
                taskId: task.id
              });
              break;
            case 'doc':
            case 'text':
            case 'ppt':
              this.$router.push({
                name: 'course_web',
                query: {
                  courseId: this.selectedPlanId,
                  taskId: task.id,
                  type: task.type,
                  preview: 1,
                }
              })
              break;
            default:
              return Toast(`请先加入${this.currentCourseType}`);
          }
        } else {
          this.joinStatus ? this.showTypeDetail(task) : Toast(`请先加入${this.currentCourseType}`);
        }
        //join after click
      },
      showTypeDetail (task) {
        if (task.status !== 'published') {
          Toast('敬请期待');
          return;
        }
        switch(task.type) {
          case 'video':
            if (task.mediaSource === 'self') {
              this.setSourceType({
                sourceType: 'video',
                taskId: task.id
              })
            } else {
              Toast('暂不支持此类型');
            }
            break;
          case 'audio':
            this.setSourceType({
              sourceType: 'audio',
              taskId: task.id
            })
            break;
          case 'text':
          case 'ppt':
          case 'doc':
            this.$router.push({
              name: 'course_web',
              query: {
                courseId: this.selectedPlanId,
                taskId: task.id,
                type: task.type
              }
            })
            break;
          case 'live':
            const nowDate = new Date()
            const endDate = new Date(task.endTime * 1000)
            const startDate = new Date(task.startTime * 1000)
            let replay = false
            if (nowDate > endDate) {
              if (task.activity.replayStatus == 'videoGenerated') {
                // 本站文件
                if (task.mediaSource === 'self') {
                  this.setSourceType({
                    sourceType: 'video',
                    taskId: task.id
                  })
                } else {
                  Toast('暂不支持此类型');
                }
                return;
              } else if (task.activity.replayStatus == 'ungenerated') {
                Toast('暂无回放');
                return
              } else {
                replay = true
              }
            }

            this.$router.push({
              name: 'live',
              query: {
                courseId: this.selectedPlanId,
                taskId: task.id,
                type: task.type,
                title: task.title,
                replay,
              }
            })
            break;
          default:
            Toast('暂不支持此类型');
        }
      },
    },
    filters: {
      liveStatusText(lesson) {
        const now = new Date().getTime();
        const startTimeStamp = new Date(lesson.startTime * 1000);
        const endTimeStamp = new Date(lesson.endTime * 1000);
        // 直播未开始
        if (now <= startTimeStamp) {
          return formatCompleteTime(startTimeStamp);
        }
        if (now > endTimeStamp) {
          if (lesson.activity.replayStatus === 'ungenerated') {
            return '已结束';
          }
          return '回放';
        }
        return '直播中';
      }
    }
  }
</script>
