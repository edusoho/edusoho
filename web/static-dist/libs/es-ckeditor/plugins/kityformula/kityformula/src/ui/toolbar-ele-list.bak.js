/*!
 * toolbar元素列表定义
 */

define( function ( require ) {

    var UI_ELE_TYPE = require( "ui/ui-impl/def/ele-type" ),
        BOX_TYPE = require( "ui/ui-impl/def/box-type" ),
        kity = require( "kity" );

    var config = [ {
        type: UI_ELE_TYPE.DRAPDOWN_BOX,
        options: {
            button: {
                label: '预设<br/>',
                className: 'yushe-btn',
                icon: "assets/images/toolbar/button/fx.png",
                iconSize: {
                    w: 40
                }
            },
            box: {
                width: 367,
                group: [ {
                    title: "预设公式",
                    items: [ {
                        title: "预设公式",
                        content: [ {
                            label: "二次公式",
                            item: {
                                show: 'assets/images/toolbar/ys/1.png',
                                val: "x=\\frac {-b\\pm\\sqrt {b^2-4ac}}{2a}"
                            }
                        }, {
                            label: "二项式定理",
                            item: {
                                show: 'assets/images/toolbar/ys/2.png',
                                val: "{\\left(x+a\\right)}^2=\\sum^n_{k=0}{\\left(^n_k\\right)x^ka^{n-k}}"
                            }
                        }, {
                            label: "勾股定理",
                            item: {
                                show: 'assets/images/toolbar/ys/3.png',
                                val: "a^2+b^2=c^2"
                            }
                        } ]
                    } ]
                } ]
            }
        }
    }, {
        type: UI_ELE_TYPE.DELIMITER
    }, {
        type: UI_ELE_TYPE.AREA,
        options: {
            box: {
                fixOffset: true,
                width: 527,
                type: BOX_TYPE.OVERLAP,
                group: [ {
                    title: "基础数学",
                    items: []
                }, {
                    title: "希腊字母",
                    items: []
                }, {
                    title: "求反关系运算符",
                    items: []
                }, {
                    title: "字母类符号",
                    items: []
                }, {
                    title: "箭头",
                    items: []
                }, {
                    title: "手写体",
                    items: []
                } ]
            }
        }
    }, {
        type: UI_ELE_TYPE.DELIMITER
    }, {
        type: UI_ELE_TYPE.DRAPDOWN_BOX,
        options: {
            button: {
                label: "分数<br/>",
                icon: "assets/images/toolbar/button/frac.png"
            },
            box: {
                width: 332,
                group: [ {
                    title: "分数",
                    items: [ {
                        title: "分数",
                        content: [ {
                            item: {
                                show: 'assets/images/toolbar/frac/1.png',
                                val: "\\frac \\placeholder\\placeholder"
                            }
                        }, {
                            item: {
                                show: 'assets/images/toolbar/frac/2.png',
                                val: "{\\placeholder/\\placeholder}"
                            }
                        } ]
                    }, {
                        title: "常用分数",
                        content: [ {
                            item: {
                                show: 'assets/images/toolbar/frac/c1.png',
                                val: "\\frac {dy}{dx}"
                            }
                        }, {
                            item: {
                                show: 'assets/images/toolbar/frac/c2.png',
                                val: "\\frac {\\Delta y}{\\Delta x}"
                            }
                        }, {
                            item: {
                                show: 'assets/images/toolbar/frac/c4.png',
                                val: "\\frac {\\delta y}{\\delta x}"
                            }
                        }, {
                            item: {
                                show: 'assets/images/toolbar/frac/c5.png',
                                val: "\\frac \\pi 2"
                            }
                        } ]
                    } ]
                } ]
            }
        }
    }, {
        type: UI_ELE_TYPE.DRAPDOWN_BOX,
        options: {
            button: {
                label: "上下标<br/>",
                icon: "assets/images/toolbar/button/script.png"
            },
            box: {
                width: 332,
                group: [ {
                    title: "上标和下标",
                    items: [ {
                        title: "上标和下标",
                        content: [ {
                            item: {
                                show: 'assets/images/toolbar/script/1.png',
                                val: "\\placeholder^\\placeholder"
                            }
                        }, {
                            item: {
                                show: 'assets/images/toolbar/script/2.png',
                                val: "\\placeholder_\\placeholder"
                            }
                        }, {
                            item: {
                                show: 'assets/images/toolbar/script/3.png',
                                val: "\\placeholder^\\placeholder_\\placeholder"
                            }
                        }, {
                            item: {
                                show: 'assets/images/toolbar/script/4.png',
                                val: "{^\\placeholder_\\placeholder\\placeholder}"
                            }
                        } ]
                    }, {
                        title: "常用的上标和下标",
                        content: [ {
                            item: {
                                show: 'assets/images/toolbar/script/c1.png',
                                val: "e^{-i\\omega t}"
                            }
                        }, {
                            item: {
                                show: 'assets/images/toolbar/script/c2.png',
                                val: "x^2"
                            }
                        }, {
                            item: {
                                show: 'assets/images/toolbar/script/c3.png',
                                val: "{}^n_1Y"
                            }
                        } ]
                    } ]
                } ]
            }
        }
    }, {
        type: UI_ELE_TYPE.DRAPDOWN_BOX,
        options: {
            button: {
                label: "根式<br/>",
                icon: "assets/images/toolbar/button/sqrt.png"
            },
            box: {
                width: 342,
                group: [ {
                    title: "根式",
                    items: [ {
                        title: "根式",
                        content: [ {
                            item: {
                                show: 'assets/images/toolbar/sqrt/1.png',
                                val: "\\sqrt \\placeholder"
                            }
                        }, {
                            item: {
                                show: 'assets/images/toolbar/sqrt/2.png',
                                val: "\\sqrt [\\placeholder] \\placeholder"
                            }
                        }, {
                            item: {
                                show: 'assets/images/toolbar/sqrt/3.png',
                                val: "\\sqrt [2] \\placeholder"
                            }
                        }, {
                            item: {
                                show: 'assets/images/toolbar/sqrt/4.png',
                                val: "\\sqrt [3] \\placeholder"
                            }
                        } ]
                    }, {
                        title: "常用根式",
                        content: [ {
                            item: {
                                show: 'assets/images/toolbar/sqrt/c1.png',
                                val: "\\frac {-b\\pm\\sqrt{b^2-4ac}}{2a}"
                            }
                        }, {
                            item: {
                                show: 'assets/images/toolbar/sqrt/c2.png',
                                val: "\\sqrt {a^2+b^2}"
                            }
                        } ]
                    } ]
                } ]
            }
        }
    }, {
        type: UI_ELE_TYPE.DRAPDOWN_BOX,
        options: {
            button: {
                label: "积分<br/>",
                icon: "assets/images/toolbar/button/int.png"
            },
            box: {
                width: 332,
                group: [ {
                    title: "积分",
                    items: [ {
                        title: "积分",
                        content: [ {
                            item: {
                                show: 'assets/images/toolbar/int/1.png',
                                val: "\\int \\placeholder"
                            }
                        }, {
                            item: {
                                show: 'assets/images/toolbar/int/2.png',
                                val: "\\int^\\placeholder_\\placeholder\\placeholder"
                            }
                        }, {
                            item: {
                                show: 'assets/images/toolbar/int/3.png',
                                val: "\\iint\\placeholder"
                            }
                        }, {
                            item: {
                                show: 'assets/images/toolbar/int/4.png',
                                val: "\\iint^\\placeholder_\\placeholder\\placeholder"
                            }
                        }, {
                            item: {
                                show: 'assets/images/toolbar/int/5.png',
                                val: "\\iiint\\placeholder"
                            }
                        }, {
                            item: {
                                show: 'assets/images/toolbar/int/6.png',
                                val: "\\iiint^\\placeholder_\\placeholder\\placeholder"
                            }
                        } ]
                    } ]
                } ]
            }
        }
    }, {
        type: UI_ELE_TYPE.DRAPDOWN_BOX,
        options: {
            button: {
                label: "大型<br/>运算符",
                icon: "assets/images/toolbar/button/sum.png"
            },
            box: {
                width: 332,
                group: [ {
                    title: "求和",
                    items: [ {
                        title: "求和",
                        content: [ {
                            item: {
                                show: 'assets/images/toolbar/large/1.png',
                                val: "\\sum\\placeholder"
                            }
                        }, {
                            item: {
                                show: 'assets/images/toolbar/large/2.png',
                                val: "\\sum^\\placeholder_\\placeholder\\placeholder"
                            }
                        }, {
                            item: {
                                show: 'assets/images/toolbar/large/3.png',
                                val: "\\sum_\\placeholder\\placeholder"
                            }
                        } ]
                    } ]
                } ]
            }
        }
    }, {
        type: UI_ELE_TYPE.DRAPDOWN_BOX,
        options: {
            button: {
                label: "括号<br/>",
                icon: "assets/images/toolbar/button/brackets.png"
            },
            box: {
                width: 332,
                group: [ {
                    title: "方括号",
                    items: [ {
                        title: "方括号",
                        content: [ {
                            item: {
                                show: 'assets/images/toolbar/brackets/1.png',
                                val: "\\left(\\placeholder\\right)"
                            }
                        }, {
                            item: {
                                show: 'assets/images/toolbar/brackets/2.png',
                                val: "\\left[\\placeholder\\right]"
                            }
                        }, {
                            item: {
                                show: 'assets/images/toolbar/brackets/3.png',
                                val: "\\left\\{\\placeholder\\right\\}"
                            }
                        }, {
                            item: {
                                show: 'assets/images/toolbar/brackets/4.png',
                                val: "\\left|\\placeholder\\right|"
                            }
                        } ]
                    } ]
                } ]
            }
        }
    }, {
        type: UI_ELE_TYPE.DRAPDOWN_BOX,
        options: {
            button: {
                label: "函数<br/>",
                icon: "assets/images/toolbar/button/sin.png"
            },
            box: {
                width: 340,
                group: [ {
                    title: "函数",
                    items: [ {
                        title: "三角函数",
                        content: [ {
                            item: {
                                show: 'assets/images/toolbar/func/1.png',
                                val: "\\sin\\placeholder"
                            }
                        }, {
                            item: {
                                show: 'assets/images/toolbar/func/2.png',
                                val: "\\cos\\placeholder"
                            }
                        }, {
                            item: {
                                show: 'assets/images/toolbar/func/3.png',
                                val: "\\tan\\placeholder"
                            }
                        }, {
                            item: {
                                show: 'assets/images/toolbar/func/4.png',
                                val: "\\csc\\placeholder"
                            }
                        }, {
                            item: {
                                show: 'assets/images/toolbar/func/5.png',
                                val: "\\sec\\placeholder"
                            }
                        }, {
                            item: {
                                show: 'assets/images/toolbar/func/6.png',
                                val: "\\cot\\placeholder"
                            }
                        } ]
                    }, {
                        title: "常用函数",
                        content: [ {
                            item: {
                                show: 'assets/images/toolbar/func/c1.png',
                                val: "\\sin\\theta"
                            }
                        }, {
                            item: {
                                show: 'assets/images/toolbar/func/c2.png',
                                val: "\\sin{2x}"
                            }
                        }, {
                            item: {
                                show: 'assets/images/toolbar/func/c3.png',
                                val: "\\tan\\theta=\\frac {\\sin\\theta}{\\cos\\theta}"
                            }
                        } ]
                    } ]
                } ]
            }
        }
    } ];

    // 初始化基础数学
    ( function () {

        var list = [
                "pm", "infty", {
                    key: "=",
                    img: "eq"
                }, "sim", "times", "div", {
                    key: "!",
                    img: "tanhao"
                }, {
                    key: "<",
                    img: "lt"
                }, "ll", {
                    key: ">",
                    img: "gt"
                },
                "gg", "leq", "geq", "mp", "cong", "equiv", "propto", "approx", "forall", "partial",
                "surd", "cup", "cap", "varnothing", {
                    key: "%",
                    img: "baifenhao"
                },
                "circ", "exists", "nexists", "in", "ni", "gets", "uparrow", "to", "downarrow",
                "leftrightarrow", "therefore", "because", {
                    key: "+",
                    img: "plus"
                }, {
                    key: "-",
                    img: "minus"
                },
                "neg", "ast", "cdot", "vdots", "ddots", "aleph", "beth", "blacksquare"

            ],
            configList = config[ 2 ].options.box.group[ 0 ].items;

        configList.push( {
            title: "基础数学",
            content: getContents( {
                path: "assets/images/toolbar/char/math/",
                values: list
            } )
        } );

    } )();

    // 初始化希腊字符配置
    ( function () {

        var greekList = [ {
                title: "小写",
                values: [ "alpha", "beta", "gamma", "delta", "epsilon", "zeta", "eta", "theta", "iota", "kappa", "lambda", "mu", "nu", "xi", "omicron", "pi", "rho", "sigma", "tau", "upsilon", "phi", "chi", "psi", "omega" ]
            }, {
                title: "大写",
                values: [ "Alpha", "Beta", "Gamma", "Delta", "Epsilon", "Zeta", "Eta", "Theta", "Iota", "Kappa", "Lambda", "Mu", "Nu", "Xi", "Omicron", "Pi", "Rho", "Sigma", "Tau", "Upsilon", "Phi", "Chi", "Psi", "Omega" ]
            }, {
                title: "变体",
                values: [ "digamma", "varepsilon", "varkappa", "varphi", "varpi", "varrho", "varsigma", "vartheta" ]
            } ],
            greekConfigList = config[ 2 ].options.box.group[ 1 ].items;

        // 小写处理
        greekConfigList.push( {
            title: greekList[ 0 ].title,
            content: getContents( {
                path: "assets/images/toolbar/char/greek/lower/",
                values: greekList[ 0 ].values
            } )
        } );

        // 大写处理
        greekConfigList.push( {
            title: greekList[ 1 ].title,
            content: getContents( {
                path: "assets/images/toolbar/char/greek/upper/",
                values: greekList[ 1 ].values
            } )
        } );

        // 变体处理
        greekConfigList.push( {
            title: greekList[ 2 ].title,
            content: getContents( {
                path: "assets/images/toolbar/char/greek/misc/",
                values: greekList[ 2 ].values
            } )
        } );

    } )();

    // 初始化求反运算符
    ( function () {

        var greekList = [ {
                title: "求反关系运算符",
                values: [
                    "neq", "nless", "ngtr", "nleq", "ngeq", "nsim", "lneqq",
                    "gneqq", "nprec", "nsucc", "notin", "nsubseteq", "nsupseteq",
                    "subsetneq", "supsetneq", "lnsim", "gnsim", "precnsim",
                    "succnsim", "ntriangleleft", "ntriangleright", "ntrianglelefteq",
                    "ntrianglerighteq", "nmid", "nparallel", "nvdash", {
                        key: "\\nVdash",
                        img: "nvdash-1"
                    }, {
                        key: "\\nvDash",
                        img: "nvdash-2"
                    }, {
                        key: "\\nVDash",
                        img: "nvdash-3"
                    }, "nexists"
                ]
            } ],
            greekConfigList = config[ 2 ].options.box.group[ 2 ].items;

        greekConfigList.push( {
            title: greekList[ 0 ].title,
            content: getContents( {
                path: "assets/images/toolbar/char/not/",
                values: greekList[ 0 ].values
            } )
        } );

    } )();

    // 初始字母类符号
    ( function () {

        var list = [
                "aleph", "beth", "daleth", "gimel", "complement", "ell", "eth", "hbar",
                "hslash", "mho", "partial", "wp", "circledS", "Bbbk", "Finv", "Game",
                "Im", "Re"
            ],
            configList = config[ 2 ].options.box.group[ 3 ].items;

        configList.push( {
            title: "字母类符号",
            content: getContents( {
                path: "assets/images/toolbar/alphabetic/",
                values: list
            } )
        } );

    } )();

    ( function () {

        var list = [
                "gets", "to", "uparrow", "downarrow", "leftrightarrow", "updownarrow",
                {
                    key: "\\Leftarrow",
                    img: "u-leftarrow"
                }, {
                    key: "\\Rightarrow",
                    img: "u-rightarrow"
                }, {
                    key: "\\Uparrow",
                    img: "u-uparrow"
                }, {
                    key: "\\Downarrow",
                    img: "u-downarrow"
                }, {
                    key: "\\Leftrightarrow",
                    img: "u-leftrightarrow"
                }, {
                    key: "\\Updownarrow",
                    img: "u-updownarrow"
                }, "longleftarrow", "longrightarrow", "longleftrightarrow",
                {
                    key: "\\Longleftarrow",
                    img: "u-longleftarrow"
                }, {
                    key: "\\Longrightarrow",
                    img: "u-longrightarrow"
                }, {
                    key: "\\Longleftrightarrow",
                    img: "u-longleftrightarrow"
                }, "nearrow",
                "nwarrow", "searrow", "swarrow", "nleftarrow", "nrightarrow",
                {
                    key: "\\nLeftarrow",
                    img: "u-nleftarrow"
                }, {
                    key: "\\nRightarrow",
                    img: "u-nrightarrow"
                }, {
                    key: "\\nLeftrightarrow",
                    img: "u-nleftrightarrow"
                }, "leftharpoonup", "leftharpoondown", "rightharpoonup",
                "rightharpoondown", "upharpoonleft", "upharpoonright", "downharpoonleft",
                "downharpoonright", "leftrightharpoons", "rightleftharpoons", "leftleftarrows",
                "rightrightarrows", "upuparrows", "downdownarrows", "leftrightarrows",
                "rightleftarrows", "looparrowleft", "looparrowright", "leftarrowtail",
                "rightarrowtail",
                {
                    key: "\\Lsh",
                    img: "u-lsh"
                }, {
                    key: "\\Rsh",
                    img: "u-rsh"
                }, {
                    key: "\\Lleftarrow",
                    img: "u-lleftarrow"
                }, {
                    key: "\\Rrightarrow",
                    img: "u-rrightarrow"
                }, "curvearrowleft",
                "curvearrowright", "circlearrowleft", "circlearrowright", "multimap",
                "leftrightsquigarrow", "twoheadleftarrow", "twoheadrightarrow", "rightsquigarrow"
            ],
            configList = config[ 2 ].options.box.group[ 4 ].items;

        configList.push( {
            title: "箭头",
            content: getContents( {
                path: "assets/images/toolbar/arrow/",
                values: list
            } )
        } );

    } )();

    ( function () {

        var list = [ {
                title: "手写体",
                values: [
                    "A", "B", "C", "D", "E", "F", "G", "H", "I", "J", "K", "L",
                    "M", "N", "O", "P", "Q", "R", "S", "T", "U", "V", "W", "X",
                    "Y", "Z" ]
            }, {
                title: "花体",
                values: [
                    "A", "B", "C", "D", "E", "F", "G", "H", "I", "J", "K", "L",
                    "M", "N", "O", "P", "Q", "R", "S", "T", "U", "V", "W", "X",
                    "Y", "Z", "a", "b", "c", "d", "e", "f", "g", "h", "i", "j",
                    "k", "l", "m", "n", "o", "p", "q", "r", "s", "t", "u", "v",
                    "w", "x", "y", "z"
                ]
            }, {
                title: "双线",
                values: [
                    "A", "B", "C", "D", "E", "F", "G", "H", "I", "J", "K", "L",
                    "M", "N", "O", "P", "Q", "R", "S", "T", "U", "V", "W", "X",
                    "Y", "Z"
                ]
            }, {
                title: "罗马",
                values: [
                    "A", "B", "C", "D", "E", "F", "G", "H", "I", "J", "K", "L",
                    "M", "N", "O", "P", "Q", "R", "S", "T", "U", "V", "W", "X",
                    "Y", "Z", "a", "b", "c", "d", "e", "f", "g", "h", "i", "j",
                    "k", "l", "m", "n", "o", "p", "q", "r", "s", "t", "u", "v",
                    "w", "x", "y", "z"
                ]
            } ],
            configList = config[ 2 ].options.box.group[ 5 ].items;

        kity.Utils.each( list[ 0 ].values, function ( item, index ) {

            list[ 0 ].values[ index ] = {
                key: "\\mathcal{" + item + "}",
                img: item.toLowerCase()
            };

        } );

        kity.Utils.each( list[ 1 ].values, function ( item, index ) {

            list[ 1 ].values[ index ] = {
                key: "\\mathfrak{" + item + "}",
                img: item.replace( /[A-Z]/, function ( match ) {
                    return "u" + match.toLowerCase();
                } )
            };

        } );

        kity.Utils.each( list[ 2 ].values, function ( item, index ) {

            list[ 2 ].values[ index ] = {
                key: "\\mathbb{" + item + "}",
                img: item.toLowerCase()
            };

        } );

        kity.Utils.each( list[ 3 ].values, function ( item, index ) {

            list[ 3 ].values[ index ] = {
                key: "\\mathrm{" + item + "}",
                img: item.replace( /[A-Z]/, function ( match ) {
                    return "u" + match.toLowerCase();
                } )
            };

        } );

        // 手写体
        configList.push( {
            title: list[ 0 ].title,
            content: getContents( {
                path: "assets/images/toolbar/char/cal/",
                values: list[ 0 ].values
            } )
        } );

        configList.push( {
            title: list[ 1 ].title,
            content: getContents( {
                path: "assets/images/toolbar/char/frak/",
                values: list[ 1 ].values
            } )
        } );

        configList.push( {
            title: list[ 2 ].title,
            content: getContents( {
                path: "assets/images/toolbar/char/bb/",
                values: list[ 2 ].values
            } )
        } );

        configList.push( {
            title: list[ 3 ].title,
            content: getContents( {
                path: "assets/images/toolbar/char/rm/",
                values: list[ 3 ].values
            } )
        } );

    } )();

    function getContents ( data ) {

        var result = [],
            path = data.path,
            values = data.values;

        kity.Utils.each( values, function ( value ) {

            var img = value,
                val = value;

            if ( typeof value !== "string" ) {
                img = value.img;
                val = value.key;
            } else {
                val = "\\" + value;
            }

            result.push( {
                item: {
                    show: '' + path + img.toLowerCase() +'.png',
                    val: val
                }
            } );

        } );

        return result;

    }

    window.iconConfig = config;
    return config;

} );