/*!
 * 编辑器主体结构
 */

define( function ( require ) {

    var kity = require( "kity" ),
        Utils = require( "base/utils" ),
        defaultOpt = {
            formula: {
                fontsize: 50,
                autoresize: false
            },
            ui: {
                zoom: true,
                maxzoom: 2,
                minzoom: 1
            }

        };

    // 同步组件列表
    var COMPONENTS = {},
        // 异步组件列表
        ResourceManager = require( "kf" ).ResourceManager;

    var KFEditor = kity.createClass( 'KFEditor', {

        constructor: function ( container, opt ) {

            this.options = Utils.extend( true, {}, defaultOpt, opt );

            this.FormulaClass = null;
            // 就绪状态
            this._readyState = false;
            this._callbacks = [];

            this.container = container;
            this.services = {};
            this.commands = {};

            this.initResource();

        },

        isReady: function () {

            return !!this._readyState;

        },

        triggerReady: function () {

            var cb = null,
                _self = this;

            while ( cb = this._callbacks.shift() ) {
                cb.call( _self, _self );
            }

        },

        ready: function ( cb ) {

            if ( this._readyState ) {
                cb.call( this, this );
            } else {
                this._callbacks.push( cb );
            }

        },

        getContainer: function () {
            return this.container;
        },

        getDocument: function () {
            return this.container.ownerDocument;
        },

        getFormulaClass: function () {
            return this.FormulaClass;
        },

        getOptions: function () {
            return this.options;
        },

        initResource: function () {

            var _self = this;

            ResourceManager.ready( function ( Formula ) {

                _self.FormulaClass = Formula;
                _self.initComponents();
                _self._readyState = true;
                _self.triggerReady();

            }, this.options.resource );

        },

        /**
         * 初始化同步组件
         */
        initComponents: function () {

            var _self = this;

            Utils.each( COMPONENTS, function ( Component, name ) {

                new Component( _self, _self.options[ name ] );

            } );

        },

        requestService: function ( serviceName, args ) {

            var serviceObject =  getService.call( this, serviceName );

            return serviceObject.service[ serviceObject.key ].apply( serviceObject.provider, [].slice.call( arguments, 1 ) );

        },

        request: function ( serviceName ) {

            var serviceObject = getService.call( this, serviceName );

            return serviceObject.service;

        },

        registerService: function ( serviceName, provider, serviceObject ) {

            var key = null;

            for ( key in serviceObject ) {

                if ( serviceObject[ key ] && serviceObject.hasOwnProperty( key ) ) {
                    serviceObject[ key ] = Utils.proxy( serviceObject[ key ], provider );
                }

            }

            this.services[ serviceName ] = {
                provider: provider,
                key: key,
                service: serviceObject
            };

        },

        registerCommand: function ( commandName, executor, execFn ) {

            this.commands[ commandName ] = {
                executor: executor,
                execFn: execFn
            };

        },

        execCommand: function ( commandName, args ) {

            var commandObject =  this.commands[ commandName ];

            if ( !commandObject ) {
                throw new Error( 'KFEditor: not found command, ' + commandName );
            }

            return commandObject.execFn.apply( commandObject.executor, [].slice.call( arguments, 1 ) );

        },

        replaceSpecialCharacter: function(source) {
            //基础数学
            var $source = source.replace(/\\cong/g, '=^\\sim')
                .replace(/\\varnothing/g, '\\oslash')
                .replace(/\\gets/g, '\\leftarrow')
                .replace(/\\because/g, '\\cdot_\\cdot\\cdot')
                .replace(/\\blacksquare/g, '\\rule{20}{20}')
                //希腊字母
                .replace(/\\omicron/g, '\\mathrm{o}')
                .replace(/\\Alpha/g, '\\mathrm{A}')
                .replace(/\\Beta/g, '\\mathrm{B}')
                .replace(/\\Epsilon/g, '\\mathrm{E}')
                .replace(/\\Zeta/g, '\\mathrm{Z}')
                .replace(/\\Eta/g, '\\mathrm{H}')
                .replace(/\\Iota/g, '\\mathrm{I}')
                .replace(/\\Kappa/g, '\\mathrm{K}')
                .replace(/\\Mu/g, '\\mathrm{M}')
                .replace(/\\Nu/g, '\\mathrm{N}')
                .replace(/\\Omicron/g, '\\mathrm{O}')
                .replace(/\\Rho/g, '\\mathrm{P}')
                .replace(/\\Tau/g, '\\mathrm{T}')
                .replace(/\\Chi/g, '\\mathrm{X}')
                .replace(/\\digamma/g, '\\mathcal{F}')
                .replace(/\\varkappa/g, '\\mathcal{N}')
                //求反运算符
                .replace(/\\nless/g, '\\not<')
                .replace(/\\ngtr/g, '\\not>')
                .replace(/\\nleq/g, '\\not\\leq')
                .replace(/\\ngeq/g, '\\not\\geq')
                .replace(/\\nsim/g, '\\not\\sim')
                .replace(/\\lneqq/g, '\\underset{\\not=}<')
                .replace(/\\gneqq/g, '\\underset{\\not=}>')
                .replace(/\\nprec/g, '\\not\\prec')
                .replace(/\\nsucc/g, '\\not\\succ')
                .replace(/\\nsubseteq/g, '\\not\\subseteq')
                .replace(/\\nsupseteq/g, '\\not\\supseteq')
                .replace(/\\subsetneq/g, '\\underset{\\not-}\\subset')
                .replace(/\\supsetneq/g, '\\underset{\\not-}\\supset')
                .replace(/\\lnsim/g, '\\underset{\\not\\sim}<')
                .replace(/\\gnsim/g, '\\underset{\\not\\sim}>')
                .replace(/\\precnsim/g, '\\underset{\\not\\sim}\\prec')
                .replace(/\\succnsim/g, '\\underset{\\not\\sim}\\succ')
                .replace(/\\ntriangleleft/g, '\\not\\triangleleft')
                .replace(/\\ntriangleright/g, '\\not\\triangleright')
                .replace(/\\nmid/g, '\\not\\mid')
                .replace(/\\nparallel/g, '\\not\\parallel')
                .replace(/\\nvdash/g, '\\not\\vdash')
                //字母类符号
                //箭头
                .replace(/\\nleftarrow/g, '\\not\\leftarrow')
                .replace(/\\nrightarrow/g, '\\not\\rightarrow')
                .replace(/\\nLeftarrow/g, '\\not\\Leftarrow')
                .replace(/\\nRightarrow/g, '\\not\\Rightarrow')
                .replace(/\\nLeftrightarrow/g, '\\not\\Leftrightarrow')
                .replace(/\\leftleftarrows/g, '_\\leftarrow^\\leftarrow')
                .replace(/\\rightrightarrows/g, '_\\rightarrow^\\rightarrow')
                .replace(/\\upuparrows/g, '\\uparrow\\uparrow')
                .replace(/\\downdownarrows/g, '\\downarrow\\downarrow')
                .replace(/\\leftrightarrows/g, '_\\rightarrow^\\leftarrow')
                .replace(/\\rightleftarrows/g, '_\\leftarrow ^\\rightarrow')
                //组合
                .replace(/\{\\{ \}/g, '\\{')
                //(x)
                .replace(/\\left \(/g, '\{\(')
                .replace(/\\right \)/g, '\)\}')
                //[x]
                .replace(/\\left \[/g, '\{\[')
                .replace(/\\right \]/g, '\]\}')
                //|x|
                .replace(/\\left \|/g, '\{\|')
                .replace(/\\right \|/g, '\|\}')
                ;
            return $source;
        }

    } );

    function getService ( serviceName ) {

        var serviceObject =  this.services[ serviceName ];

        if ( !serviceObject ) {
            throw new Error( 'KFEditor: not found service, ' + serviceName );
        }

        return serviceObject;

    }

    Utils.extend( KFEditor, {

        registerComponents: function ( name, component ) {

            COMPONENTS[ name ] = component;

        }

    } );

    return KFEditor;

} );
