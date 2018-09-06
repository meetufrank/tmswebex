webpackJsonp([11],{220:function(t,e,a){"use strict";function o(t){d||a(813)}Object.defineProperty(e,"__esModule",{value:!0});var n=a(432),i=a.n(n);for(var r in n)"default"!==r&&function(t){a.d(e,t,function(){return n[t]})}(r);var s=a(815),l=(a.n(s),a(1)),d=!1,c=o,m=Object(l.a)(i.a,s.render,s.staticRenderFns,!1,c,"data-v-5dd01c18",null);m.options.__file="src\\views\\interface\\group.vue",e.default=m.exports},432:function(t,e,a){"use strict";function o(t){return t&&t.__esModule?t:{default:t}}Object.defineProperty(e,"__esModule",{value:!0});var n=a(48),i=o(n),r=a(27),s=o(r),l=function(t,e,a,o){return e("Button",{props:{type:"primary"},style:{margin:"0 5px"},on:{click:function(){t.formItem.id=a.id,t.formItem.name=a.name,t.formItem.hash=a.hash,t.formItem.image=a.image,t.formItem.description=a.description,t.modalSetting.show=!0,t.modalSetting.index=o}}},"编辑")},d=function(t,e,a,o){return e("Poptip",{props:{confirm:!0,title:"您确定要删除此接口分组么? 如当前分组下仍有接口，将自动划归于默认分组！",transfer:!0},on:{"on-ok":function(){s.default.get("InterfaceGroup/del",{params:{hash:a.hash}}).then(function(e){a.loading=!1,1===e.data.code?(t.tableData.splice(o,1),t.$Message.success(e.data.msg)):t.$Message.error(e.data.msg)})}}},[e("Button",{style:{margin:"0 5px"},props:{type:"error",placement:"top",loading:a.isDeleting}},"删除")])};e.default={name:"interface_group",data:function(){return{uploadUrl:"",uploadHeader:{},columnsList:[{title:"序号",type:"index",width:65,align:"center"},{title:"接口组名称",align:"center",key:"name"},{title:"接口组描述",align:"center",key:"description"},{title:"接口组热度",align:"center",key:"hot",width:120},{title:"接口组标识",align:"center",key:"hash",width:130},{title:"接口组状态",align:"center",key:"status",width:100},{title:"操作",align:"center",key:"handle",width:180,handle:["edit","delete"]}],tableData:[],tableShow:{currentPage:1,pageSize:10,listCount:0},searchConf:{type:"",keywords:"",status:""},modalSetting:{show:!1,loading:!1,index:0},formItem:{description:"",name:"",hash:"",image:"",id:0},ruleValidate:{name:[{required:!0,message:"接口组名称不能为空",trigger:"blur"}]}}},created:function(){this.init(),this.getList()},methods:{init:function(){var t=this;this.uploadUrl=i.default.baseUrl+"Index/upload",this.uploadHeader={ApiAuth:sessionStorage.getItem("apiAuth")},this.columnsList.forEach(function(e){e.handle&&(e.render=function(e,a){var o=t.tableData[a.index];return e("div",[l(t,e,o,a.index),d(t,e,o,a.index)])}),"status"===e.key&&(e.render=function(e,a){var o=t.tableData[a.index];return e("i-switch",{attrs:{size:"large"},props:{"true-value":1,"false-value":0,value:o.status},on:{"on-change":function(e){s.default.get("InterfaceGroup/changeStatus",{params:{status:e,id:o.id}}).then(function(e){var a=e.data;1===a.code?t.$Message.success(a.msg):-14===a.code?(t.$store.commit("logout",t),t.$router.push({name:"login"})):(t.$Message.error(a.msg),t.getList()),t.cancel()})}}},[e("span",{slot:"open"},"启用"),e("span",{slot:"close"},"禁用")])}),"hot"===e.key&&(e.render=function(e,a){var o=t.tableData[a.index];if(o.hot>1e4){return e("span",(parseInt(o.hot)/1e4).toFixed(1)+"万")}return e("span",o.hot)})})},alertAdd:function(){var t=this;s.default.get("InterfaceList/getHash").then(function(e){var a=e.data;1===a.code?t.formItem.hash=a.data.hash:-14===a.code?(t.$store.commit("logout",t),t.$router.push({name:"login"})):t.$Message.error(a.msg)}),this.modalSetting.show=!0},submit:function(){var t=this,e=this;this.$refs.myForm.validate(function(a){if(a){e.modalSetting.loading=!0;var o="";o=0===t.formItem.id?"InterfaceGroup/add":"InterfaceGroup/edit",s.default.post(o,e.formItem).then(function(t){1===t.data.code?e.$Message.success(t.data.msg):e.$Message.error(t.data.msg),e.getList(),e.cancel()})}})},cancel:function(){this.modalSetting.show=!1},changePage:function(t){this.tableShow.currentPage=t,this.getList()},changeSize:function(t){this.tableShow.pageSize=t,this.getList()},search:function(){this.tableShow.currentPage=1,this.getList()},getList:function(){var t=this;s.default.get("InterfaceGroup/index",{params:{page:t.tableShow.currentPage,size:t.tableShow.pageSize,type:t.searchConf.type,keywords:t.searchConf.keywords,status:t.searchConf.status}}).then(function(e){var a=e.data;1===a.code?(t.tableData=a.data.list,t.tableShow.listCount=a.data.count):-14===a.code?(t.$store.commit("logout",t),t.$router.push({name:"login"})):t.$Message.error(a.msg)})},handleImgFormatError:function(t){this.$Notice.warning({title:"文件类型不合法",desc:t.name+"的文件类型不正确，请上传jpg或者png图片。"})},handleImgRemove:function(){this.formItem.image=""},handleImgSuccess:function(t){1===t.code?(this.$Message.success(t.msg),this.formItem.image=t.data.fileUrl):this.$Message.error(t.msg)},handleImgMaxSize:function(t){this.$Notice.warning({title:"文件大小不合法",desc:t.name+"太大啦请上传小于5M的文件。"})},doCancel:function(t){t||(this.formItem.id=0,this.formItem.image="",this.$refs.myForm.resetFields(),this.modalSetting.loading=!1,this.modalSetting.index=0)}}}},813:function(t,e,a){var o=a(814);"string"==typeof o&&(o=[[t.i,o,""]]),o.locals&&(t.exports=o.locals);var n=a(13).default;n("d458b51c",o,!1,{})},814:function(t,e,a){e=t.exports=a(12)(!1),e.push([t.i,"\n.api-box[data-v-5dd01c18] {\n  max-height: 300px;\n  overflow: auto;\n  border: 1px solid #dddee1;\n  border-radius: 5px;\n  padding: 10px;\n}\n.demo-upload-list[data-v-5dd01c18] {\n  display: inline-block;\n  width: 60px;\n  height: 60px;\n  text-align: center;\n  line-height: 60px;\n  border: 1px solid transparent;\n  border-radius: 4px;\n  overflow: hidden;\n  background: #fff;\n  position: relative;\n  box-shadow: 0 1px 1px rgba(0, 0, 0, 0.2);\n  margin-right: 4px;\n}\n.demo-upload-list img[data-v-5dd01c18] {\n  width: 100%;\n  height: 100%;\n}\n.demo-upload-list-cover[data-v-5dd01c18] {\n  display: none;\n  position: absolute;\n  top: 0;\n  bottom: 0;\n  left: 0;\n  right: 0;\n  background: rgba(0, 0, 0, 0.6);\n}\n.demo-upload-list:hover .demo-upload-list-cover[data-v-5dd01c18] {\n  display: block;\n}\n.demo-upload-list-cover i[data-v-5dd01c18] {\n  color: #fff;\n  font-size: 20px;\n  cursor: pointer;\n  margin: 0 2px;\n}\n",""])},815:function(t,e,a){"use strict";Object.defineProperty(e,"__esModule",{value:!0});var o=function(){var t=this,e=t.$createElement,a=t._self._c||e;return a("div",[a("Row",[a("Col",{attrs:{span:"24"}},[a("Card",{staticStyle:{"margin-bottom":"10px"}},[a("Form",{attrs:{inline:""}},[a("FormItem",{staticStyle:{"margin-bottom":"0"}},[a("Select",{staticStyle:{width:"100px"},attrs:{clearable:"",placeholder:"请选择状态"},model:{value:t.searchConf.status,callback:function(e){t.$set(t.searchConf,"status",e)},expression:"searchConf.status"}},[a("Option",{attrs:{value:1}},[t._v("启用")]),t._v(" "),a("Option",{attrs:{value:0}},[t._v("禁用")])],1)],1),t._v(" "),a("FormItem",{staticStyle:{"margin-bottom":"0"}},[a("Select",{staticStyle:{width:"100px"},attrs:{clearable:"",placeholder:"请选择类别"},model:{value:t.searchConf.type,callback:function(e){t.$set(t.searchConf,"type",e)},expression:"searchConf.type"}},[a("Option",{attrs:{value:1}},[t._v("接口组标识")]),t._v(" "),a("Option",{attrs:{value:2}},[t._v("接口组名称")])],1)],1),t._v(" "),a("FormItem",{staticStyle:{"margin-bottom":"0"}},[a("Input",{attrs:{placeholder:""},model:{value:t.searchConf.keywords,callback:function(e){t.$set(t.searchConf,"keywords",e)},expression:"searchConf.keywords"}})],1),t._v(" "),a("FormItem",{staticStyle:{"margin-bottom":"0"}},[a("Button",{attrs:{type:"primary"},on:{click:t.search}},[t._v("查询/刷新")])],1)],1)],1)],1)],1),t._v(" "),a("Row",[a("Col",{attrs:{span:"24"}},[a("Card",[a("p",{staticStyle:{height:"32px"},attrs:{slot:"title"},slot:"title"},[a("Button",{attrs:{type:"primary",icon:"plus-round"},on:{click:t.alertAdd}},[t._v("新增")])],1),t._v(" "),a("div",[a("Table",{attrs:{columns:t.columnsList,data:t.tableData,border:"","disabled-hover":""}})],1),t._v(" "),a("div",{staticClass:"margin-top-15",staticStyle:{"text-align":"center"}},[a("Page",{attrs:{total:t.tableShow.listCount,current:t.tableShow.currentPage,"page-size":t.tableShow.pageSize,"show-elevator":"","show-sizer":"","show-total":""},on:{"on-change":t.changePage,"on-page-size-change":t.changeSize}})],1)])],1)],1),t._v(" "),a("Modal",{attrs:{width:"668",styles:{top:"30px"}},on:{"on-visible-change":t.doCancel},model:{value:t.modalSetting.show,callback:function(e){t.$set(t.modalSetting,"show",e)},expression:"modalSetting.show"}},[a("p",{staticStyle:{color:"#2d8cf0"},attrs:{slot:"header"},slot:"header"},[a("Icon",{attrs:{type:"information-circled"}}),t._v(" "),a("span",[t._v(t._s(t.formItem.id?"编辑":"新增")+"接口组")])],1),t._v(" "),a("Form",{ref:"myForm",attrs:{rules:t.ruleValidate,model:t.formItem,"label-width":80}},[a("FormItem",{attrs:{label:"组名称",prop:"name"}},[a("Input",{attrs:{placeholder:"请输入接口组名称"},model:{value:t.formItem.name,callback:function(e){t.$set(t.formItem,"name",e)},expression:"formItem.name"}})],1),t._v(" "),a("FormItem",{attrs:{label:"组头像",prop:"image"}},[t.formItem.image?a("div",{staticClass:"demo-upload-list"},[a("img",{attrs:{src:t.formItem.image}}),t._v(" "),a("div",{staticClass:"demo-upload-list-cover"},[a("Icon",{attrs:{type:"ios-trash-outline"},nativeOn:{click:function(e){t.handleImgRemove()}}})],1)]):t._e(),t._v(" "),t.formItem.image?a("input",{directives:[{name:"model",rawName:"v-model",value:t.formItem.image,expression:"formItem.image"}],attrs:{type:"hidden",name:"image"},domProps:{value:t.formItem.image},on:{input:function(e){e.target.composing||t.$set(t.formItem,"image",e.target.value)}}}):t._e(),t._v(" "),t.formItem.image?t._e():a("Upload",{staticStyle:{display:"inline-block",width:"58px"},attrs:{type:"drag",action:t.uploadUrl,headers:t.uploadHeader,format:["jpg","jpeg","png"],"max-size":5120,"on-success":t.handleImgSuccess,"on-format-error":t.handleImgFormatError,"on-exceeded-size":t.handleImgMaxSize}},[a("div",{staticStyle:{width:"58px",height:"58px","line-height":"58px"}},[a("Icon",{attrs:{type:"camera",size:"20"}})],1)])],1),t._v(" "),a("FormItem",{attrs:{label:"组标识",prop:"hash"}},[a("Input",{staticStyle:{width:"300px"},attrs:{disabled:""},model:{value:t.formItem.hash,callback:function(e){t.$set(t.formItem,"hash",e)},expression:"formItem.hash"}}),t._v(" "),a("Badge",{staticStyle:{"margin-left":"5px"},attrs:{count:"系统自动生成，不允许修改"}})],1),t._v(" "),a("FormItem",{attrs:{label:"组描述",prop:"description"}},[a("Input",{attrs:{autosize:{maxRows:10,minRows:4},type:"textarea",placeholder:"请输入接口组描述"},model:{value:t.formItem.description,callback:function(e){t.$set(t.formItem,"description",e)},expression:"formItem.description"}})],1)],1),t._v(" "),a("div",{attrs:{slot:"footer"},slot:"footer"},[a("Button",{staticStyle:{"margin-right":"8px"},attrs:{type:"text"},on:{click:t.cancel}},[t._v("取消")]),t._v(" "),a("Button",{attrs:{type:"primary",loading:t.modalSetting.loading},on:{click:t.submit}},[t._v("确定")])],1)],1)],1)},n=[];o._withStripped=!0,e.render=o,e.staticRenderFns=n}});