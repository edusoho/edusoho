function BrowserType() {
    var ua = navigator.userAgent.toLowerCase();
    var testUa = function(regexp) {return regexp.test(ua)};
    var testVs = function(regexp) {
        return ua.match(regexp)
            .toString()
            .replace(/[^0-9|_.]/g, "")
            .replace(/_/g, ".");
    };


    var system = "unknow";
    if (testUa(/windows|win32|win64|wow32|wow64/g)) {
        system = "windows";
    } else if (testUa(/macintosh|macintel/g)) {
        system = "macos";
    } else if (testUa(/x11/g)) {
        system = "linux";
    } else if (testUa(/android|adr/g)) {
        system = "android";
    } else if (testUa(/ios|iphone|ipad|ipod|iwatch/g)) {
        system = "ios";
    }

    var systemVs = "unknow";
    if (system === "windows") {
        if (testUa(/windows nt 5.0|windows 2000/g)) {
            systemVs = "2000";
        } else if (testUa(/windows nt 5.1|windows xp/g)) {
            systemVs = "xp";
        } else if (testUa(/windows nt 5.2|windows 2003/g)) {
            systemVs = "2003";
        } else if (testUa(/windows nt 6.0|windows vista/g)) {
            systemVs = "vista";
        } else if (testUa(/windows nt 6.1|windows 7/g)) {
            systemVs = "7";
        } else if (testUa(/windows nt 6.2|windows 8/g)) {
            systemVs = "8";
        } else if (testUa(/windows nt 6.3|windows 8.1/g)) {
            systemVs = "8.1";
        } else if (testUa(/windows nt 10.0|windows 10/g)) {
            systemVs = "10";
        }
    } else if (system === "macos") {
        systemVs = testVs(/os x [\d._]+/g);
    } else if (system === "android") {
        systemVs = testVs(/android [\d._]+/g);
    } else if (system === "ios") {
        systemVs = testVs(/os [\d._]+/g);
    }

    var platform = "unknow";
    if (system === "windows" || system === "macos" || system === "linux") {
        platform = "desktop";
    } else if (system === "android" || system === "ios" || testUa(/mobile/g)) {
        platform = "mobile";
    }

    var engine = "unknow";
    var supporter = "unknow";
    if (testUa(/applewebkit/g)) {
        engine = "webkit";
        if (testUa(/edge/g)) {
            supporter = "edge";
        } else if (testUa(/opr/g)) {
            supporter = "opera";
        } else if (testUa(/chrome/g)) {
            supporter = "chrome";
        } else if (testUa(/safari/g)) {
            supporter = "safari";
        }
    } else if (testUa(/gecko/g) && testUa(/firefox/g)) {
        engine = "gecko";
        supporter = "firefox";
    } else if (testUa(/presto/g)) {
        engine = "presto";
        supporter = "opera";
    } else if (testUa(/trident|compatible|msie/g)) {
        engine = "trident";
        supporter = "iexplore";
    }

    var engineVs = "unknow";
    if (engine === "webkit") {
        engineVs = testVs(/applewebkit\/[\d._]+/g);
    } else if (engine === "gecko") {
        engineVs = testVs(/gecko\/[\d._]+/g);
    } else if (engine === "presto") {
        engineVs = testVs(/presto\/[\d._]+/g);
    } else if (engine === "trident") {
        engineVs = testVs(/trident\/[\d._]+/g);
    }

    var supporterVs = "unknow";
    if (supporter === "chrome") {
        supporterVs = testVs(/chrome\/[\d._]+/g);
    } else if (supporter === "safari") {
        supporterVs = testVs(/version\/[\d._]+/g);
    } else if (supporter === "firefox") {
        supporterVs = testVs(/firefox\/[\d._]+/g);
    } else if (supporter === "opera") {
        supporterVs = testVs(/opr\/[\d._]+/g);
    } else if (supporter === "iexplore") {
        supporterVs = testVs(/(msie [\d._]+)|(rv:[\d._]+)/g);
    } else if (supporter === "edge") {
        supporterVs = testVs(/edge\/[\d._]+/g);
    }

    var shell = "none";
    var shellVs = "unknow";
    if (testUa(/micromessenger/g)) {
        shell = "wechat";
        shellVs = testVs(/micromessenger\/[\d._]+/g);
    } else if (testUa(/qqbrowser/g)) {
        shell = "qq";
        shellVs = testVs(/qqbrowser\/[\d._]+/g);
    } else if (testUa(/ucbrowser/g)) {
        shell = "uc";
        shellVs = testVs(/ucbrowser\/[\d._]+/g);
    } else if (testUa(/qihu 360se/g)) {
        shell = "360";
    } else if (testUa(/2345explorer/g)) {
        shell = "2345";
        shellVs = testVs(/2345explorer\/[\d._]+/g);
    } else if (testUa(/metasr/g)) {
        shell = "sougou";
    } else if (testUa(/lbbrowser/g)) {
        shell = "liebao";
    } else if (testUa(/maxthon/g)) {
        shell = "maxthon";
        shellVs = testVs(/maxthon\/[\d._]+/g);
    }


    var result = {
        engine: engine,
        engineVs: engineVs,
        platform: platform,
        supporter: supporter,
        supporterVs: supporterVs,
        system: system,
        systemVs: systemVs,
    };

    if (shell !== "none") {
        result.shell = shell;
        result.shellVs = shellVs;
    }

    return result;
}

const checkBrowserCompatibility = function() {
    var result = {
        ok: true,
        message: "",
    };

    var browser = BrowserType();

    let hint = result.message = '请下载安装使用最新版 <a href="https://edtech.edusoho.net/software/chrome-win64.exe" target="_blank">谷歌</a>、<a href="https://browser.360.cn/se/" target="_blank">360</a> 或 <a href="https://ie.sogou.com/" target="_blank">搜狗</a> 浏览器。';

    if (browser.platform !== "desktop") {
        result.ok = false;
        result.message = "请在电脑端浏览器中打开！";
    } else if (browser.supporter === "iexplore") {
        result.ok = false;
        result.message = '不支持当前浏览器，' + hint;
    } else if (browser.shell && browser.shell === 'qq') {
        result.ok = false;
        result.message = '不支持当前浏览器，' + hint;
    } else if (browser.supporter === "chrome" && parseInt(browser.supporterVs) < 69) {
        result.ok = false;
        result.message = '当前浏览器版本过低，' + hint;
    }

    return result;
};

export {
    checkBrowserCompatibility,
};