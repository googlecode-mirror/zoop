/*
 * Ext JS Library 1.1 Beta 2
 * Copyright(c) 2006-2007, Ext JS, LLC.
 * licensing@extjs.com
 * 
 * http://www.extjs.com/license
 */


Ext.Button=function(_1,_2){Ext.apply(this,_2);this.addEvents({"click":true,"toggle":true,"mouseover":true,"mouseout":true});if(this.menu){this.menu=Ext.menu.MenuMgr.get(this.menu);}if(_1){this.render(_1);}Ext.Button.superclass.constructor.call(this);};Ext.extend(Ext.Button,Ext.util.Observable,{hidden:false,disabled:false,pressed:false,tabIndex:undefined,enableToggle:false,menu:undefined,menuAlign:"tl-bl?",iconCls:undefined,type:"button",menuClassTarget:"tr",clickEvent:"click",handleMouseEvents:true,tooltipType:"qtip",render:function(_3){var _4;if(this.hideParent){this.parentEl=Ext.get(_3);}if(!this.dhconfig){if(!this.template){if(!Ext.Button.buttonTemplate){Ext.Button.buttonTemplate=new Ext.Template("<table border=\"0\" cellpadding=\"0\" cellspacing=\"0\" class=\"x-btn-wrap\"><tbody><tr>","<td class=\"x-btn-left\"><i>&#160;</i></td><td class=\"x-btn-center\"><em unselectable=\"on\"><button class=\"x-btn-text\" type=\"{1}\">{0}</button></em></td><td class=\"x-btn-right\"><i>&#160;</i></td>","</tr></tbody></table>");}this.template=Ext.Button.buttonTemplate;}_4=this.template.append(_3,[this.text||"&#160;",this.type],true);var _5=_4.child("button:first");_5.on("focus",this.onFocus,this);_5.on("blur",this.onBlur,this);if(this.cls){_4.addClass(this.cls);}if(this.icon){_5.setStyle("background-image","url("+this.icon+")");}if(this.iconCls){_5.addClass(this.iconCls);if(!this.cls){_4.addClass(this.text?"x-btn-text-icon":"x-btn-icon");}}if(this.tabIndex!==undefined){_5.dom.tabIndex=this.tabIndex;}if(this.tooltip){if(typeof this.tooltip=="object"){Ext.QuickTips.tips(Ext.apply({target:_5.id},this.tooltip));}else{_5.dom[this.tooltipType]=this.tooltip;}}}else{_4=Ext.DomHelper.append(Ext.get(_3).dom,this.dhconfig,true);}this.el=_4;if(this.id){this.el.dom.id=this.el.id=this.id;}if(this.menu){this.el.child(this.menuClassTarget).addClass("x-btn-with-menu");this.menu.on("show",this.onMenuShow,this);this.menu.on("hide",this.onMenuHide,this);}_4.addClass("x-btn");if(Ext.isIE&&!Ext.isIE7){this.autoWidth.defer(1,this);}else{this.autoWidth();}if(this.handleMouseEvents){_4.on("mouseover",this.onMouseOver,this);_4.on("mouseout",this.onMouseOut,this);_4.on("mousedown",this.onMouseDown,this);}_4.on(this.clickEvent,this.onClick,this);if(this.hidden){this.hide();}if(this.disabled){this.disable();}Ext.ButtonToggleMgr.register(this);if(this.pressed){this.el.addClass("x-btn-pressed");}if(this.repeat){var _6=new Ext.util.ClickRepeater(_4,typeof this.repeat=="object"?this.repeat:{});_6.on("click",this.onClick,this);}},getEl:function(){return this.el;},destroy:function(){Ext.ButtonToggleMgr.unregister(this);this.el.removeAllListeners();this.purgeListeners();this.el.remove();},autoWidth:function(){if(this.el){this.el.setWidth("auto");if(Ext.isIE7&&Ext.isStrict){var ib=this.el.child("button");if(ib&&ib.getWidth()>20){ib.clip();ib.setWidth(Ext.util.TextMetrics.measure(ib,this.text).width+ib.getFrameWidth("lr"));}}if(this.minWidth){if(this.hidden){this.el.beginMeasure();}if(this.el.getWidth()<this.minWidth){this.el.setWidth(this.minWidth);}if(this.hidden){this.el.endMeasure();}}}},setHandler:function(_8,_9){this.handler=_8;this.scope=_9;},setText:function(_a){this.text=_a;if(this.el){this.el.child("td.x-btn-center button.x-btn-text").update(_a);}this.autoWidth();},getText:function(){return this.text;},show:function(){this.hidden=false;if(this.el){this[this.hideParent?"parentEl":"el"].setStyle("display","");}},hide:function(){this.hidden=true;if(this.el){this[this.hideParent?"parentEl":"el"].setStyle("display","none");}},setVisible:function(_b){if(_b){this.show();}else{this.hide();}},toggle:function(_c){_c=_c===undefined?!this.pressed:_c;if(_c!=this.pressed){if(_c){this.el.addClass("x-btn-pressed");this.pressed=true;this.fireEvent("toggle",this,true);}else{this.el.removeClass("x-btn-pressed");this.pressed=false;this.fireEvent("toggle",this,false);}if(this.toggleHandler){this.toggleHandler.call(this.scope||this,this,_c);}}},focus:function(){this.el.child("button:first").focus();},disable:function(){if(this.el){this.el.addClass("x-btn-disabled");}this.disabled=true;},enable:function(){if(this.el){this.el.removeClass("x-btn-disabled");}this.disabled=false;},setDisabled:function(v){this[v!==true?"enable":"disable"]();},onClick:function(e){if(e){e.preventDefault();}if(e.button!=0){return;}if(!this.disabled){if(this.enableToggle){this.toggle();}if(this.menu&&!this.menu.isVisible()){this.menu.show(this.el,this.menuAlign);}this.fireEvent("click",this,e);if(this.handler){this.el.removeClass("x-btn-over");this.handler.call(this.scope||this,this,e);}}},onMouseOver:function(e){if(!this.disabled){this.el.addClass("x-btn-over");this.fireEvent("mouseover",this,e);}},onMouseOut:function(e){if(!e.within(this.el,true)){this.el.removeClass("x-btn-over");this.fireEvent("mouseout",this,e);}},onFocus:function(e){if(!this.disabled){this.el.addClass("x-btn-focus");}},onBlur:function(e){this.el.removeClass("x-btn-focus");},onMouseDown:function(e){if(!this.disabled&&e.button==0){this.el.addClass("x-btn-click");Ext.get(document).on("mouseup",this.onMouseUp,this);}},onMouseUp:function(e){if(e.button==0){this.el.removeClass("x-btn-click");Ext.get(document).un("mouseup",this.onMouseUp,this);}},onMenuShow:function(e){this.el.addClass("x-btn-menu-active");},onMenuHide:function(e){this.el.removeClass("x-btn-menu-active");}});Ext.ButtonToggleMgr=function(){var _17={};function toggleGroup(btn,_19){if(_19){var g=_17[btn.toggleGroup];for(var i=0,l=g.length;i<l;i++){if(g[i]!=btn){g[i].toggle(false);}}}}return{register:function(btn){if(!btn.toggleGroup){return;}var g=_17[btn.toggleGroup];if(!g){g=_17[btn.toggleGroup]=[];}g.push(btn);btn.on("toggle",toggleGroup);},unregister:function(btn){if(!btn.toggleGroup){return;}var g=_17[btn.toggleGroup];if(g){g.remove(btn);btn.un("toggle",toggleGroup);}}};}();
