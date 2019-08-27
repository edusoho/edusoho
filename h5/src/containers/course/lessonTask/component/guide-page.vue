<template>
   <div class="guide" v-show="isFristVisited" @click="isFristVisited=false">
      <div class="guide__text">左右切换滑动</div>
      <div class="guide__gesture">
        <img src="static/images/leftslide.png"/>
        <img src="static/images/rightslide.png"/>
      </div>
    </div>
</template>

<script>
import { mapState} from "vuex";
export default {
    name:'guide-page',
    data(){
        return{
            isFristVisited:false,//是否第一次进行考试任务
        }
    },
    computed: {
        ...mapState({
            user:state => state.user
        }),
    },
    created() {
        this.setVisited();
    },
    methods:{
        //把当前标记存入localstorge，用于记录是否是第一次访问，控制显示引导页
        setVisited(){
            let localvisitedName=`${this.user.id}-task-visited`;
            let isVisited=localStorage.getItem(localvisitedName);

            if(!localStorage.getItem(localvisitedName)){
                this.isFristVisited=true;
                localStorage.setItem(localvisitedName, true);
            }
        },
    }
}
</script>
