import appConfig  from '../config';
import webpack from 'webpack';
import path from 'path';
import ExtractTextPlugin from 'extract-text-webpack-plugin';
import ChunkManifestPlugin from 'chunk-manifest-webpack-plugin';
import FixModuleIdAndChunkIdPlugin from 'fix-moduleid-and-chunkid-plugin';

const __DEBUG__ = appConfig.__DEBUG__;
const __DEV__ = appConfig.__DEV__;
const bundleEntry = appConfig.bundleEntry;
const libEntry = appConfig.libEntry;
const nodeModulesDir = appConfig.nodeModulesDir;

let entries = libEntry;
let webpackPlugins = [];

const bundleEntryKeys = Object.keys(bundleEntry);
bundleEntryKeys.forEach((key) => {
  Object.assign(entries, bundleEntry[key]);
  webpackPlugins.push(new webpack.optimize.CommonsChunkPlugin({
    name: key,
    filename: '[name].js',
    chunks: Object.keys(bundleEntry[key]),
    minChunks: 2,
  }));
});

let config = {
  entry: entries,
  output: Object.assign(appConfig.output, {
    filename: '[name].js',
  }),
  resolve: {
    root: appConfig.assetsSrcDirs,
    alias: {
      libs: appConfig.libsDir,
      common: appConfig.commonDir,
      nodeModulesDir: nodeModulesDir,
    },
    extensions: ['', '.js', '.jsx'],
  },
  module: {
    noParse: [],

    // preLoaders: [{
    //     test: /\.js$/,
    //     loader: 'eslint',
    //     include: appConfig.assetsDirs
    // }],

    loaders: [{
      test: /\.js[x]?$/,
      loader: 'babel',
      query: {
        cacheDirectory: true
      },
      include: appConfig.assetsDirs
    }, {
      test: /\.json$/,
      loader: 'json'
    }, {
      test: /\.css$/,
      loader: ExtractTextPlugin.extract('style', 'css'),
    }, {
      test: /\.less$/,
      loader: ExtractTextPlugin.extract('style', 'css!less'),
    }, {
      test: /\.(png|jpe?g|gif)$/,
      loader: 'url?limit=8192&name=/img/[hash:8].[ext]'
    }, {
      test: /\.(woff|woff2|eot|ttf|svg)(\?v=\d+\.\d+\.\d+)?$/,
      loader: 'file?name=fonts/[name].[ext]'
    }]
  },
  plugins: [
    new ExtractTextPlugin('[name].css', {
      allChunks: true,
    }),
    new webpack.NoErrorsPlugin(),
    new webpack.optimize.DedupePlugin(),
    new webpack.DefinePlugin({
      __DEBUG__: __DEBUG__,
      __DEV__: __DEV__,
    }),
    new ChunkManifestPlugin({
      filename: "chunk-manifest.json",
      manifestVariable: "webpackManifest"
    }),

    new FixModuleIdAndChunkIdPlugin(),
  ].concat(webpackPlugins),
};

appConfig.noParseDeps.forEach(dep => {
  // add the specific deps to noParse and alias
  const depPath = path.resolve(nodeModulesDir, dep);
  config.resolve.alias[dep.split(path.sep)[0].replace('.', '-')] = depPath;
  config.module.noParse.push(depPath);
  
});

config.module.loaders.push({
  test: new RegExp(`(${appConfig.noParseDeps.join('|')})$`),
  loader: 'imports?define=>false&module=>false&exports=>false&this=>window',
});

if (__DEV__) {
  config.devtool = 'cheap-module-eval-source-map';
} else {
  if (!__DEBUG__) { //debug mode not compress
    config.plugins.push(new webpack.optimize.UglifyJsPlugin({
      compress: {
        warnings: false,
        screw_ie8: true,
        sequences: true,
        dead_code: true,
        drop_debugger: true,
        comparisons: true,
        conditionals: true,
        evaluate: true,
        booleans: true,
        loops: true,
        unused: true,
        hoist_funs: true,
        if_return: true,
        join_vars: true,
        cascade: true,
        drop_console: true,
      },
      output: {
        comments: false,
      },
    }));
  }
}

export default config;
