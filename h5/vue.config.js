const path = require('path');
const UglifyJsPlugin = require('uglifyjs-webpack-plugin');
const merge = require('webpack-merge');

function resolve(dir) {
	return path.join(__dirname, dir);
}
let page = {};
let publicPath="./";
let bgImgPath="/";
let projectname = process.argv[4]; // 获取入口文件
if (process.env.NODE_ENV === 'production') {
	projectname = process.argv[5]
	bgImgPath=publicPath=projectname==='h5' ? "/h5/" : "/h5/admin/";
}
const chunks = {
	dafault: ['chunk-vendors', 'chunk-common', projectname],
	prodChunks: ['chunk-libs', 'chunk-commons', 'chunk-vant', 'chunk-elementUI', 'runtime', projectname]
}
page[projectname]={
		entry: `src/${projectname}/main.js`, // page 的入口
		template: `src/public/${projectname==='h5'?'index.html':'admin.html'}`, // 模板来源
		filename: "index.html", // 在 dist/index.html 的输出r
		// 当使用 title 选项时，template 中的 title 标签需要是 <title><%= htmlWebpackPlugin.options.title %></title>
		title: "Index Page",
		// 在这个页面中包含的块，默认情况下会包含,提取出来的通用 chunk 和 vendor chunk。
		//chunks: ['chunk-commons','chunk-vant','chunk-libs','chunk-common', 'h5']
		chunks: (process.env.NODE_ENV !== 'development') ? chunks.prodChunks : chunks.dafault
}

module.exports = {
	publicPath: publicPath, // 官方要求修改路径在这里做更改，默认是根目录下，可以自行配置
	outputDir: `dist${projectname==='h5' ? '' : '/'+projectname }`,//标识是打包哪个文件
	assetsDir:'static',
  lintOnSave:process.env.NODE_ENV === 'production'? false : true,
 //lintOnSave:false,
  css: {
          loaderOptions: {
              scss: {
                  data: `$baseUrl: "${bgImgPath}";`
              }
          }
   },
	//默认情况下，生成的静态资源在它们的文件名中包含了 hash 以便更好的控制缓存。如果你无法使用 Vue CLI 生成的 index HTML，你可以通过将这个选项设为 false 来关闭文件名哈希。
	filenameHashing: true,
	pages: page,
	productionSourceMap: false, // 生产环境 sourceMap
	devServer: {
		open: true, // 项目构建成功之后，自动弹出页面
		host: "0.0.0.0", // 主机名，也可以127.0.0.0 || 做真机测试时候0.0.0.0
		port: 8081, // 端口号，默认8080
		https: false, // 协议
		hotOnly: false, // 没啥效果，热模块，webpack已经做好了
		overlay: {
			warnings: false,
			errors: true
		},
		proxy: {
			'/api': {
				// target: 'https://www.easy-mock.com/mock/5b1742522de86c43cc2dc73a/edusoho',
				// target: 'http://zyc.st.edusoho.cn',
        //target: 'http://wr.st.edusoho.cn',
        //target:'http://gdy.st.edusoho.cn',
        //target:'https://www.iyamusic.com',
				target: 'http://devtest.edusoho.cn',
				changeOrigin: true,
				secure: false,
			}
		},
	},
	configureWebpack: config => {
		if (process.env.NODE_ENV === 'production') {
			config.plugins.push(
				new UglifyJsPlugin({
					uglifyOptions: {
						compress: {
							drop_console: true,
							drop_debugger: true
						}
					},
					cache: true, // 启用文件缓存
					parallel: true // 使用多进程并行运行来提高构建速度
					// sourceMap: false // 映射错误信息到模块
				})
			);
		}
		else {
			// 为开发环境修改配置...
    }
		config.resolve.extensions = ['.js', '.vue', '.json']
	},
	chainWebpack: config => {
		config.performance.set('hints',false);
		config.plugins
			.delete('prefetch-h5')
			.delete('preload-h5')
			.delete('prefetch-admin')
			.delete('preload-admin');
		//set第一个参数：设置的别名，第二个参数：设置的路径
		config.resolve.alias
			.set("@", resolve("./src/h5"))
			.set("&", resolve("./src"))
			.set("admin", resolve("./src/admin"))
			.set('vue$', 'vue/dist/vue.esm.js');
		//设置iconfont的文件位置
		config.module
			.rule('fonts')
			.test(/\.(woff2?|eot|ttf|otf)(\?.*)?$/i)
			.use('url-loader')
			.loader('url-loader')
			.tap(options =>
				merge(options, {
					limit: 10000,
					name: 'static/fonts/[name].[hash:8].[ext]'
				})
			);
    config.module
          .rule('images')
          .use('url-loader')
          .tap(options => {
            return merge(options, {
              publicPath: publicPath
            })
          })
		config
			.when(process.env.NODE_ENV !== 'development',
				config => {
					config
						.plugin('ScriptExtHtmlWebpackPlugin')
						.after('html')
						.use('script-ext-html-webpack-plugin', [{
							// `runtime` must same as runtimeChunk name. default is `runtime`
							inline: /runtime\..*\.js$/
						}])
						.end();
					config
						.optimization.splitChunks({
							chunks: 'all',
							cacheGroups: {
								libs: {
									name: 'chunk-libs',
									test: /[\\/]node_modules[\\/]/,
									priority: 10,
									chunks: 'initial' // only package third parties that are initially dependent
								},
								elementUI: {
									name: 'chunk-elementUI', // split elementUI into a single package
									priority: 20, // the weight needs to be larger than libs and app or it will be packaged into libs or app
									test: /[\\/]node_modules[\\/]_?element-ui(.*)/ // in order to adapt to cnpm
								},
								vant: {
									name: 'chunk-vant', // split elementUI into a single package
									priority: 20, // the weight needs to be larger than libs and app or it will be packaged into libs or app
									test: /[\\/]node_modules[\\/]_?vant(.*)/ // in order to adapt to cnpm
								},
								commons: {
									name: 'chunk-commons',
									test: resolve('src/h5/containers/components'), // can customize your rules
									minChunks: 3, //  minimum common number
									priority: 5,
									reuseExistingChunk: true
								}
							}
						});
					config.optimization.runtimeChunk('single');
				});
	}
}
