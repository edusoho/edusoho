<template>
  <ant-config-provider>
    <div class="flex flex-col grow shrink-0 justify-start items-start self-stretch gap-20 bg-white min-h-800 pt-20 px-32 pb-0">
      <div class="flex w-full justify-between items-center">
        <span class="text-16 font-medium leading-24 text-black/[.88]">学员管理</span>
        <div v-if="['published', 'unpublished'].includes(classroomStatus)" class="flex items-center gap-20">
          <button class="bulk-import-btn"
                  data-toggle="modal"
                  data-target="#modal"
                  data-backdrop="static"
                  data-keyboard="false"
                  :data-url="`/importer/classroom-member/index?classroomId=${classroomId}`"
          >
            <ImportOutlined class="w-16 text-[--primary-color]"/>
            <span>批量导入</span>
          </button>
          <button class="add-students-btn"
                  data-toggle="modal"
                  data-target="#modal"
                  data-backdrop="static"
                  data-keyboard="false"
                  :data-url="`/classroom/${classroomId}/manage/student/create`"
          >
            <UserAddOutlined class="w-16 text-white"/>
            <span>添加学员</span>
          </button>
        </div>
      </div>
      <div class="flex flex-col w-full items-start gap-20">
        <div class="flex items-start gap-32 self-stretch border-0 border-b border-solid border-b-[#F0F2F5]">
          <div v-for="tab in tabs" class="flex px-0 py-12 justify-center items-center cursor-pointer" :class="tab.key === 'students' ? 'border-0 border-b-2 border-solid border-b-[--primary-color]' : ''">
            <span class="text-[#1E2226] text-14 font-normal leading-22" :class="tab.key === 'students' ? 'text-[--primary-color] font-semibold' : ''" @click="goto(tab.link)">{{ tab.text }}</span>
          </div>
        </div>
        <div class="flex flex-col items-start self-stretch gap-20">
          <div class="flex flex-col items-start self-stretch gap-20 p-20 rounded-6 bg-[#FAFAFA]">
            <div class="flex items-center gap-20 self-stretch">
            <span class="shrink-0">
              加入时间：<a-range-picker
              class="rounded-6 border border-solid border-[#DFE2E6]"
              @change="onDateChange"
              :value="formState.joinDate"
            />
            </span>
              <a-select
                allowClear
                placeholder="加入方式"
                :options="joinedChannelSelectOptions"
                v-model:value="formState.joinedChannel"
              />
              <a-select
                allowClear
                placeholder="学习有效期"
                :options="learnDeadlineSelectOptions"
                v-model:value="formState.learnDeadline"
              />
              <a-input
                class="h-32 rounded-6 border border-solid border-[#E5E6EB]"
                placeholder="请输入用户名/邮箱/手机号"
                v-model:value="formState.userKeyword"
              />
            </div>
            <div class="flex items-center gap-20 self-stretch">
              <button class="flex py-0 px-15 justify-center items-center rounded-6 border border-solid border-[--primary-color] bg-white h-32" @click="fetchStudents">
                <span class="text-[--primary-color] text-14 font-normal leading-22">搜索</span>
              </button>
              <button class="flex py-0 px-15 justify-center items-center rounded-6 border border-solid border-[#E5E6EB] bg-white shadow-[0_2px_0_0_rgba(0, 0, 0, 0.02)] h-32" @click="resetForm">
                <span class="text-[#37393D] text-14 font-normal leading-22">重置</span>
              </button>
              <div class="flex items-center gap-8 cursor-pointer" @click="onExport">
                <ExportOutlined class="text-16 text-[--primary-color]"/>
                <span class="text-[--primary-color] text-14 font-normal leading-22">导出搜索结果</span>
              </div>
            </div>
          </div>
          <div v-show="students.length > 0" class="flex items-start gap-20">
            <button class="student-operate-button" @click="onBatchRemove">批量移除</button>
            <button class="student-operate-button" @click="onBatchUpdateExpiryDate">批量修改有效期</button>
            <button class="student-operate-button" @click="onUpdateAllExpiryDate">全员修改有效期</button>
          </div>
        </div>
        <div class="flex flex-col items-start gap-16 w-full">
          <a-table
            class="w-full"
            :columns="table.columns"
            :data-source="students"
            :row-key="student => student.user.id"
            :pagination="false"
            :loading="table.loading"
            :row-selection="{selectedRowKeys: table.rowSelection.selectedRowKeys, onChange: table.rowSelection.onChange}"
          >
            <template #headerCell="{ column }">
              <template v-if="column.key === 'joinTime'">
                <div class="flex items-center self-stretch gap-4 whitespace-nowrap">
                  加入时间/学习有效期
                  <a-tooltip placement="top" title="取加入方式中学习有效期最长的生效和展示">
                    <InfoCircleOutlined class="w-16 text-[#919399]"/>
                  </a-tooltip>
                </div>
              </template>
            </template>
            <template #bodyCell="{ column, record }">
              <template v-if="column.key === 'user'">
                <div class="flex items-center gap-12 shrink-0">
                  <img :src="record.user.avatar.small" class="w-40 h-40 rounded-40 cursor-pointer" alt="">
                  <div class="flex flex-col">
                    <a-tooltip placement="top">
                      <template #title>{{ record.user.nickname }}</template>
                      <div @click="open(`/user/${record.user.uuid}`)" class="w-fit max-w-100 truncate text-14 text-[#1D2129] cursor-pointer hover:text-[--primary-color]">{{ record.user.nickname }}</div>
                    </a-tooltip>
                    <div class="w-100 truncate text-12 text-[#87898F]" v-if="record.remark">{{ record.remark }}</div>
                  </div>
                </div>
              </template>
              <template v-else-if="column.key === 'mobile'">
                <div class="flex flex-col">
                  <div class="flex">
                    <div class="flex gap-8 min-w-50">
                  <span class="text-[#37393D] text-14 font-normal leading-22">
                    {{ mobile(record.user.id, record.user.verifiedMobile) }}
                  </span>
                      <EyeOutlined v-show="openEyeVisible(record.user.id, record.user.verifiedMobile)" class="w-24 text-[#919399]"/>
                      <EyeInvisibleOutlined v-show="closeEyeVisible(record.user.id, record.user.verifiedMobile)" @click="showWholeMobile(record.user.id, record.user.encryptedMobile)" class="w-24 cursor-pointer text-[#919399]"/>
                    </div>
                  </div>
                </div>
                <div class="w-111 truncate text-12 text-[#87898F]">{{ record.joinedChannelText }}</div>
              </template>
              <template v-else-if="column.key === 'joinTime'">
                <div class="flex flex-col">
                  <div>{{ formatDate(record.createdTime, 'YYYY-MM-DD HH:mm') }}</div>
                  <div class="text-12 text-[#87898F]">{{ record.deadline == 0 ? '长期有效' : formatDate(record.deadline, 'YYYY-MM-DD HH:mm') }}</div>
                </div>
              </template>
              <template v-else-if="column.key === 'learnProgress'">
                <div class="flex gap-8 items-center">
                  <div class="flex flex-col justify-center items-start h-16 w-100 p-2 rounded-99 bg-[#F5F5F5]">
                    <div class="h-12 rounded-99" :style="`width: ${record.learningProgressPercent}px;`" style="background-image: linear-gradient(90deg, rgba(0, 194, 97, 0.4), rgb(0, 194, 97));"></div>
                  </div>
                  <span class="text-[#37393D] text-14 font-normal leading-22">{{ record.learningProgressPercent }}%</span>
                </div>
              </template>
              <template v-else-if="column.key === 'operation'">
                <div class="flex items-center gap-16">
                  <div v-if="isAdmin || isTeacher" class="text-[--primary-color] text-14 font-normal leading-22 cursor-pointer" data-toggle="modal" data-target="#modal" :data-url="`/classroom/${classroomId}/manage/member/deadline?userIds=${record.user.id}`">修改有效期</div>
                  <div v-else class="text-[#C0C0C2] text-14 font-normal leading-22 cursor-not-allowed">修改有效期</div>
                  <a-dropdown placement="bottomRight" trigger="['click']">
                    <span class="flex items-center cursor-pointer">
                      <EllipsisOutlined class="w-20 text-[--primary-color]"/>
                    </span>
                    <template #overlay>
                      <a-menu>
                        <a-menu-item v-if="record.user.canSendMessage" data-toggle="modal" data-target="#modal" :data-url="`/message/create/${record.user.id}`">
                          发私信
                        </a-menu-item>
                        <a-menu-item v-if="isAdmin" data-toggle="modal" data-target="#modal" :data-url="`/course_set/0/manage/course/0/students/${record.user.id}/show`">
                          查看资料
                        </a-menu-item>
                        <a-menu-item data-toggle="modal" data-target="#modal" :data-url="`/classroom/${classroomId}/manage/student/${record.user.id}/remark`">
                          备注
                        </a-menu-item>
                        <a-menu-item @click="removeStudent(record.user.id)">
                          移除
                        </a-menu-item>
                      </a-menu>
                    </template>
                  </a-dropdown>
                </div>
              </template>
            </template>
          </a-table>
          <div class="flex justify-between items-center self-stretch pb-28 px-8 w-full">
            <div class="">
              <a-checkbox
                :indeterminate="table.rowSelection.selectedRowKeys.length > 0 && !table.isSelectAll"
                :checked="table.isSelectAll"
                :disabled="students.length === 0"
                @change="table.onSelectAllChange">
                <span class="text-[#37393D] text-14 font-normal leading-22">全选</span>
              </a-checkbox>
              <span class="text-[#37393D] text-14 font-normal leading-22">选择 {{ table.rowSelection.selectedRowKeys.length }} 项</span>
            </div>
            <a-pagination
              show-quick-jumper
              show-size-changer
              :show-total="pagination.showTotal"
              :total="pagination.total"
              v-model="pagination.current"
              :disabled="pagination.total === 0"
              @change="pagination.onChange"
            />
          </div>
        </div>
      </div>
    </div>
  </ant-config-provider>
</template>

<script setup>
import {computed, reactive, ref} from 'vue';
import {message} from 'ant-design-vue';
import AntConfigProvider from '../../components/AntConfigProvider';
import {formatDate, getData, goto, open, trans} from '../../common';
import Api from 'vue3/api';
import {
  ImportOutlined,
  UserAddOutlined,
  InfoCircleOutlined,
  EyeInvisibleOutlined,
  EyeOutlined, EllipsisOutlined, ExportOutlined
} from '@ant-design/icons-vue';

const classroomId = getData('student-list-app', 'classroom-id');
const classroomStatus = getData('student-list-app', 'classroom-status');

const permissions = ref([]);
const fetchPermissions = async () => {
  permissions.value = await Api.me.getPermissions(['admin_v2', 'web']);
};
fetchPermissions();

const isAdmin = computed(() => {
  return permissions.value.includes('admin_v2');
});

const isTeacher = computed(() => {
  return permissions.value.includes('web');
});

const tabs = [
  {
    key: 'students',
    text: '正式学员',
    link: `/classroom/${classroomId}/manage/students`,
  },
  {
    key: 'auditor',
    text: '旁听生',
    link: `/classroom/${classroomId}/manage/auditor`,
  },
  {
    key: 'joinRecords',
    text: '加入记录',
    link: `/classroom/${classroomId}/manage/record/join`,
  },
  {
    key: 'exitRecords',
    text: '退出记录',
    link: `/classroom/${classroomId}/manage/record/exit`,
  },
];

const joinedChannelSelectOptions = [
  {
    value: 'buy_join',
    label: '购买加入',
  },
  {
    value: 'free_join',
    label: '免费加入',
  },
  {
    value: 'vip_join',
    label: '会员加入',
  },
  {
    value: 'import_join',
    label: '教师添加',
  },
];

const learnDeadlineSelectOptions = [
  {
    value: 'deadlineAfter',
    label: '有效期内',
  },
  {
    value: 'deadlineBefore',
    label: '有效期外',
  },
];

const formState = reactive({
  joinDate: undefined,
  joinDateGreaterThan: undefined,
  joinDateLessThan: undefined,
  joinedChannel: undefined,
  learnDeadline: undefined,
  userKeyword: undefined,
});

const resetForm = () => {
  formState.joinDate = undefined;
  formState.joinDateGreaterThan = undefined;
  formState.joinDateLessThan = undefined;
  formState.joinedChannel = undefined;
  formState.learnDeadline = undefined;
  formState.userKeyword = undefined;
  fetchStudents();
};

const onDateChange = (date, dateString) => {
  formState.joinDate = date;
  formState.joinDateGreaterThan = dateString[0] ? Date.parse(dateString[0] + ' 00:00:00') / 1000 : undefined;
  formState.joinDateLessThan = dateString[1] ? Date.parse(dateString[1] + ' 00:00:00') / 1000 + 86400 : undefined;
};

const table = {
  columns: [
    {
      key: 'user',
      title: '学员',
    },
    {
      key: 'mobile',
      title: '手机号/加入方式',
    },
    {
      key: 'joinTime',
      title: '加入时间/有效期',
    },
    {
      key: 'learnProgress',
      title: '学习进度',
    },
    {
      key: 'operation',
      title: '操作',
    },
  ],
  loading: false,
  rowSelection: reactive({
    hideDefaultSelections: true,
    onChange: (selectedRowKeys) => {
      table.rowSelection.selectedRowKeys = selectedRowKeys;
      table.isSelectAll = selectedRowKeys.length === students.value.length;
    },
    selectedRowKeys: [],
  }),
  isSelectAll: false,
  onSelectAllChange: () => {
    table.isSelectAll = !table.isSelectAll;
    if (table.isSelectAll) {
      table.rowSelection.selectedRowKeys = students.value.map(student => student.user.id);
    } else {
      table.rowSelection.selectedRowKeys = [];
    }
  },
};

const pagination = {
  current: 1,
  total: 0,
  pageSize: 10,
  showTotal: total => {
    return trans('question.bank.paper.pageTotal', {total});
  },
  onChange: (page, pageSize) => {
    pagination.current = page;
    pagination.pageSize = pageSize;
    fetchStudents();
  },
};

const students = ref([]);

const getFormParams = () => {
  const params = {
    startTimeGreaterThan: formState.joinDateGreaterThan,
    startTimeLessThan: formState.joinDateLessThan,
    joinedChannel: formState.joinedChannel,
    userKeyword: formState.userKeyword,
  };
  if (formState.learnDeadline) {
    params[formState.learnDeadline] = Math.round(Date.now() / 1000);
  }

  return params;
};

async function fetchStudents() {
  table.loading = true;
  const params = getFormParams();
  params.role = 'student';
  params.offset = (pagination.current - 1) * pagination.pageSize;
  params.limit = pagination.pageSize;
  const resp = await Api.classroomMember.search(classroomId, params);
  students.value = resp.data;
  pagination.total = resp.paging.total;
  table.loading = false;
}

fetchStudents();

const wholeMobiles = reactive({});

const mobile = computed(() => {
  return (userId, maskMobile) => {
    if (wholeMobiles[userId]) {
      return wholeMobiles[userId];
    }

    return maskMobile ? maskMobile : '-';
  };
});

const openEyeVisible = computed(() => {
  return (userId, mobile) => {
    if (mobile.length === 0) {
      return false;
    }

    return !!wholeMobiles[userId];
  };
});

const closeEyeVisible = computed(() => {
  return (userId, mobile) => {
    if (mobile.length === 0) {
      return false;
    }

    return !wholeMobiles[userId];
  };
});

const showWholeMobile = async (userId, encryptedMobile) => {
  const result = await Api.security.decryptMobile(encryptedMobile);
  wholeMobiles[userId] = result?.mobile;
};

let exporting = false;
const $modal = $('#modal');

const onExport = async () => {
  if (exporting) {
    return;
  }
  exporting = true;
  await exportData();
  exporting = false;
};

const exportData = async (start, fileName) => {
  const params = getFormParams();
  params.start = start || 0;
  if (fileName) {
    params.fileName = fileName;
  }
  const response = await Api.classroomMember.export(classroomId, params);
  if (response.status === 'getData') {
    await exportData(response.start, response.fileName);
  } else {
    location.href = `/classroom/${classroomId}/manage/student/export?role=student&fileName=${response.fileName}`;
  }
};

const onBatchUpdateExpiryDate = async () => {
  if (table.rowSelection.selectedRowKeys.length === 0) {
    message.error(trans('course.manage.student.add_expiry_day.select_tips'));
    return;
  }
  const response = await fetch(`/classroom/${classroomId}/manage/member/deadline?` + new URLSearchParams({userIds: table.rowSelection.selectedRowKeys}));
  const html = await response.text();
  $modal.html(html).modal('show');
};

const onUpdateAllExpiryDate = async () => {
  const response = await fetch(`/classroom/${classroomId}/manage/member/deadline?all=1`);
  const html = await response.text();
  $modal.html(html).modal('show');
};

const onBatchRemove = async () => {
  if (table.rowSelection.selectedRowKeys.length === 0) {
    message.error(trans('course.manage.student.batch_remove.select_tips'));
    return;
  }
  if (!confirm(trans('course.manage.students_delete_hint'))) {
    return;
  }
  const result = await Api.classroomMember.batchRemove(classroomId, table.rowSelection.selectedRowKeys);
  if (result.success) {
    message.success(trans('member.delete_success_hint'));
    table.rowSelection.selectedRowKeys = [];
    await fetchStudents();
  } else {
    message.error(`${trans('member.delete_fail_hint')}:${result.message}`);
  }
};

const removeStudent = async userId => {
  if (!confirm(trans('course.manage.student_delete_hint'))) {
    return;
  }
  const result = await Api.classroomMember.remove(classroomId, userId);
  if (result) {
    message.success(trans('member.delete_success_hint'));
    table.rowSelection.selectedRowKeys = table.rowSelection.selectedRowKeys.filter(key => key !== userId);
    await fetchStudents();
  } else {
    message.error(`${trans('member.delete_fail_hint')}:${result.message}`);
  }
};

</script>

<style lang="less">
.bulk-import-btn {
  display: flex;
  height: 32px;
  padding: 0 15px;
  justify-content: center;
  align-items: center;
  gap: 8px;
  border-radius: 6px;
  border: 1px solid var(--primary-color);
  background: #fff;

  span {
    color: var(--primary-color);
    font-size: 14px;
    font-style: normal;
    font-weight: 400;
    line-height: 22px;
  }
}

.add-students-btn {
  display: flex;
  height: 32px;
  padding: 0 15px;
  justify-content: center;
  align-items: center;
  gap: 8px;
  border-radius: 6px;
  border: 1px solid var(--primary-color);
  background: var(--primary-color);

  span {
    color: #FFF;
    font-size: 14px;
    font-style: normal;
    font-weight: 400;
    line-height: 22px;
  }
}

.student-operate-button {
  display: flex;
  height: 32px;
  padding: 0 15px;
  justify-content: center;
  align-items: center;
  border-radius: 6px;
  border: 1px solid #E5E6EB;
  background: #FFF;
  box-shadow: 0 2px 0 0 rgba(0, 0, 0, 0.02);

  color: #37393D;
  font-size: 14px;
  font-style: normal;
  font-weight: 400;
  line-height: 22px;
}

.ant-message {
  left: 50%;
  transform: translateX(-50%)
}
</style>
