module.exports = {
	presets: ["@vue/app","@babel/preset-env"],
	plugins: [
		[
			"import",
			{
				libraryName: "vant",
				libraryDirectory: "es",
				style: true
			}
		],
		[
			"component",
			{
				"libraryName": "element-ui",
				"styleLibraryName": "theme-chalk"
			}
    ],
    ["@babel/plugin-proposal-optional-chaining"]
	]
};
