<template>
    <div class="swiper-directory ">
        <van-swipe :show-indicators="false" :loop="false" :touchable="true" :width="265" :initial-swipe="slideIndex" @change="changeChapter">
            <van-swipe-item v-for="(items, index) in item" :key="index" >
                <div class="chapter nochapter" v-if="items.isExist==0" :class="[current===index] ? 'swiper-directory-active':''">
                    <i class="iconfont icon-wuzhangjieliang"></i>
                    无章节
                </div>
                <div class="chapter haschapter" v-if="items.isExist==1" :class="[current===index ? 'swiper-directory-active' : '']">
                    <p class="chapter-title text-overflow" >第{{items.number}}{{hasChapter?'章':'节'}}：{{items.title}}</p>
                    <p class="chapter-des text-overflow">{{hasChapter? `节${items.unitNum}`:''}} 课时({{items.lessonNum}}) 学习任务({{items.tasksNum}})</p>
                </div>
            </van-swipe-item>
        </van-swipe>
    </div>
</template>
<script>
export default {
    name:'swiperDirectory',
    props:{
        item:{
            type:Array,
            default:()=>[]
        },
        slideIndex:{
            type:Number,
            default:0
        },
        hasChapter:{
            type:Boolean,
            default:true
        }
    },
    data(){
        return{
            current:this.slideIndex
        }
    },
    methods:{
         changeChapter(index) {
            this.current=index
            this.$emit('changeChapter', index)
        }
    }
}
</script>