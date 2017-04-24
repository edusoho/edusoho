/*!
 * ====================================================
 * Kity Formula Parser - v1.0.0 - 2014-07-30
 * https://github.com/HanCong03/kityformula-editor
 * GitHub: https://github.com/kitygraph/kityformula-editor.git 
 * Copyright (c) 2014 Baidu Kity Group; Licensed MIT
 * ====================================================
 */

(function () {
var _p = {
    r: function(index) {
        if (_p[index].inited) {
            return _p[index].value;
        }
        if (typeof _p[index].value === "function") {
            var module = {
                exports: {}
            }, returnValue = _p[index].value(null, module.exports, module);
            _p[index].inited = true;
            _p[index].value = returnValue;
            if (returnValue !== undefined) {
                return returnValue;
            } else {
                for (var key in module.exports) {
                    if (module.exports.hasOwnProperty(key)) {
                        _p[index].inited = true;
                        _p[index].value = module.exports;
                        return module.exports;
                    }
                }
            }
        } else {
            _p[index].inited = true;
            return _p[index].value;
        }
    }
};

/*!
 * 装配器
 */
/* jshint forin: false */
/* global kf */
//TODO 重构generateExpression函数
/* 由于有一个大函数，临时把单个函数内的最大语句行数调整一下， 留待以后重构 */
/* jshint maxstatements: 500 */
_p[0] = {
    value: function() {
        var CONSTRUCT_MAPPING = {}, CURSOR_CHAR = "";
        /* ---------------------------------- Assembly 对象 */
        function Assembly(formula) {
            this.formula = formula;
        }
        Assembly.prototype.generateBy = function(data) {
            var tree = data.tree, objTree = {}, selectInfo = {}, mapping = {};
            if (typeof tree === "string") {
                //TODO return值统一
                throw new Error("Unhandled error");
            } else {
                this.formula.appendExpression(generateExpression(tree, deepCopy(tree), objTree, mapping, selectInfo));
                return {
                    select: selectInfo,
                    parsedTree: tree,
                    tree: objTree,
                    mapping: mapping
                };
            }
        };
        Assembly.prototype.regenerateBy = function(data) {
            this.formula.clearExpressions();
            return this.generateBy(data);
        };
        /**
     * 根据提供的树信息生成表达式
     * @param tree 中间格式的解析树
     * @return {kf.Expression} 生成的表达式
     */
        function generateExpression(originTree, tree, objTree, mapping, selectInfo) {
            var currentOperand = null, exp = null, // 记录光标位置
            cursorLocation = [], operand = tree.operand || [], constructor = null, ConstructorProxy;
            objTree.operand = [];
            // 文本表达式已经不需要再处理了
            if (tree.name.indexOf("text") === -1) {
                // 处理操作数
                for (var i = 0, len = operand.length; i < len; i++) {
                    currentOperand = operand[i];
                    //TODO 光标定位， 配合编辑器， 后期应该考虑是否有更佳的方案来实现
                    if (currentOperand === CURSOR_CHAR) {
                        cursorLocation.push(i);
                        if (!selectInfo.hasOwnProperty("startOffset")) {
                            // 字符串中的开始偏移是需要修正的
                            selectInfo.startOffset = i;
                        }
                        selectInfo.endOffset = i;
                        if (tree.attr && tree.attr.id) {
                            selectInfo.groupId = tree.attr.id;
                        }
                        continue;
                    }
                    if (!currentOperand) {
                        operand[i] = createObject("empty");
                        objTree.operand.push(operand[i]);
                    } else if (typeof currentOperand === "string") {
                        // 括号表达式不能对前2个参数做处理， 这两个参数是代表括号类型
                        if (tree.name === "brackets" && i < 2) {
                            operand[i] = currentOperand;
                        } else if (tree.name === "function" && i === 0) {
                            operand[i] = currentOperand;
                        } else {
                            operand[i] = createObject("text", currentOperand);
                        }
                        objTree.operand.push(operand[i]);
                    } else {
                        objTree.operand.push({});
                        operand[i] = generateExpression(originTree.operand[i], currentOperand, objTree.operand[objTree.operand.length - 1], mapping, selectInfo);
                    }
                }
                // 包含有选区时， 需要修正一下偏移
                if (cursorLocation.length === 2) {
                    selectInfo.endOffset -= 1;
                }
                while (i = cursorLocation.length) {
                    i = cursorLocation[i - 1];
                    operand.splice(i, 1);
                    cursorLocation.length--;
                    originTree.operand.splice(i, 1);
                }
            }
            constructor = getConstructor(tree.name);
            if (!constructor) {
                throw new Error("operator type error: not found " + tree.operator);
            }
            ConstructorProxy = function() {};
            ConstructorProxy.prototype = constructor.prototype;
            exp = new ConstructorProxy();
            constructor.apply(exp, operand);
            objTree.func = exp;
            // 调用配置函数
            for (var fn in tree.callFn) {
                if (!tree.callFn.hasOwnProperty(fn) || !exp[fn]) {
                    continue;
                }
                exp[fn].apply(exp, tree.callFn[fn]);
            }
            if (tree.attr) {
                if (tree.attr.id) {
                    mapping[tree.attr.id] = {
                        objGroup: exp,
                        strGroup: originTree
                    };
                }
                if (tree.attr["data-root"]) {
                    mapping.root = {
                        objGroup: exp,
                        strGroup: originTree
                    };
                }
                exp.setAttr(tree.attr);
            }
            return exp;
        }
        function createObject(type, value) {
            switch (type) {
              case "empty":
                return new kf.EmptyExpression();

              case "text":
                return new kf.TextExpression(value);
            }
        }
        /**
     * 根据操作符获取对应的构造器
     */
        function getConstructor(name) {
            return CONSTRUCT_MAPPING[name] || kf[name.replace(/^[a-z]/i, function(match) {
                return match.toUpperCase();
            }).replace(/-([a-z])/gi, function(match, char) {
                return char.toUpperCase();
            }) + "Expression"];
        }
        function deepCopy(source) {
            var target = {};
            if ({}.toString.call(source) === "[object Array]") {
                target = [];
                for (var i = 0, len = source.length; i < len; i++) {
                    target[i] = doCopy(source[i]);
                }
            } else {
                for (var key in source) {
                    if (!source.hasOwnProperty(key)) {
                        continue;
                    }
                    target[key] = doCopy(source[key]);
                }
            }
            return target;
        }
        function doCopy(source) {
            if (!source) {
                return source;
            }
            if (typeof source !== "object") {
                return source;
            }
            return deepCopy(source);
        }
        return Assembly;
    }
};

/**
 * latex实现工具包
 */
_p[1] = {
    value: function(require) {
        return {
            toRPNExpression: _p.r(2),
            generateTree: _p.r(3)
        };
    }
};

_p[2] = {
    value: function(require) {
        var Utils = _p.r(4);
        function rpn(units) {
            var signStack = [], currentUnit = null;
            // 先处理函数
            units = processFunction(units);
            while (currentUnit = units.shift()) {
                // 移除brackets中外层包裹的combination节点
                if (currentUnit.name === "combination" && currentUnit.operand.length === 1 && currentUnit.operand[0].name === "brackets") {
                    currentUnit = currentUnit.operand[0];
                }
                if (Utils.isArray(currentUnit)) {
                    signStack.push(rpn(currentUnit));
                    continue;
                }
                signStack.push(currentUnit);
            }
            // 要处理brackets被附加的包裹元素
            return signStack;
        }
        /**
     * “latex函数”处理器
     * @param units 单元组
     * @returns {Array} 处理过后的单元组
     */
        function processFunction(units) {
            var processed = [], currentUnit = null;
            while ((currentUnit = units.pop()) !== undefined) {
                if (currentUnit && typeof currentUnit === "object" && (currentUnit.sign === false || currentUnit.name === "function")) {
                    // 预先处理不可作为独立符号的函数
                    var tt = currentUnit.handler(currentUnit, [], processed.reverse());
                    processed.unshift(tt);
                    processed.reverse();
                } else {
                    processed.push(currentUnit);
                }
            }
            return processed.reverse();
        }
        return rpn;
    }
};

/**
 * 从单元组构建树
 */
_p[3] = {
    value: function(require) {
        var mergeHandler = _p.r(13), Utils = _p.r(4);
        function generateTree(units) {
            var currentUnit = null, tree = [];
            for (var i = 0, len = units.length; i < len; i++) {
                if (Utils.isArray(units[i])) {
                    units[i] = generateTree(units[i]);
                }
            }
            while (currentUnit = units.shift()) {
                if (typeof currentUnit === "object" && currentUnit.handler) {
                    // 后操作数
                    tree.push(currentUnit.handler(currentUnit, tree, units));
                } else {
                    tree.push(currentUnit);
                }
            }
            return mergeHandler(tree);
        }
        return generateTree;
    }
};

/**
 * 通用工具包
 */
_p[4] = {
    value: function(require) {
        var OPERATOR_LIST = _p.r(7), FUNCTION_LIST = _p.r(6), FUNCTION_HANDLER = _p.r(15), Utils = {
            // 根据输入的latex字符串， 检测出该字符串所对应的kf的类型
            getLatexType: function(str) {
                str = str.replace(/^\\/, "");
                // 操作符
                if (OPERATOR_LIST[str]) {
                    return "operator";
                }
                if (FUNCTION_LIST[str]) {
                    return "function";
                }
                return "text";
            },
            isArray: function(obj) {
                return obj && Object.prototype.toString.call(obj) === "[object Array]";
            },
            getDefine: function(str) {
                return Utils.extend({}, OPERATOR_LIST[str.replace("\\", "")]);
            },
            getFuncDefine: function(str) {
                return {
                    name: "function",
                    params: str.replace(/^\\/, ""),
                    handler: FUNCTION_HANDLER
                };
            },
            getBracketsDefine: function(leftBrackets, rightBrackets) {
                return Utils.extend({
                    params: [ leftBrackets, rightBrackets ]
                }, OPERATOR_LIST.brackets);
            },
            extend: function(target, sources) {
                for (var key in sources) {
                    if (sources.hasOwnProperty(key)) {
                        target[key] = sources[key];
                    }
                }
                return target;
            }
        };
        return Utils;
    }
};

/**
 * 定义括号类型， 对于属于括号类型的符号或表达式， 则可以应用brackets函数处理
 */
_p[5] = {
    value: function() {
        var t = true;
        return {
            ".": t,
            "{": t,
            "}": t,
            "[": t,
            "]": t,
            "(": t,
            ")": t,
            "|": t
        };
    }
};

/**
 * 函数列表
 */
_p[6] = {
    value: function() {
        return {
            sin: 1,
            cos: 1,
            arccos: 1,
            cosh: 1,
            det: 1,
            inf: 1,
            limsup: 1,
            Pr: 1,
            tan: 1,
            arcsin: 1,
            cot: 1,
            dim: 1,
            ker: 1,
            ln: 1,
            sec: 1,
            tanh: 1,
            arctan: 1,
            coth: 1,
            exp: 1,
            lg: 1,
            log: 1,
            arg: 1,
            csc: 1,
            gcd: 1,
            lim: 1,
            max: 1,
            sinh: 1,
            deg: 1,
            hom: 1,
            liminf: 1,
            min: 1,
            sup: 1
        };
    }
};

/**
 * 操作符列表
 */
_p[7] = {
    value: function(require) {
        var scriptHandler = _p.r(22), TYPE = _p.r(11);
        return {
            "^": {
                name: "superscript",
                type: TYPE.OP,
                handler: scriptHandler
            },
            _: {
                name: "subscript",
                type: TYPE.OP,
                handler: scriptHandler
            },
            frac: {
                name: "fraction",
                type: TYPE.FN,
                sign: false,
                handler: _p.r(14)
            },
            sqrt: {
                name: "radical",
                type: TYPE.FN,
                sign: false,
                handler: _p.r(23)
            },
            sum: {
                name: "summation",
                type: TYPE.FN,
                traversal: "rtl",
                handler: _p.r(24)
            },
            "int": {
                name: "integration",
                type: TYPE.FN,
                traversal: "rtl",
                handler: _p.r(16)
            },
            brackets: {
                name: "brackets",
                type: TYPE.FN,
                handler: _p.r(12)
            },
            mathcal: {
                name: "mathcal",
                type: TYPE.FN,
                sign: false,
                handler: _p.r(19)
            },
            mathfrak: {
                name: "mathfrak",
                type: TYPE.FN,
                sign: false,
                handler: _p.r(20)
            },
            mathbb: {
                name: "mathbb",
                type: TYPE.FN,
                sign: false,
                handler: _p.r(18)
            },
            mathrm: {
                name: "mathrm",
                type: TYPE.FN,
                sign: false,
                handler: _p.r(21)
            }
        };
    }
};

/**
 * 预处理器列表
 */
_p[8] = {
    value: function(require) {
        return {
            // 积分预处理器
            "int": _p.r(26),
            // 引号预处理
            quot: _p.r(27)
        };
    }
};

/*!
 * 逆解析对照表
 */
_p[9] = {
    value: function(require) {
        return {
            combination: _p.r(29),
            fraction: _p.r(30),
            "function": _p.r(31),
            integration: _p.r(32),
            subscript: _p.r(39),
            superscript: _p.r(41),
            script: _p.r(37),
            radical: _p.r(38),
            summation: _p.r(40),
            brackets: _p.r(28),
            mathcal: _p.r(34),
            mathfrak: _p.r(35),
            mathbb: _p.r(33),
            mathrm: _p.r(36)
        };
    }
};

/*!
 * 特殊字符定义
 */
_p[10] = {
    value: function() {
        return {
            "#": 1,
            $: 1,
            "%": 1,
            _: 1,
            "&": 1,
            "{": 1,
            "}": 1,
            "^": 1,
            "~": 1
        };
    }
};

/**
 * 操作符类型定义
 */
_p[11] = {
    value: function() {
        return {
            OP: 1,
            FN: 2
        };
    }
};

/*!
 * 括号处理器
 */
_p[12] = {
    value: function(require) {
        var BRACKETS_TYPE = _p.r(5);
        return function(info, processedStack, unprocessedStack) {
            // 括号验证
            for (var i = 0, len = info.params.length; i < len; i++) {
                if (!(info.params[i] in BRACKETS_TYPE)) {
                    throw new Error("Brackets: invalid params");
                }
            }
            info.operand = info.params;
            info.params[2] = unprocessedStack.shift();
            delete info.handler;
            delete info.params;
            return info;
        };
    }
};

/*!
 * 合并处理(特殊处理函数)
 */
_p[13] = {
    value: function() {
        return function() {
            return {
                name: "combination",
                operand: arguments[0] || []
            };
        };
    }
};

/*!
 * 分数函数处理器
 */
_p[14] = {
    value: function() {
        // 处理函数接口
        return function(info, processedStack, unprocessedStack) {
            var numerator = unprocessedStack.shift(), // 分子
            denominator = unprocessedStack.shift();
            // 分母
            if (numerator === undefined || denominator === undefined) {
                throw new Error("Frac: Syntax Error");
            }
            info.operand = [ numerator, denominator ];
            delete info.handler;
            return info;
        };
    }
};

/*!
 * 函数表达式处理器
 */
_p[15] = {
    value: function(require) {
        var ScriptExtractor = _p.r(17);
        // 处理函数接口
        return function(info, processedStack, unprocessedStack) {
            var params = ScriptExtractor.exec(unprocessedStack);
            info.operand = [ info.params, params.expr, params.superscript, params.subscript ];
            delete info.params;
            delete info.handler;
            return info;
        };
    }
};

/*!
 * 积分函数处理器
 */
_p[16] = {
    value: function(require) {
        var ScriptExtractor = _p.r(17), FN_TYPE = _p.r(11).FN;
        return function(info, processedStack, unprocessedStack) {
            var count = unprocessedStack.shift(), params = ScriptExtractor.exec(unprocessedStack);
            if (params.expr && params.expr.type === FN_TYPE && params.expr.handler) {
                params.expr = params.expr.handler(params.expr, processedStack, unprocessedStack);
            }
            info.operand = [ params.expr, params.superscript, params.subscript ];
            // 参数配置调用
            info.callFn = {
                setType: [ count | 0 ]
            };
            delete info.handler;
            return info;
        };
    }
};

/*!
 * 通用上下标提取器
 */
_p[17] = {
    value: function() {
        return {
            exec: function(stack) {
                // 提取上下标
                var result = extractScript(stack), expr = stack.shift();
                if (expr && expr.name && expr.name.indexOf("script") !== -1) {
                    throw new Error("Script: syntax error!");
                }
                result.expr = expr || null;
                return result;
            }
        };
        function extractScript(stack) {
            var scriptGroup = extract(stack), nextGroup = null, result = {
                superscript: null,
                subscript: null
            };
            if (scriptGroup) {
                nextGroup = extract(stack);
            } else {
                return result;
            }
            result[scriptGroup.type] = scriptGroup.value || null;
            if (nextGroup) {
                if (nextGroup.type === scriptGroup.type) {
                    throw new Error("Script: syntax error!");
                }
                result[nextGroup.type] = nextGroup.value || null;
            }
            return result;
        }
        function extract(stack) {
            var forward = stack.shift();
            if (!forward) {
                return null;
            }
            if (forward.name === "subscript" || forward.name === "superscript") {
                return {
                    type: forward.name,
                    value: stack.shift()
                };
            }
            stack.unshift(forward);
            return null;
        }
    }
};

/*!
 * 双线处理
 */
_p[18] = {
    value: function() {
        return function(info, processedStack, unprocessedStack) {
            var chars = unprocessedStack.shift();
            if (typeof chars === "object" && chars.name === "combination") {
                chars = chars.operand.join("");
            }
            info.name = "text";
            info.attr = {
                _reverse: "mathbb"
            };
            info.callFn = {
                setFamily: [ "KF AMS BB" ]
            };
            info.operand = [ chars ];
            delete info.handler;
            return info;
        };
    }
};

/*!
 * 手写体处理
 */
_p[19] = {
    value: function() {
        return function(info, processedStack, unprocessedStack) {
            var chars = unprocessedStack.shift();
            if (typeof chars === "object" && chars.name === "combination") {
                chars = chars.operand.join("");
            }
            info.name = "text";
            info.attr = {
                _reverse: "mathcal"
            };
            info.callFn = {
                setFamily: [ "KF AMS CAL" ]
            };
            info.operand = [ chars ];
            delete info.handler;
            return info;
        };
    }
};

/*!
 * 花体处理
 */
_p[20] = {
    value: function() {
        return function(info, processedStack, unprocessedStack) {
            var chars = unprocessedStack.shift();
            if (typeof chars === "object" && chars.name === "combination") {
                chars = chars.operand.join("");
            }
            info.name = "text";
            info.attr = {
                _reverse: "mathfrak"
            };
            info.callFn = {
                setFamily: [ "KF AMS FRAK" ]
            };
            info.operand = [ chars ];
            delete info.handler;
            return info;
        };
    }
};

/*!
 * 罗马处理
 */
_p[21] = {
    value: function() {
        return function(info, processedStack, unprocessedStack) {
            var chars = unprocessedStack.shift();
            if (typeof chars === "object" && chars.name === "combination") {
                chars = chars.operand.join("");
            }
            info.name = "text";
            info.attr = {
                _reverse: "mathrm"
            };
            info.callFn = {
                setFamily: [ "KF AMS ROMAN" ]
            };
            info.operand = [ chars ];
            delete info.handler;
            return info;
        };
    }
};

/*!
 * 上下标操作符函数处理
 */
_p[22] = {
    value: function() {
        // 处理函数接口
        return function(info, processedStack, unprocessedStack) {
            var base = processedStack.pop(), script = unprocessedStack.shift() || null;
            if (!script) {
                throw new Error("Missing script");
            }
            base = base || "";
            if (base.name === info.name || base.name === "script") {
                throw new Error("script error");
            }
            // 执行替换
            if (base.name === "subscript") {
                base.name = "script";
                base.operand[2] = base.operand[1];
                base.operand[1] = script;
                return base;
            } else if (base.name === "superscript") {
                base.name = "script";
                base.operand[2] = script;
                return base;
            }
            info.operand = [ base, script ];
            // 删除处理器
            delete info.handler;
            return info;
        };
    }
};

/*!
 * 方根函数处理器
 */
_p[23] = {
    value: function(require) {
        var mergeHandler = _p.r(13);
        // 处理函数接口
        return function(info, processedStack, unprocessedStack) {
            var exponent = unprocessedStack.shift(), tmp = null, // 被开方数
            radicand = null;
            if (exponent === "[") {
                exponent = [];
                while (tmp = unprocessedStack.shift()) {
                    if (tmp === "]") {
                        break;
                    }
                    exponent.push(tmp);
                }
                if (exponent.length === 0) {
                    exponent = null;
                } else {
                    exponent = mergeHandler(exponent);
                }
                radicand = unprocessedStack.shift();
            } else {
                radicand = exponent;
                exponent = null;
            }
            info.operand = [ radicand, exponent ];
            delete info.handler;
            return info;
        };
    }
};

/*!
 * 求和函数处理器
 */
_p[24] = {
    value: function(require) {
        var ScriptExtractor = _p.r(17);
        return function(info, processedStack, unprocessedStack) {
            var params = ScriptExtractor.exec(unprocessedStack);
            info.operand = [ params.expr, params.superscript, params.subscript ];
            delete info.handler;
            return info;
        };
    }
};

/**
 * Kity Formula Latex解析器实现
 */
/* jshint forin: false */
_p[25] = {
    value: function(require) {
        var Parser = _p.r(43).Parser, LatexUtils = _p.r(1), PRE_HANDLER = _p.r(8), serialization = _p.r(42), OP_DEFINE = _p.r(7), REVERSE_DEFINE = _p.r(9), SPECIAL_LIST = _p.r(10), Utils = _p.r(4);
        // data
        var leftChar = "￸", rightChar = "￼", clearCharPattern = new RegExp(leftChar + "|" + rightChar, "g"), leftCharPattern = new RegExp(leftChar, "g"), rightCharPattern = new RegExp(rightChar, "g");
        Parser.register("latex", Parser.implement({
            parse: function(data) {
                var units = this.split(this.format(data));
                units = this.parseToGroup(units);
                units = this.parseToStruct(units);
                return this.generateTree(units);
            },
            serialization: function(tree, options) {
                return serialization(tree, options);
            },
            expand: function(expandObj) {
                var parseObj = expandObj.parse, formatKey = null, preObj = expandObj.pre, reverseObj = expandObj.reverse;
                for (var key in parseObj) {
                    if (!parseObj.hasOwnProperty(key)) {
                        continue;
                    }
                    formatKey = key.replace(/\\/g, "");
                    OP_DEFINE[formatKey] = parseObj[key];
                }
                for (var key in reverseObj) {
                    if (!reverseObj.hasOwnProperty(key)) {
                        continue;
                    }
                    REVERSE_DEFINE[key.replace(/\\/g, "")] = reverseObj[key];
                }
                // 预处理
                if (preObj) {
                    for (var key in preObj) {
                        if (!preObj.hasOwnProperty(key)) {
                            continue;
                        }
                        PRE_HANDLER[key.replace(/\\/g, "")] = preObj[key];
                    }
                }
            },
            // 格式化输入数据
            format: function(input) {
                // 清理多余的空格
                input = clearEmpty(input);
                // 处理输入的“{”和“}”
                input = input.replace(clearCharPattern, "").replace(/\\{/gi, leftChar).replace(/\\}/gi, rightChar);
                // 预处理器处理
                for (var key in PRE_HANDLER) {
                    if (PRE_HANDLER.hasOwnProperty(key)) {
                        input = PRE_HANDLER[key](input);
                    }
                }
                return input;
            },
            split: function(data) {
                var units = [], pattern = /(?:\\[^a-z]\s*)|(?:\\[a-z]+\s*)|(?:[{}]\s*)|(?:[^\\{}]\s*)/gi, emptyPattern = /^\s+|\s+$/g, match = null;
                data = data.replace(emptyPattern, "");
                while (match = pattern.exec(data)) {
                    match = match[0].replace(emptyPattern, "");
                    if (match) {
                        units.push(match);
                    }
                }
                return units;
            },
            /**
         * 根据解析出来的语法单元生成树
         * @param units 单元
         * @return 生成的树对象
         */
            generateTree: function(units) {
                var tree = [], currentUnit = null;
                // 递归处理
                while (currentUnit = units.shift()) {
                    if (Utils.isArray(currentUnit)) {
                        tree.push(this.generateTree(currentUnit));
                    } else {
                        tree.push(currentUnit);
                    }
                }
                tree = LatexUtils.toRPNExpression(tree);
                return LatexUtils.generateTree(tree);
            },
            parseToGroup: function(units) {
                var group = [], groupStack = [ group ], groupCount = 0, bracketsCount = 0;
                for (var i = 0, len = units.length; i < len; i++) {
                    switch (units[i]) {
                      case "{":
                        groupCount++;
                        groupStack.push(group);
                        group.push([]);
                        group = group[group.length - 1];
                        break;

                      case "}":
                        groupCount--;
                        group = groupStack.pop();
                        break;

                      // left-right分组
                        case "\\left":
                        bracketsCount++;
                        groupStack.push(group);
                        // 进入两层
                        group.push([ [] ]);
                        group = group[group.length - 1][0];
                        group.type = "brackets";
                        // 读取左括号
                        i++;
                        group.leftBrackets = units[i].replace(leftCharPattern, "{").replace(rightCharPattern, "}");
                        break;

                      case "\\right":
                        bracketsCount--;
                        // 读取右括号
                        i++;
                        group.rightBrackets = units[i].replace(leftCharPattern, "{").replace(rightCharPattern, "}");
                        group = groupStack.pop();
                        break;

                      default:
                        group.push(units[i].replace(leftCharPattern, "\\{").replace(rightCharPattern, "\\}"));
                        break;
                    }
                }
                if (groupCount !== 0) {
                    throw new Error("Group Error!");
                }
                if (bracketsCount !== 0) {
                    throw new Error("Brackets Error!");
                }
                return groupStack[0];
            },
            parseToStruct: function(units) {
                var structs = [];
                for (var i = 0, len = units.length; i < len; i++) {
                    if (Utils.isArray(units[i])) {
                        if (units[i].type === "brackets") {
                            // 处理自动调整大小的括号组
                            // 获取括号组定义
                            structs.push(Utils.getBracketsDefine(units[i].leftBrackets, units[i].rightBrackets));
                            // 处理内部表达式
                            structs.push(this.parseToStruct(units[i]));
                        } else {
                            // 普通组
                            structs.push(this.parseToStruct(units[i]));
                        }
                    } else {
                        structs.push(parseStruct(units[i]));
                    }
                }
                return structs;
            }
        }));
        /**
     * 把序列化的字符串表示法转化为中间格式的结构化表示
     */
        function parseStruct(str) {
            // 特殊控制字符优先处理
            if (isSpecialCharacter(str)) {
                return str.substring(1);
            }
            switch (Utils.getLatexType(str)) {
              case "operator":
                return Utils.getDefine(str);

              case "function":
                return Utils.getFuncDefine(str);

              default:
                // text
                return transformSpecialCharacters(str);
            }
        }
        // 转换特殊的文本字符
        function transformSpecialCharacters(char) {
            if (char.indexOf("\\") === 0) {
                return char + "\\";
            }
            return char;
        }
        function isSpecialCharacter(char) {
            if (char.indexOf("\\") === 0) {
                return !!SPECIAL_LIST[char.substring(1)];
            }
            return false;
        }
        function clearEmpty(data) {
            return data.replace(/\\\s+/, "").replace(/\s*([^a-z0-9\s])\s*/gi, function(match, symbol) {
                return symbol;
            });
        }
    }
};

/**
 * “开方”预处理器
 */
_p[26] = {
    value: function() {
        return function(input) {
            return input.replace(/\\(i+)nt(\b|[^a-zA-Z])/g, function(match, sign, suffix) {
                return "\\int " + sign.length + suffix;
            });
        };
    }
};

/**
 * “双引号”预处理器
 */
_p[27] = {
    value: function() {
        return function(input) {
            return input.replace(/``/g, "“");
        };
    }
};

/*!
 * 逆解析处理函数: brackets
 */
_p[28] = {
    value: function() {
        /**
     * operands中元素对照表
     * 0: 左符号
     * 1: 右符号
     * 2: 表达式
     */
        return function(operands) {
            if (operands[0] === "{" || operands[0] === "}") {
                operands[0] = "\\" + operands[0];
            }
            if (operands[1] === "{" || operands[1] === "}") {
                operands[1] = "\\" + operands[1];
            }
            return [ "\\left", operands[0], operands[2], "\\right", operands[1] ].join(" ");
        };
    }
};

/*!
 * 逆解析处理函数：combination
 */
_p[29] = {
    value: function() {
        return function(operands) {
            if (this.attr["data-root"] || this.attr["data-placeholder"]) {
                return operands.join("");
            }
            return "{" + operands.join("") + "}";
        };
    }
};

/*!
 * 逆解析处理函数: fraction
 */
_p[30] = {
    value: function() {
        return function(operands) {
            return "\\frac " + operands[0] + " " + operands[1];
        };
    }
};

/*!
 * 逆解析处理函数: func
 */
_p[31] = {
    value: function() {
        /**
     * operands中元素对照表
     * 0: 函数名
     * 1: 上标
     * 2: 下标
     */
        return function(operands) {
            var result = [ "\\" + operands[0] ];
            // 上标
            if (operands[2]) {
                result.push("^" + operands[2]);
            }
            // 下标
            if (operands[3]) {
                result.push("_" + operands[3]);
            }
            if (operands[1]) {
                result.push(" " + operands[1]);
            }
            return result.join("");
        };
    }
};

/*!
 * 逆解析处理函数: integration
 */
_p[32] = {
    value: function() {
        /**
     * operands中元素对照表
     * 0: 上标
     * 1: 下标
     */
        return function(operands) {
            var result = [ "\\int " ];
            // 修正多重积分的序列化
            if (this.callFn && this.callFn.setType) {
                result = [ "\\" ];
                for (var i = 0, len = this.callFn.setType; i < len; i++) {
                    result.push("i");
                }
                result.push("nt ");
            }
            // 上标
            if (operands[1]) {
                result.push("^" + operands[1]);
            }
            // 下标
            if (operands[2]) {
                result.push("_" + operands[2]);
            }
            if (operands[0]) {
                result.push(" " + operands[0]);
            }
            return result.join("");
        };
    }
};

/*!
 * 逆解析处理函数: mathbb
 */
_p[33] = {
    value: function() {
        return function(operands) {
            return "\\mathbb{" + operands[0] + "}";
        };
    }
};

/*!
 * 逆解析处理函数: mathcal
 */
_p[34] = {
    value: function() {
        return function(operands) {
            return "\\mathcal{" + operands[0] + "}";
        };
    }
};

/*!
 * 逆解析处理函数: mathfrak
 */
_p[35] = {
    value: function() {
        return function(operands) {
            return "\\mathfrak{" + operands[0] + "}";
        };
    }
};

/*!
 * 逆解析处理函数: mathcal
 */
_p[36] = {
    value: function() {
        return function(operands) {
            return "\\mathrm{" + operands[0] + "}";
        };
    }
};

/*!
 * 逆解析处理函数: script
 */
_p[37] = {
    value: function() {
        /**
     * operands中元素对照表
     * 0: 表达式
     * 1: 上标
     * 2: 下标
     */
        return function(operands) {
            return operands[0] + "^" + operands[1] + "_" + operands[2];
        };
    }
};

/*!
 * 逆解析处理函数: sqrt
 */
_p[38] = {
    value: function() {
        /**
     * operands中元素对照表
     * 0: 表达式
     * 1: 指数
     */
        return function(operands) {
            var result = [ "\\sqrt" ];
            // 上标
            if (operands[1]) {
                result.push("[" + operands[1] + "]");
            }
            result.push(" " + operands[0]);
            return result.join("");
        };
    }
};

/*!
 * 逆解析处理函数: subscript
 */
_p[39] = {
    value: function() {
        /**
     * operands中元素对照表
     * 0: 表达式
     * 1: 下标
     */
        return function(operands) {
            return operands[0] + "_" + operands[1];
        };
    }
};

/*!
 * 逆解析处理函数: summation
 */
_p[40] = {
    value: function() {
        /**
     * operands中元素对照表
     * 0: 上标
     * 1: 下标
     */
        return function(operands) {
            var result = [ "\\sum " ];
            // 上标
            if (operands[1]) {
                result.push("^" + operands[1]);
            }
            // 下标
            if (operands[2]) {
                result.push("_" + operands[2]);
            }
            if (operands[0]) {
                result.push(" " + operands[0]);
            }
            return result.join("");
        };
    }
};

/*!
 * 逆解析处理函数: superscript
 */
_p[41] = {
    value: function() {
        /**
     * operands中元素对照表
     * 0: 表达式
     * 1: 上标
     */
        return function(operands) {
            return operands[0] + "^" + operands[1];
        };
    }
};

/**
 * Created by hn on 14-3-20.
 */
_p[42] = {
    value: function(require) {
        var reverseHandlerTable = _p.r(9), SPECIAL_LIST = _p.r(10), specialCharPattern = /(\\(?:[\w]+)|(?:[^a-z]))\\/gi;
        return function(tree, options) {
            return reverseParse(tree, options);
        };
        function reverseParse(tree, options) {
            var operands = [], reverseHandlerName = null, originalOperands = null;
            // 字符串处理， 需要处理特殊字符
            if (typeof tree !== "object") {
                if (isSpecialCharacter(tree)) {
                    return "\\" + tree + " ";
                }
                return tree.replace(specialCharPattern, function(match, group) {
                    return group + " ";
                });
            }
            // combination需要特殊处理, 重复嵌套的combination节点要删除
            if (tree.name === "combination" && tree.operand.length === 1 && tree.operand[0].name === "combination") {
                tree = tree.operand[0];
            }
            originalOperands = tree.operand;
            for (var i = 0, len = originalOperands.length; i < len; i++) {
                if (originalOperands[i]) {
                    operands.push(reverseParse(originalOperands[i]));
                } else {
                    operands.push(originalOperands[i]);
                }
            }
            if (tree.attr && tree.attr._reverse) {
                reverseHandlerName = tree.attr._reverse;
            } else {
                reverseHandlerName = tree.name;
            }
            return reverseHandlerTable[reverseHandlerName].call(tree, operands, options);
        }
        function isSpecialCharacter(char) {
            return !!SPECIAL_LIST[char];
        }
    }
};

/*!
 * Kity Formula 公式表示法Parser接口
 */
_p[43] = {
    value: function(require, exports, module) {
        // Parser 配置列表
        var CONF = {}, IMPL_POLL = {}, // 内部简单工具类
        Utils = {
            extend: function(target, sources) {
                var source = null;
                sources = [].slice.call(arguments, 1);
                for (var i = 0, len = sources.length; i < len; i++) {
                    source = sources[i];
                    for (var key in source) {
                        if (source.hasOwnProperty(key)) {
                            target[key] = source[key];
                        }
                    }
                }
            },
            setData: function(container, key, value) {
                if (typeof key === "string") {
                    container[key] = value;
                } else if (typeof key === "object") {
                    for (value in key) {
                        if (key.hasOwnProperty(value)) {
                            container[value] = key[value];
                        }
                    }
                } else {
                    // 配置项类型错误
                    throw new Error("invalid option");
                }
            }
        };
        /**
     * 解析器
     */
        var Parser = {
            use: function(type) {
                if (!IMPL_POLL[type]) {
                    throw new Error("unknown parser type");
                }
                return this.proxy(IMPL_POLL[type]);
            },
            config: function(key, value) {
                Utils.setData(CONF, key, value);
                return this;
            },
            /**
         * 注册解析器实现
         * @param type 解析器所属类型
         * @param parserImpl 解析器实现
         */
            register: function(type, parserImpl) {
                IMPL_POLL[type.toLowerCase()] = parserImpl;
                return this;
            },
            // 提供构造器的实现的默认结构
            implement: function(parser) {
                var Impl = function() {}, constructor = parser.constructor || function() {}, result = function() {
                    ParserInterface.call(this);
                    constructor.call(this);
                };
                Impl.prototype = ParserInterface.prototype;
                result.prototype = new Impl();
                delete parser.constructor;
                for (var key in parser) {
                    if (key !== "constructor" && parser.hasOwnProperty(key)) {
                        result.prototype[key] = parser[key];
                    }
                }
                return result;
            },
            /**
         * 代理给定的parser实现
         * @private
         * @param parserImpl 需代理的parser实现
         */
            proxy: function(parserImpl) {
                return new ParserProxy(parserImpl);
            }
        };
        /**
     * parser实现的代理对象， 所有实现均通过该代理对象对外提供统一接口
     * @constructor
     * @param parserImpl 需代理的对象
     */
        function ParserProxy(ParserImpl) {
            this.impl = new ParserImpl();
            this.conf = {};
        }
        Utils.extend(ParserProxy.prototype, {
            config: function(key, value) {
                Utils.setData(this.conf, key, value);
            },
            /**
         * 设置特定解析器实现所需的配置项，参数也可以是一个Key-Value Mapping
         * @param key 配置项名称
         * @param value 配置项值
         */
            set: function(key, value) {
                this.impl.set(key, value);
            },
            parse: function(data) {
                var result = {
                    config: {},
                    // 调用实现获取解析树
                    tree: this.impl.parse(data)
                };
                Utils.extend(result.config, CONF, this.conf);
                return result;
            },
            serialization: function(tree, options) {
                return this.impl.serialization(tree, options);
            },
            expand: function(obj) {
                this.impl.expand(obj);
            }
        });
        /**
     * 解析器所需实现的接口
     * @constructor
     */
        function ParserInterface() {
            this.conf = {};
        }
        Utils.extend(ParserInterface.prototype, {
            set: function(key, value) {
                Utils.extend(this.conf, key, value);
            },
            /**
         * 需要特定解析器实现， 该方法是解析器的核心方法，解析器的实现者应该完成该方法对给定数据进行解析
         * @param data 待解析的数据
         * @return 解析树， 具体格式庆参考Kity Formula Parser 的文档
         */
            parse: function() {
                throw new Error("Abstract function");
            }
        });
        // exports
        module.exports = {
            Parser: Parser,
            ParserInterface: ParserInterface
        };
    }
};

/*!
 * 启动模块
 */
_p[44] = {
    value: function(require) {
        var Parser = _p.r(43).Parser;
        // 初始化组件
        _p.r(25);
        window.kf.Parser = Parser;
        window.kf.Assembly = _p.r(0);
    }
};

var moduleMapping = {
    "kf.start": 44
};

function use(name) {
    _p.r([ moduleMapping[name] ]);
}
/**
 * 模块暴露
 */

( function ( global ) {

    // build环境中才含有use
    try {
        use( 'kf.start' );
    } catch ( e ) {
    }

} )( this );
})();