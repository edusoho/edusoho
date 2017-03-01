/* 默认值,可在settings中重写
  const settings = {
    devtool: 'cheap-module-eval-source-map', // 可设置为 'source-map'，方便错误排查 
    openThemesModule: [],  //默认扫描web/themes下所有主题，但可以指定监听具体的主题，如 ['themes/default','themes/default-b']
    openPluginsModule: [],  //默认扫描plugins下所有插件，但可以指定监听具体的插件，如 ['plugins/CrmPlugin','plugins/VipPlugin']
    openModule: ['lib','app','admin','plugin','copy','theme'], // 可以选择监听哪几种资源文件
  }
*/

const settings = {
  imglimit: 1024,
  fontlimit: 1024,
  // openModule: ['lib','app','admin','plugin','copy','theme'], 
};

export default settings;
