import webpackDevMiddleware from 'webpack-dev-middleware';

const QUIET_MODE = false;

export default function (compiler, publicPath) {
  const webpackDevMiddlewareOptions = {
    publicPath,
    quiet: QUIET_MODE,
    noInfo: QUIET_MODE,
    stats: {
      colors: true,
      chunks: false,
      chunkModules: false,
    },
    hot: true,
    lazy: false,
    historyApiFallback: true,
  };

  return webpackDevMiddleware(compiler, webpackDevMiddlewareOptions);
}
