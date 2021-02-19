<template>
    <div>
        <el-row :gutter="20">
            <div class="course-manage-subltitle cd-mb40 ml0">数据集</div>
            <el-col :span="12">
                <el-form :model="form" :rules="formRule"
                        ref="datasetedit"
                        label-position="right"
                        label-width="150px">
                    <el-form-item label="数据集标题" prop="title">
                        <el-col>
                            <el-input ref="title" v-model="form.title" auto-complete="off"></el-input>
                        </el-col>
                    </el-form-item>
                    <el-form-item label="描述信息" prop="remark">
                        <el-col>
                            <el-input ref="remark" v-model="form.remark" type="textarea" rows="3"></el-input>
                        </el-col>
                    </el-form-item>
                    <el-form-item label="挂载目录列表" prop="tableData">
                        <el-col>
                            <el-table
                                :data="form.tableData"
                                stripe>
                                <el-table-column prop="name" label="名称"></el-table-column>
                                <el-table-column prop="path" label="路径"></el-table-column>
                                <el-table-column prop="action" label="操作">
                                    <template slot-scope="scope">
                                        <el-button
                                        size="mini"
                                        @click="deleteDirectory(scope)">删除</el-button>
                                    </template>
                                </el-table-column>
                            </el-table>
                        </el-col>
                    </el-form-item>
                </el-form>
            </el-col>
            <el-col :span="12">
                <el-form>
                    <el-form-item :label="treeName">
                        <el-col>
                            <div class="tree" style="max-height:500px;overflow:auto;" >
                                <el-tree
                                    ref="tree"
                                    :props="defaultprop"
                                    :load="loadNode"
                                    node-key="path"
                                    lazy
                                    :check-strictly="true"
                                    show-checkbox>
                                </el-tree>
                            </div>
                        </el-col>
                    </el-form-item>
                    <el-button size="mini" round @click="confirmDirectory()">确认</el-button>
                </el-form>
            </el-col>
        </el-row>
        <el-button size="mini" type="primary" round @click="submit()">提交</el-button>
    </div>

</template>

<script>

    export default {
        name: "dataset-info",
        props: {
            info:Array,
            treeData:Array,
            dirGetPath:String,
            submitPath:String,
        },
        data() {
            return {
                treeName:"挂载树",
                defaultprop: {
                    label: 'name',
                    children: 'zones',
                    isLeaf: 'leaf'
                },
                form: {
                    title: "标题",
                    remark: "副标题",
                    tableData: [],
                },
                formRule: {
                    title: [
                        {
                            required: true,
                            message: "请输入标题",
                            trigger: 'blur'
                        },
                    ],
                    remark: [
                        {
                            required: true,
                            message: "请输入描述信息",
                            trigger: 'blur',
                        },
                    ],
                    tableData:[
                       {
                            required: true,
                            message: "请选择目录",
                            trigger: 'blur',
                        },  
                    ]
                },
            };
        },
        methods: {
            // 确认选择树 (有bug、先保留)
            confirmDirectory(){
                let nodes = this.$refs.tree.getCheckedNodes();
                nodes.forEach(node=>{
                    let p = true;
                    this.form.tableData.forEach(v=>{
                        if(v.path == node.path){
                            p = false;
                            return;
                        }
                    })
                    if(p){
                        this.form.tableData.push({name:node.name,path:node.path});
                    }
                })
            },
            // 删除table
            deleteDirectory(info){
                let nodes = this.$refs.tree.getCheckedNodes();
                nodes.forEach(node=>{
                    if(info.row.path == node.path){
                        this.$refs.tree.setChecked(node.path,false);
                        this.form.tableData.splice(info.$index, 1);
                    }
                })
                this.form.tableData.splice(info.$index, 1);
            },
            loadNode(node, resolve) {
                console.log("level:"+node.level);
                if(node.level  >= 1){
                    // 加载子目录
                    let data = {}
                    if(node.data.path !==undefined && node.data.path !== ''){
                        data.path = node.data.path;
                    }
                    this.$axios.get(this.dirGetPath, {params:data}, {emulateJSON: true}).then(res=>{
                        resolve(res.data.body);
                        this.checkNode();
                    })
                }else{
                    resolve(this.treeData.body);
                }
            },
            // 遍历选中
            checkNode(){
                console.log("遍历选中");
                this.form.tableData.forEach((v)=>{
                    this.$refs.tree.setChecked(v.path,true);
                })
            },
            submit(){
                console.log("提交");
                let data = {
                    title:this.form.title,
                    remark:this.form.remark,
                    paths:[]
                }
                this.form.tableData.forEach(v=>{
                    data.paths.push(v.path);
                })
                console.log(data);
                this.$refs.datasetedit.validate((valid, invalidFields) => {
                    if (valid) {
                        // 验证是否选择数据集
                        console.log(this.submitPath);
                        this.$axios.post(this.submitPath,data).then(res=>{
                            cd.message({type: 'success', message: "提交成功"});
                        })
                    }
                });
            }
        },
        mounted() {
            this.form.tableData = this.info.files;
            this.form.title = this.info.name;
            this.form.remark = this.info.remark;
            // setTimeout(() => {
            //     this.treeName = "呱呱";
            // },500);
            this.checkNode();
        }
    }
</script>