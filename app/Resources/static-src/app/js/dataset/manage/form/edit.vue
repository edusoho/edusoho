<template>
    <div>
        <el-row :gutter="20">
            <div class="course-manage-subltitle cd-mb40 ml0">数据集</div>
            <el-col :span="12">
                <el-form :model="form" :rules="formRule"
                        ref="baseInfoForm"
                        label-position="right"
                        label-width="150px">
                    <el-form-item label="数据集标题">
                        <el-col>
                            <el-input ref="title" v-model="form.title" auto-complete="off"></el-input>
                        </el-col>
                    </el-form-item>
                    <el-form-item label="描述信息">
                        <el-col>
                            <el-input ref="subtitle" v-model="form.subtitle" type="textarea" rows="3"></el-input>
                        </el-col>
                    </el-form-item>
                    <el-form-item label="挂载目录列表">
                        <el-col>
                            <el-table
                                :data="tableData"
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
                    {{ccc}}
                    <el-form-item label="挂载树">
                        <el-col>
                            <div class="tree" style="max-height:500px;overflow:auto;" >
                                <el-tree
                                    ref="tree"
                                    :props="props"
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
    import * as validation from 'common/element-validation';

    export default {
        name: "dataset-info",
        props: {
            info:Array,
            treeData:Array,
            dirGetPath:String,
        },
        methods: {
            // 确认选择树 (有bug、先保留)
            confirmDirectory(){
                let nodes = this.$refs.tree.getCheckedNodes();
                nodes.forEach(node=>{
                    let p = true;
                    this.paths.forEach(v=>{
                        if(v.path == node.path){
                            p = false;
                            return;
                        }
                    })
                    if(p){
                        this.tableData.push({name:node.name,path:node.path});
                        this.paths.push({path:node.path});
                    }
                })
            },
            // 删除table
            deleteDirectory(info){
                let nodes = this.$refs.tree.getCheckedNodes();
                nodes.forEach(node=>{
                    if(info.row.path == node.path){
                        this.$refs.tree.setChecked(node.path,false);
                        this.tableData.splice(info.$index, 1);
                        this.paths.splice(info.$index,1);
                    }
                })
                this.tableData.splice(info.$index, 1);
                this.paths.splice(info.$index,1);
            },
            loadNode(node, resolve) {
                console.log(node.level);
                // if (node.level === 0 && this.first===false) {
                //     this.first = true;
                //     return resolve(this.treeData.body);
                // }
                if(node.level  >= 1){
                    // 加载子目录
                    let data = {}
                    if(node.data.path !==undefined && node.data.path !== ''){
                        data.path = node.data.path;
                    }
                    this.$axios.get(this.dirGetPath, {params:data}, {emulateJSON: true}).then(res=>{
                        resolve(res.data.body);
                    })
                }else if(node.level === 0 && this.first===false){
                    this.first = true;
                    return resolve(this.treeData.body);
                }else{
                    return resolve(this.treeData.body);
                }
            }
        },
        data() {
            return {
                first:false,
                props: {
                    label: 'name',
                    children: 'zones',
                    isLeaf: 'leaf'
                },
                tableData: [],
                paths:[],
                form: {
                    title: "标题",
                    subtitle: "副标题",
                },
                formRule: {
                    title: [
                        {
                            required: true,
                            message: "请输入标题",
                            trigger: 'blur'
                        },
                        {
                            min: 2,
                            message: Translator.trans('validate.length_min.message', {'length': 2}),
                            trigger: 'blur',
                        },
                        {
                            max: 30,
                            message: Translator.trans('validate.length_max.message', {'length': 30}),
                            trigger: 'blur',
                        },
                        {validator: validation.trim, trigger: 'blur'},
                        {validator: validation.course_title, trigger: 'blur'}
                    ],
                    subtitle: [
                        {
                            max: 30,
                            message: Translator.trans('validate.length_max.message', {'length': 30}),
                            trigger: 'blur',
                        },
                    ]
                },
            };
        },
        mounted() {
            // 暂时先这样、没时间研究了
            // setTimeout(() => {
            //     this.confirmDirectory();
            // },1000);
        }
    }
</script>