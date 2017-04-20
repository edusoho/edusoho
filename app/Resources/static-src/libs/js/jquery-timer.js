/*global define:false */
/*
 * =======================
 * jQuery Timer Plugin
 * =======================
 * Start/Stop/Resume a time in any HTML element
 */
/* eslint-disable */
(function(root, factory) {
    if (typeof define === 'function' && define.amd) {
        // define(['jquery'], factory);
        factory($);
    } else {
        factory($);
    }
}(this, function($) {
    if (!Array.prototype.forEach) {

        Array.prototype.forEach = function forEach(callback, thisArg) {

            var T, k;

            if (this == null) {
                throw new TypeError("this is null or not defined");
            }
            var O = Object(this);
            var len = O.length >>> 0;
            if (typeof callback !== "function") {
                throw new TypeError(callback + " is not a function");
            }
            if (arguments.length > 1) {
                T = thisArg;
            }
            k = 0;

            while (k < len) {

                var kValue;
                if (k in O) {

                    kValue = O[k];
                    callback.call(T, kValue, k, O);
                }
                k++;
            }
        };
    }

    if (!Function.prototype.bind) {
        Function.prototype.bind = function(oThis) {
            if (typeof this !== "function") {
                throw new TypeError("Function.prototype.bind - what is trying to be bound is not callable");
            }
            var aArgs = Array.prototype.slice.call(arguments, 1),
                fToBind = this,
                fNOP = function() {},
                fBound = function() {
                    return fToBind.apply(this instanceof fNOP && oThis ? this : oThis,
                        aArgs.concat(Array.prototype.slice.call(arguments)));
                };
            fNOP.prototype = this.prototype;
            fBound.prototype = new fNOP();
            return fBound;
        };
    }
    // PRIVATE
    var options = {
            seconds: 0,                                 // default seconds value to start timer from
            editable: false,                            // this will let users make changes to the time
            restart: false,                             // this will enable stop or continue after a timer callback
            duration: null,                             // duration to run callback after
            // callback to run after elapsed duration
            callback: function() {
                alert('Time up!');
            },
            startTimer: function() {},
            pauseTimer: function() {},
            resumeTimer: function() {},
            resetTimer: function() {},
            removeTimer: function() {},
            repeat: false,                              // this will repeat callback every n times duration is elapsed
            countdown: false,                           // if true, this will render the timer as a countdown if duration > 0
            format: null,                               // this sets the format in which the time will be printed
            updateFrequency: 1000,                      // How often should timer display update (default 500ms)
            state: 'running'
        },
        display = 'html',   // to be used as $el.html in case of div and $el.val in case of input type text
        // Constants for various states of the timer
        TIMER_STOPPED = 'stopped',
        TIMER_RUNNING = 'running',
        TIMER_PAUSED = 'paused';

    /**
     * Common function to start or resume a timer interval
     */
    function startTimerInterval(timer) {
        var element = timer.element;
        $(element).data('intr', setInterval(incrementSeconds.bind(timer), timer.options.updateFrequency));
        $(element).data('isTimerRunning', true);
    }

    /**
     * Common function to stop timer interval
     */
    function stopTimerInterval(timer) {
        clearInterval($(timer.element).data('intr'));
        $(timer.element).data('isTimerRunning', false);
    }

    /**
     * Increment total seconds by subtracting startTime from the current unix timestamp in seconds
     * and call render to display pretty time
     */
    function incrementSeconds() {
        $(this.element).data('totalSeconds', getUnixSeconds() - $(this.element).data('startTime'));
        render(this);

        // Check if totalSeconds is equal to duration if any
        if ($(this.element).data('duration') &&
            $(this.element).data('totalSeconds') % $(this.element).data('duration') === 0) {

            // If 'repeat' is not requested then disable the duration
            if (!this.options.repeat) {
                $(this.element).data('duration', null);
                this.options.duration = null;
            }

            // If this is a countdown, then end it as duration has completed
            if (this.options.countdown) {
                stopTimerInterval(this);
                this.options.countdown = false;
                $(this.element).data('state', TIMER_STOPPED);
            }

            // Run the default callback
            this.options.callback();
        }
    }

    /**
     * Render pretty time
     */
    function render(timer) {
        var element = timer.element,
            sec = $(element).data('totalSeconds');


        if (timer.options.countdown && ($(element).data('duration') > 0)) {
            sec = $(element).data('duration') - $(element).data('totalSeconds');
        }

        $(element)[display](secondsToTime(sec, timer));
        $(element).data('seconds', sec);
    }

    /**
     * Method to make timer field editable
     * This method hard binds focus & blur events to pause & resume
     * and recognizes built-in pretty time (for eg 12 sec OR 3:34 min)
     * It won't recognize user created formats.
     * Users may not always want this hard bound. In such a case,
     * do not use the editable property. Instead bind custom functions
     * to blur and focus.
     */
    function makeEditable(timer) {
        var element = timer.element;
        $(element).on('focus', function() {
            pauseTimer(timer);
        });

        $(element).on('blur', function() {
            // eg. 12 sec 3:34 min 12:30 min
            var val = $(element)[display](), valArr;

            if (val.indexOf('sec') > 0) {
                // sec
                $(element).data('totalSeconds', Number(val.replace(/\ssec/g, '')));
            } else if (val.indexOf('min') > 0) {
                // min
                val = val.replace(/\smin/g, '');
                valArr = val.split(':');
                $(element).data('totalSeconds', Number(valArr[0] * 60) + Number(valArr[1]));
            } else if (val.match(/\d{1,2}:\d{2}:\d{2}/)) {
                // hrs
                valArr = val.split(':');
                $(element).data('totalSeconds', Number(valArr[0] * 3600) + Number(valArr[1] * 60) + Number(valArr[2]));
            }

            resumeTimer(timer);
        });
    }

    /**
     * Get the current unix timestamp in seconds
     * @return {Number} [unix timestamp in seconds]
     */
    function getUnixSeconds() {
        return Math.round(new Date().getTime() / 1000);
    }

    /**
     * Convert a number of seconds into an object of hours, minutes and seconds
     * @param  {Number} sec [Number of seconds]
     * @return {Object}     [An object with hours, minutes and seconds representation of the given seconds]
     */
    function sec2TimeObj(sec) {
        var hours = 0, minutes = Math.floor(sec / 60), seconds;

        // Hours
        if (sec >= 3600) {
            hours = Math.floor(sec / 3600);
        }

        // Minutes
        if (sec >= 3600) {
            minutes = Math.floor(sec % 3600 / 60);
        }
        // Prepend 0 to minutes under 10
        if (minutes < 10 && hours > 0) {
            minutes = '0' + minutes;
        }
        // Seconds
        seconds = sec % 60;
        // Prepend 0 to seconds under 10
        if (seconds < 10 && (minutes > 0 || hours > 0)) {
            seconds = '0' + seconds;
        }

        return {
            hours: hours,
            minutes: minutes,
            seconds: seconds
        };
    }

    /**
     * Convert the given seconds to an object made up of hours, minutes and seconds and return a pretty display
     * @param  {Number} sec [Second to display as pretty time]
     * @return {String}     [Pretty time]
     */
    function secondsToTime(sec, timer) {
        var time = '',
            timeObj = sec2TimeObj(sec);

        if (timer.options.format) {
            var formatDef = [
                {identifier: '%h', value: timeObj.hours, pad: false},
                {identifier: '%m', value: timeObj.minutes, pad: false},
                {identifier: '%s', value: timeObj.seconds, pad: false},
                {identifier: '%H', value: parseInt(timeObj.hours), pad: true},
                {identifier: '%M', value: parseInt(timeObj.minutes), pad: true},
                {identifier: '%S', value: parseInt(timeObj.seconds), pad: true}
            ];
            time = timer.options.format;

            formatDef.forEach(function(format) {
                time = time.replace(
                    new RegExp(format.identifier.replace(/([.*+?^=!:${}()|\[\]\/\\])/g, '\\$1'), 'g'),
                    (format.pad) ? ((format.value < 10) ? '0' + format.value : format.value) : format.value
                );
            });
        } else {
            if (timeObj.hours) {
                time = timeObj.hours + ':' + timeObj.minutes + ':' + timeObj.seconds;
            } else {
                if (timeObj.minutes) {
                    time = timeObj.minutes + ':' + timeObj.seconds + ' min';
                } else {
                    time = timeObj.seconds + ' sec';
                }
            }
        }
        return time;
    }

    /**
     * Convert a string time like 5m30s to seconds
     * If a number (eg 300) is provided, then return as is
     * @param  {Number|String} time [The human time to convert to seconds]
     * @return {Number}      [Number of seconds]
     */
    function timeToSeconds(time) {
        // In case the passed arg is a number, then use that as number of seconds
        if (!isNaN(Number(time))) {
            return time;
        }

        var hMatch = time.match(/\d{1,2}h/),
            mMatch = time.match(/\d{1,2}m/),
            sMatch = time.match(/\d{1,2}s/),
            seconds = 0;

        time = time.toLowerCase();

        // @todo: throw an error in case of faulty time value like 5m61s or 61m
        if (hMatch) {
            seconds += Number(hMatch[0].replace('h', '')) * 3600;
        }

        if (mMatch) {
            seconds += Number(mMatch[0].replace('m', '')) * 60;
        }

        if (sMatch) {
            seconds += Number(sMatch[0].replace('s', ''));
        }

        return seconds;
    }

    // TIMER INTERFACE
    function startTimer(timer) {
        var element = timer.element;
        if (!$(element).data('isTimerRunning')) {
            render(timer);
            startTimerInterval(timer);
            $(element).data('state', TIMER_RUNNING);
            timer.options.startTimer.bind(timer).call();
        }
    }

    function pauseTimer(timer) {
        var element = timer.element;
        if ($(element).data('isTimerRunning')) {
            stopTimerInterval(timer);
            $(element).data('state', TIMER_PAUSED);
            timer.options.pauseTimer.bind(timer).call();
        }
    }

    function resumeTimer(timer) {
        var element = timer.element;
        if (!$(element).data('isTimerRunning')) {
            $(element).data('startTime', getUnixSeconds() - $(element).data('totalSeconds'));
            startTimerInterval(timer);
            $(element).data('state', TIMER_RUNNING);
            timer.options.resumeTimer.bind(timer).call();
        }
    }

    function resetTimer(timer) {
        var element = timer.element;
        $(element).data('startTime', 0);
        $(element).data('totalSeconds', 0);
        $(element).data('seconds', 0);
        $(element).data('state', TIMER_STOPPED);
        $(element).data('duration', timer.options.duration);
        timer.options.resetTimer.bind(timer).call();
    }

    function removeTimer(timer) {
        var element = timer.element;
        stopTimerInterval(timer);
        timer.options.removeTimer.bind(timer).call();
        $(element).data('plugin_' + pluginName, null);
        $(element).data('seconds', null);
        $(element).data('state', null);
        $(element)[display]('');
    }

    // TIMER PROTOTYPE
    var Timer = function(element, userOptions) {
        var elementType;

        this.options = options = $.extend(this.options, options, userOptions);
        this.element = element;

        // Setup total seconds from options.seconds (if any)
        $(element).data('totalSeconds', options.seconds);

        // Setup start time if seconds were provided
        $(element).data('startTime', getUnixSeconds() - $(element).data('totalSeconds'));

        $(element).data('seconds', $(element).data('totalSeconds'));
        $(element).data('state', TIMER_STOPPED);

        // Check if this is a input/textarea element or not
        elementType = $(element).prop('tagName').toLowerCase();
        if (elementType === 'input' || elementType === 'textarea') {
            display = 'val';
        }

        if (this.options.duration) {
            $(element).data('duration', timeToSeconds(this.options.duration));
            this.options.duration = timeToSeconds(this.options.duration);
        }

        if (this.options.editable) {
            makeEditable(this);
        }

    };

    /**
     * Initialize the plugin with public methods
     */
    Timer.prototype = {
        start: function() {
            startTimer(this);
        },

        pause: function() {
            pauseTimer(this);
        },

        resume: function() {
            resumeTimer(this);
        },

        reset: function() {
            resetTimer(this);
        },

        remove: function() {
            removeTimer(this);
        }
    };

    // INITIALIZE THE PLUGIN
    var pluginName = 'timer';
    $.fn[pluginName] = function(options) {
        options = options || 'start';

        return this.each(function() {
            /**
             * Allow the plugin to be initialized on an element only once
             * This way we can call the plugin's internal function
             * without having to reinitialize the plugin all over again.
             */
            if (!($.data(this, 'plugin_' + pluginName) instanceof Timer)) {

                /**
                 * Create a new data attribute on the element to hold the plugin name
                 * This way we can know which plugin(s) is/are initialized on the element later
                 */
                $.data(this, 'plugin_' + pluginName, new Timer(this, options));

            }

            /**
             * Use the instance of this plugin derived from the data attribute for this element
             * to conduct whatever action requested as a string parameter.
             */
            var instance = $.data(this, 'plugin_' + pluginName);

            /**
             * Provision for calling a function from this plugin
             * without initializing it all over again
             */
            if (typeof options === 'string') {
                if (typeof instance[options] === 'function') {
                    /*
                    Pass in 'instance' to provide for the value of 'this' in the called function
                    */
                    instance[options].call(instance);
                }
            }

            /**
             * Allow passing custom options object
             */
            if (typeof options === 'object') {
                if (instance.options.state === TIMER_RUNNING) {
                    instance.start.call(instance);
                } else {
                    render(instance);
                }
            }
        });
    };

}));
/* eslint-enable */