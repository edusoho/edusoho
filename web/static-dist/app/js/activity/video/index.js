webpackJsonp(["app/js/activity/video/index"],[
/* 0 */
/***/ (function(module, exports) {

	import VideoRecorder from './video-recorder';
	import VideoPlay from './video-play';
	
	var recorder = new VideoRecorder('#video-content');
	var videoplay = new VideoPlay(recorder);
	videoplay.play();

/***/ })
]);