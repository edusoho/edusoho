<template>
    <div class="iframe-box" style="width:100%;height:100%;">
        <iframe v-show="lab_url"
                class="iframe"
                :src="lab_url"
                style="width: 100%;height: 100%;padding-left: 5px"
                ref="terminals"
        ></iframe>
        <div style="width:80%;margin:0 0 0 -100px;position:relative;left:20%;top:50%;">
            <div class="text" style="text-aligin:center;color:#ffffff;font-size:18px;font-weight:700;">{{text}}</div>
            <el-progress :text-inside="true" :stroke-width="24" :percentage="percentage"></el-progress>
        </div>
    </div>
</template>
<script>
    import common from './common/mixins'
    export default {
        mixins:[common],
        data(){
            return {
                lab_url:'',
            }
        },
        created(){
            let _this = this;
            // 获取到信息后加载
            // this.$watch('cur_subsection_info',(val)=>{});
            if(_this.lab_id == null || _this.lab_id == undefined || _this.lab_id == ''){
                _this.getLabApply(100,200);
            }else{
                _this.getLabQuery(100,200);
            }
        }
    }
</script>