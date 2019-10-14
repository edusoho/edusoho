export default [
  {
    // 获取当前登录用户代理商绑定信息
    name: 'getAgencyBindRelation',
    url: '/plugins/drp/me/agency_bind_relations',
    method: 'GET'
  },
  {
    name: 'hasDrpPluginInstalled',
    url: '/settings/hasPluginInstalled?pluginCodes=Drp',
    disableLoading: true
  },
  {
    name: 'getDrpSetting',
    url: '/plugins/drp/drp_setting',
    disableLoading: true
  }
];
