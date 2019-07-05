<template>
  <div v-if="lesson.length>0">
    <div class="lesson-directory"  v-for="(lessonItem,lessonIndex) in lesson" :key="lessonIndex" >
      <div class="lesson-title" :id="lessonItem.tasks[lessonItem.index].id" :class="{'zb-ks' : doubleLine(lessonItem.tasks[lessonItem.index].type)}" @click="lessonCellClick(lessonItem.tasks[lessonItem.index])">
        <div class="lesson-title-r">
          <div class="lesson-title-des">

            <!-- 非直播考试-->
              <div class="bl l22" v-if="!doubleLine(lessonItem.tasks[lessonItem.index].type)"> 
                <!-- <span class="tryLes">试听</span> -->
                <span class="text-overflow ks" :class="{ 'lessonactive': (currentTask==lessonItem.tasks[lessonItem.index].id) }">
                  <i class="iconfont" :class="iconfont(lessonItem.tasks[lessonItem.index].type)"></i>
                  {{ Number(lessonItem.tasks[lessonItem.index].isOptional) ? '选修 ' : '课时' }}{{ Number(lessonItem.tasks[lessonItem.index].isOptional) ? ' ' : `${lessonItem.tasks[lessonItem.index].number}:${lessonItem.title}`}}
                </span>
              </div>

              <!-- 直播或者考试-->
              <div class="bl" v-if="doubleLine(lessonItem.tasks[lessonItem.index].type)">
                <!-- <span class="tryLes">试听</span> -->
                <div class="il-bl">
                  <span class="bl text-overflow ks" :class="{ 'lessonactive': (currentTask==lessonItem.tasks[lessonItem.index].id) }">
                    <i class="iconfont" :class="iconfont(lessonItem.tasks[lessonItem.index].type)"></i>
                    {{ Number(lessonItem.tasks[lessonItem.index].isOptional) ? '选修 ' : '课时' }}{{ Number(lessonItem.tasks[lessonItem.index].isOptional) ? ' ' : `${lessonItem.tasks[lessonItem.index].number}:${lessonItem.title}`}}
                  </span>
                  <span class="bl zbtime">
                    <span class="end" :class="[liveClass(lessonItem.tasks[lessonItem.index]), 'live-text', 'ml5']">{{ lessonItem.tasks[lessonItem.index]| filterTaskTime}}</span>
                  </span>
                </div>
              </div>

          </div>
        </div>

        <!-- 时长 -->
        <div class="lesson-title-l">
          <span v-if="lessonItem.tasks[lessonItem.index].type!='live'">{{ lessonItem.tasks[lessonItem.index] | filterTaskTime }}</span>
          <i class="iconfont" :class="studyStatus(lessonItem.tasks[lessonItem.index])"></i>
        </div>
      </div>
      
      <!-- task任务 -->
      <div class="lesson-items" v-if="lessonItem.tasks.length>1" >
        <div class="litem" v-for="(taskItem,taskIndex) in lessonItem.tasks" :id="taskItem.id"  :key="taskIndex" v-if="taskItem.mode!='lesson'" @click="lessonCellClick(taskItem)">
          <div class="litem-r text-overflow" :class="{ 'lessonactive': (currentTask==Number(taskItem.id)) }">
            <!-- <span class="tryLes">试听</span> -->
            <i class="iconfont" :class="iconfont(taskItem.type)"></i>{{ Number(taskItem.isOptional) ? '选修 ' : '课时' }}{{ Number(taskItem.isOptional) ? ' ' : `${taskItem.number}:${lessonItem.title}`}}
          </div>
          <div class="litem-l clearfix">
             <span >{{ taskItem | filterTaskTime }}</span>
              <i class="iconfont"  :class="studyStatus(taskItem)"></i>
          </div>
        </div>
      </div>
    </div>


  </div>
  <div v-else class="noneItem">
    <img src="static/images/none.png" class="notask"/>
    <p>暂时还没有课时哦...</p>
  </div>
</template>
<script>
import redirectMixin from '@/mixins/saveRedirect';
import { mapState, mapMutations } from 'vuex';
import * as types from '@/store/mutation-types';
import { Dialog, Toast } from 'vant';
export default {
  name: "lessonDirectory",
  mixins: [redirectMixin],
  props:{
        lesson:{
            type:Array,
            default:()=>[]
        },
        errorMsg: {
          type: String,
          default: '',
        },
        taskId:{
          type:Number,
          default:-1
        }
    },
  data(){
    return{
      currentTask:''
    }
  },
  watch:{
    taskId:{
      handler: 'getTaskId',
      immediate: true
    }
  },
  computed:{
    ...mapState('course', {
        details: state => state.details,
        joinStatus: state => state.joinStatus,
        selectedPlanId: state => state.selectedPlanId,
      }),
  },
  methods:{
      ...mapMutations('course', {
        setSourceType: types.SET_SOURCETYPE
      }),
    //获取lesson位置
    getTaskId(){
       this.currentTask=this.taskId
    },
    //直播双行显示判断
    doubleLine(type){
      let isDouble
      if(type==='live'){
        isDouble=true
      }else{
        isDouble=false
      }
      return isDouble
    },
    lessonCellClick (task) {
        // 课程错误和未发布状态，不允许学习任务
        if (this.errorMsg || task.status === 'create') {
          this.$emit('showDialog');
          return;
        }

        this.currentTask=task.id;
        const details = this.details;

        !details.allowAnonymousPreview && this.$router.push({
          name: 'login',
          query: {
            redirect: this.redirect,
          }
        });
         this.joinStatus ? this.showTypeDetail(task) : '';
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
    //任务图标(缺少下载)
    iconfont(type){
      switch(type){
        case 'audio':
        return 'icon-yinpin';
        case 'doc':
        return 'icon-wendang';
        case 'exercise':
        return 'icon-lianxi';
        case 'flash':
        return 'icon-flash';
        case 'homework':
        return 'icon-zuoye';
        case 'live':
        return 'icon-zhibo';
        case 'ppt':
        return 'icon-ppt';
        case 'discuss':
        return 'icon-taolun';
        case 'testpaper':
        return 'icon-kaoshi';
        case 'text':
        return 'icon-tuwen';
        case 'video':
        return 'icon-shipin';
        default:
          return '';
      }
    },
    //学习状态
    studyStatus(task){
      if(task.lock){
        return 'icon-suo';
      }
     if(task.result!=null){
       switch(task.result.status){
         case 'finish':
           return 'icon-yiwanchengliang';
           break;
        case 'start':
           return 'icon-weiwancheng';
           break;
        default:
          return ''
       }
     }else{
       return 'icon-weixuexi';
     }
    },
    //直播状态样式
    liveClass(lesson ) {
          const now = new Date().getTime();
          const startTimeStamp = new Date(lesson.startTime * 1000);
          const endTimeStamp = new Date(lesson.endTime * 1000);
          if (now > endTimeStamp) {
            if (lesson.activity.replayStatus === 'ungenerated') {
              return 'end';
            }
            return 'back';
          }
          return '';
     }
  }
};
</script>