<template>
    <div style="height:500px;">
        <split-pane v-on:resize="resize" :min-percent='20' :default-percent='30' split="vertical">
        <template slot="paneL">
            <div slot="left" class="left-box">
                <div class="top-bar">
                    <div  class="top-bar">
                        <span class="course-name">标题名称</span>
                    </div>
                </div>
                <div  class="box-container relative">
                    <div class="left-bar">
                        <div  class="left-bar">
                            <div class="menu-tabs">
                                <div class="item font-16" :class=" tab == 'document'?'active':'' " @click="switchTab('document')">实验手册</div>
                                <div class="item font-16" :class=" tab == 'dataset'?'active':''" @click="switchTab('dataset')">我的数据集</div>
                            </div>
                            <div class="menu-btns"></div>
                        </div>
                    </div>
                    <div class="box-content relative full-width">
                        <div  class="lab-document" >
                            <div class="steps-document" width="648" style="height: 100%;">
                                <div  class="step-content">
                                    <div class="body-box" ref="markdownDody" id="markdownDody">
                                        <div class="body-con document" v-show="tab=='document'">
                                            <p v-html="info.content"></p>
                                        </div>
                                        <div class="body-con dataSet" v-show="tab=='dataset'">
                                            <el-collapse v-model="activeNames">
                                                <el-collapse-item :title="item.name" v-for="item in bindInfo['datasets']" :key="item.id">
                                                    <ul>
                                                        <li style="cursor:pointer" @click="copyPath(item.mount_path,file.name)"  v-for="file in item.files" :key="file.id">{{file.name}}</li>
                                                    </ul>
                                                </el-collapse-item>
                                            </el-collapse>
                                            <input type="hidden" class="selectPath" value="">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>                

                    </div>
                </div>
            </div>
        </template>
        <template slot="paneR">
            <div slot="right">
                <div class="right-box">
                    <div class="env" >
                        <div v-show="isEnvShow">
                            <div class="title">
                                <p>请点击运行环境</p><br />
                                <p style="font-size:20px;">个人空间： <span style="color:green;">/home/ilab</span></p>
                            </div>
                            <div  class="env-items">
                                <div class="env-item" @click="goEnv()">
                                    <div class="icon">
                                        <i class="el-icon-s-platform" style="font-size: 70px;"></i>
                                    </div>
                                    <div class="env-title">环境名称</div>
                                </div>
                            </div>
                        </div>
                        <component :is="currentView" ></component>
                    </div>
                </div>
            </div>
        </template>
        </split-pane>
    </div>
</template>
<script>
    import Env from "./component/env.vue"

    import splitPane from 'vue-splitpane'
    export default{
        name:"view",
        components:{
            splitPane,
            Env
        },
        props:{
            info:Object,
            bindInfo:Object
        },
        data(){
            return {
                tab:"dataset",
                isEnvShow:false,
                currentView:'env',
            }
        },
        methods:{
            goEnv(){
                this.currentView = 'Env';
                this.isEnvShow = false
            },
            // 切换tab
            switchTab(tab){
                this.tab = tab;
            },
            // 复制路径
            copyPath(mount_path,name){
                let  path = mount_path + '/' + name;
                $(".selectPath").val(path);
                $(".selectPath").select();
                document.execCommand("Copy");
                Vue.prototype.$message({
                    message: '复制成功',
                    type: 'success'
                });
            }
        },
        mounted() {
        }
    }
</script>

<style lang="less" scoped>
    
</style>