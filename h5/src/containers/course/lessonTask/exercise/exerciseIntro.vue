<template>
   <div>
      <e-loading v-if="isLoading"></e-loading>
      <div class="intro-body" v-if="exercise">
        <van-panel class="panel intro-panel" title="练习名称">
            <div class="intro-panel__content intro-panel__content--title">{{ exercise.name }}</div>
        </van-panel>
        <van-panel class="panel intro-panel" title="题目数量">
            <div class="intro-panel__content" >
                共计 {{exercise.itemCount}} 题
            </div>
        </van-panel>
      </div>
      <div class="intro-footer" v-if="exercise">
        <van-button class="intro-footer__btn" type="primary" v-if="hasResult" @click="showResult">查看结果</van-button>
        <van-button class="intro-footer__btn" type="primary" v-else @click="startExercise()">开始答题</van-button>
      </div>
  </div>
</template>

<script>
import Api from '@/api';
import { mapState,mapActions } from 'vuex';
import { Dialog,Toast } from "vant";
import exerciseMixin from '@/mixins/lessonTask/exercise.js';
export default {
    name: "exercise-intro",
    mixins: [exerciseMixin],
    data(){
        return{
            courseId:null,
            taskId:null,
            exercise:null
        }
    },
    computed: {
        hasResult() {
            const latestExerciseResult=this.exercise.latestExerciseResult;
            return latestExerciseResult ? true: false;
        },
        ...mapState({
            isLoading: state => state.isLoading,
            user:state => state.user
        }),
    },
    created(){
        this.getInfo()
    },
    beforeRouteEnter(to, from, next) {
        document.getElementById("app").style.background="#f6f6f6"
        next()
    },
    beforeRouteLeave(to, from, next)  {
        document.getElementById("app").style.background=""
        next()
    },
     methods:{
        ...mapActions('course', [
                'handExercisedo',
            ]),
        getInfo(){
            this.courseId = this.$route.query.courseId;
            this.taskId = this.$route.query.taskId;
            Api.getExerciseIntro({
                query: {
                    courseId: this.courseId,
                    taskId:this.taskId
                }
            }).then(res => {
                this.exercise=res.exercise;

                this.interruption()
            });
        },
        //异常中断
        interruption(){
            this.canDoing(this.exercise.latestExerciseResult,this.user.id).then(()=>{
                this.startExercise();
            }).catch(({answer})=>{
                this.submitExercise(answer)
            })
        },
        //跳转到结果页
        showResult() {
            this.$router.push({
                name: 'exerciseResult',
                query: {
                    exerciseId: this.exercise.id,
                    exerciseResultId:this.exercise.latestExerciseResult.id,
                    courseId: this.courseId,
                    taskId:this.taskId
                }
            })
        },
        //开始作业
        startExercise(){
            this.$router.push({
                name: 'exerciseDo',
                query: {
                    targetId: this.taskId,
                    exerciseId:this.exercise.id,
                    courseId:this.courseId
                },
                params:{
                    KeepDoing:true
                }
            })
        },
        //交练习
        submitExercise(answer){
            let datas={
                answer,
                exerciseId:this.exercise.id,
                userId:this.user.id,
                exerciseResultId:this.exercise.latestExerciseResult.id
            }
            //提交练习+跳转到结果页
            this.handExercisedo(datas).then(res=>{
                this.showResult()
            }).catch((err)=>{
                Toast.fail(err.message);
            });
        }
    }
}
</script>

<style>

</style>