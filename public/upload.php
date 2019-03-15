<?php
    session_start();
    $sessionId = session_id();
?>
<!DOCTYPE html>
<!--[if IE 8]> <html lang="zh" class="ie8 no-js"> <![endif]-->
<!--[if IE 9]> <html lang="zh" class="ie9 no-js"> <![endif]-->
<!--[if !IE]><!-->
<html lang="zh">
<!--<![endif]-->
<!-- BEGIN HEAD -->
<head>
<meta charset="utf-8"/>
<title>员工管理中心</title>
<meta name="renderer" content="webkit">
<meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
<meta content="width=device-width, initial-scale=1.0" name="viewport"/>
<meta http-equiv="Content-type" content="text/html; charset=utf-8">
<meta content="" name="description"/>

<!-- <link href="https://fonts.useso.com/css?family=Open+Sans:400,300,600,700&subset=all" rel="stylesheet" type="text/css"/> -->
<link href="./static/assets/plugins/font-awesome/css/font-awesome.min.css" rel="stylesheet" type="text/css"/>
<link href="./static/assets/plugins/simple-line-icons/simple-line-icons.min.css" rel="stylesheet" type="text/css"/>
<link href="./static/assets/plugins/bootstrap/css/bootstrap.min.css" rel="stylesheet" type="text/css"/>
<link href="./static/assets/plugins/uniform/css/uniform.default.css" rel="stylesheet" type="text/css"/>
<link href="./static/assets/plugins/bootstrap-switch/css/bootstrap-switch.min.css" rel="stylesheet" type="text/css"/>
<link href="./static/assets/css/components.css" rel="stylesheet" type="text/css"/>
<link href="./static/assets/css/plugins.css" rel="stylesheet" type="text/css"/>


<link href="./static/assets/themes/default/css/tasks.css" rel="stylesheet" type="text/css"/>
<link href="./static/assets/plugins/fullcalendar/fullcalendar.min.css" rel="stylesheet" type="text/css"/>

<!-- modal对话框样式 -->
<link href="./static/assets/plugins/bootstrap-modal/css/bootstrap-modal.css" rel="stylesheet" type="text/css"/>
<!-- modal对话框样式 end -->

<!-- 滑出式消息框样式 -->
<link rel="stylesheet" type="text/css" href="./static/assets/plugins/bootstrap-toastr/toastr.min.css"/>
<!-- 滑出式消息框样式 end -->

<!-- 树状菜单样式 end -->

<link href="./static/assets/themes/default/css/layout.css" rel="stylesheet" type="text/css"/>
<link href="./static/assets/themes/default/css/default.css" rel="stylesheet" type="text/css"/>


<!-- datatables样式 -->
<link rel="stylesheet" type="text/css" href="./static/assets/plugins/datatables/plugins/bootstrap/dataTables.bootstrap.min.css"/>
<!-- datatables样式 固定列 -->
<!-- select2下拉框样式 -->
<link rel="stylesheet" type="text/css" href="./static/assets/plugins/select2/select2.css"/>
<!-- datatables样式 固定表头 -->
<link rel="stylesheet" type="text/css" href="./static/assets/plugins/datatables/extensions/FixedHeader/css/dataTables.fixedHeader.min.css"/>

<!-- 图片裁剪样式 -->
<link href="./static/assets/plugins/jcrop/css/jquery.Jcrop.min.css" rel="stylesheet"/>
<!-- 图片裁剪样式 end -->

<!-- datepicker样式 -->
<link rel="stylesheet" type="text/css" href="./static/assets/plugins/bootstrap-datepicker/css/bootstrap-datepicker3.min.css"/>
<link rel="stylesheet" type="text/css" href="./static/assets/plugins/bootstrap-datetimepicker/css/bootstrap-datetimepicker.min.css"/>
<!-- datepicker样式 end -->
<link rel="shortcut icon" href="favicon.ico"/>
<!-- js都放在</body>之前,根据实际需求可将部分js(例如:jquery.min.js)放到到<head></head>中 -->
<script src="./static/assets/plugins/jquery.min.js" type="text/javascript"></script>

<!-- 核心js,不要修改 -->
     <!--[if lte IE 9]>
  <div style="color: #8a6d3b; background-color: #fcf8e3;border-color: #faebcc;padding:20px;text-align:center;">
    不支持低版本的IE浏览器！ 请 <a target="_blank" href="http://windows.microsoft.com/zh-cn/internet-explorer/download-ie" style="color:#ff6600;text-decoration:underline;">升级到最新版本的IE浏览器</a> 或使用其它非IE内核浏览器 <a target="_blank" href="http://chrome.360.cn/" style="color:#ff6600;text-decoration:underline;">360极速浏览器</a>。
  </div>
  <script>
document.write('<script type="text/undefined">');
  </script>
<![endif]> -->

<script src="./static/assets/plugins/jquery-migrate.min.js" type="text/javascript"></script>
<script src="./static/assets/plugins/jquery-ui/jquery-ui.min.js" type="text/javascript"></script>
<script src="./static/assets/plugins/bootstrap/js/bootstrap.min.js" type="text/javascript"></script>
<script src="./static/assets/plugins/bootstrap-hover-dropdown/bootstrap-hover-dropdown.min.js" type="text/javascript"></script>
<script src="./static/assets/plugins/jquery-slimscroll/jquery.slimscroll.min.js" type="text/javascript"></script>
<script src="./static/assets/plugins/jquery.blockui.min.js" type="text/javascript"></script>
<script src="./static/assets/plugins/jquery.cokie.min.js" type="text/javascript"></script>
<script src="./static/assets/plugins/uniform/jquery.uniform.min.js" type="text/javascript"></script>
<script src="./static/assets/plugins/bootstrap-switch/js/bootstrap-switch.min.js" type="text/javascript"></script>
<script src="./static/assets/plugins/arttemplate/template.js"></script>
<!-- 核心js end -->

<script type="text/javascript" src="./static/assets/plugins/select2/select2.min.js"></script><!-- select2下拉框 -->
<script type="text/javascript" src="./static/assets/plugins/bootstrap-datepicker/js/bootstrap-datepicker.min.js"></script><!-- 日期控件 -->
<script type="text/javascript" src="./static/assets/plugins/moment/moment.js"></script>
<script type="text/javascript" src="./static/assets/plugins/moment/locale/zh-cn.js"></script>
<script type="text/javascript" src="./static/assets/plugins/bootstrap-datetimepicker/js/bootstrap-datetimepicker.min.js"></script><!-- 日期控件 -->
<script type="text/javascript" src="./static/assets/plugins/bootstrap-modal/js/bootstrap-modalmanager.js"></script><!-- 模态框 -->
<script type="text/javascript" src="./static/assets/plugins/bootstrap-modal/js/bootstrap-modal.js"></script><!-- 模态框 -->
<script type="text/javascript" src="./static/assets/plugins/jquery-validation/js/jquery.validate.min.js"></script><!-- 表单验证 -->
<script type="text/javascript" src="./static/assets/plugins/jquery-validation/js/additional-methods.min.js"></script><!-- 表单验证 -->
<script type="text/javascript" src="./static/assets/plugins/jquery.form.js"></script><!-- 表单提交 -->
<script src="./static/assets/scripts/submit.js" type="text/javascript"></script> <!-- 表单提交新方法 -->

<script type="text/javascript" src="./static/assets/plugins/datatables/media/js/jquery.dataTables.js"></script><!-- dataTables -->
<script type="text/javascript" src="./static/assets/plugins/datatables/plugins/bootstrap/dataTables.bootstrap.js"></script><!-- dataTables -->
<script type="text/javascript" src="./static/assets/plugins/datatables/extensions/FixedHeader/js/dataTables.fixedHeader.js"></script><!-- dataTables 固定表头 -->

<script src="./static/assets/scripts/datatable.js" type="text/javascript"></script>

<script type="text/javascript" src="./static/assets/plugins/bootstrap-toastr/toastr.min.js"></script><!-- 滑出式消息框 -->
<script src="./static/assets/plugins/jquery.pulsate.min.js" type="text/javascript"></script> <!-- 震动提示 -->
<script type="text/javascript" src="./static/assets/plugins/bootbox/bootbox.min.js"></script><!-- 模态框对话框 -->
<script src="./static/assets/plugins/jquery-bootpag/jquery.bootpag.min.js" type="text/javascript"></script><!-- 模态框对话框 -->
<script src="./static/assets/plugins/bootstrap-confirmation/bootstrap-confirmation.min.js" type="text/javascript"></script><!-- 模态框对话框 -->

<script src="./static/assets/plugins/autosize/autosize.min.js" type="text/javascript"></script>

<!-- 上传插件 start -->
<script type="text/javascript" src="./static/assets/plugins/plupload/js/plupload.full.js" charset="UTF-8"></script><!-- 上传核心 -->
<script type="text/javascript" src="./static/assets/plugins/jcrop/js/jquery.Jcrop.min.js"></script><!-- 图片裁剪 -->
<script src="./static/assets/scripts/upload.js" type="text/javascript"></script> <!-- 上传&裁剪打包 -->
<!-- 上传插件 end -->

<!-- 模板加载 -->
<script src="./static/assets/scripts/template.load.js"></script>

<!-- global js -->
<script src="./static/assets/scripts/global.js" type="text/javascript"></script>
<!-- global js end -->

<!-- 多级select联动 -->
<script src="./static/assets/scripts/mcselect.js"></script>

	<script src="./static/assets/scripts/calc.js" type="text/javascript"></script>
<script src="./static/assets/themes/default/scripts/layout.js" type="text/javascript"></script>

<!-- end js -->

</head>

<body class="page-header-fixed page-footer-fixed page-container-bg-solid page-sidebar-fixed page-sidebar-closed-hide-logo">
<!-- begin head -->
<!-- head end -->

<div class="clearfix"> </div>

  <!-- 模块内容区域 -->
  <div class="page-content-wrapper">
    <div class="page-content">
      <div class="page-content-body"><!-- scroller" style="height:500px;-->
          <script type="text/javascript" src="./static/assets/plugins/uploadify/jquery.uploadify.min.js"></script>
          <link href="./static/assets/plugins/uploadify/uploadify.css" rel="stylesheet" type="text/css" />
          <link href="./static/assets/plugins/uploadify/common.css" rel="stylesheet" type="text/css" />

          <!--内容-->
          <div class="portlet light">
              <div class="portlet-title">
                  <div class="caption"><input type='file' name='file' class='btn blue' id='fileField'></div>
              </div>
              <div class="portlet-body">
                  <div class="form-body clearfix">
                      <form action="/classManage/addClassPicture.json" id="form_edit" class="form-horizontal form-row-seperated" method="post">
                          <!--基本信息-->
                          <div class="form-group form-md-line-input">
                              <label class="col-md-2 control-label">图片列表</label>
                              <div class="col-md-10 imgList">

                              </div>
                          </div>
                          <div class="form-actions">
                              <div class="row">
                                  <div class="col-md-offset-2 col-md-9">
                                      <replace value="classId">
                                          <input type="hidden" name="classId" id="formhtml" value="{classId}"/>
                                      </replace>
                                      <button type="submit" class="btn blue" id="subBtn">提交</button>
                                      <button type="button" class="btn default" data-dismiss="modal">取消</button>
                                  </div>
                              </div>
                          </div>
                      </form>
                      <!--基本信息-->
                  </div>
              </div>
          </div>
          <!--内容-->
          <script>
              //上传文件
              /* 初始化上传插件 */
              var random = Math.random();
              var PHPSESSID = '<replace value="PHPSESSID">{PHPSESSID}</replace>';
              $("#fileField").uploadify({
                  "swf"             : "./static/assets/plugins/uploadify/uploadify.swf?ver=" + random,
                  "fileObjName"     : "file",
                  'cancelImg'		  : './static/assets/plugins/uploadify/cancel.png',
                  "buttonText"      : "添加图片",
                  "uploader"        : "http://api.xfnauto.com/publics_v2/upload?type=image&ver=" + random,
                  "width"           : 80,
                  "height"          : 34,
                  'removeTimeout'   : 1,
                  'formData'        : {'PHPSESSID': PHPSESSID},
                  'onInit'		  : init,
                  'multi'			  : true,
                  "onUploadSuccess" : uploadSuccess,
                  'onFallback' : function() {
                      alert('未检测到兼容版本的Flash.');
                  },
                  'onUploadError'   : function(file, errorCode, errorMsg, errorString) {
                      alert('The file ' + file.name + ' could not be uploaded: ' + errorString);
                  }
              });
              function init(){
                  $('#upload-file, #upload-file-queue').css('display','inline-block');
              }

              /* 文件上传成功回调函数 */
              function uploadSuccess(file, data){
                  var data = $.parseJSON(data);
                  var html = '';
                  if(data.status == 'success'){
                      html += "<div style='float:left;'>";
                      html +=		"<div style='margin:10px;' class='image'>";
                      html +=			"<a href='" + data.filename + "' alt='" + data.name + "' target='_blank'><img class='img-reponsetive' src='" + data.filename + "' width='145px' height='100px'></a>";
                      html +=		'</div>';
                      html +=		"<div style='margin:10px;font-size:16px;'>";
                      html +=			"<span class='title'><input type='text' name='title[]' class='form-control input-sm' placeholder='照片描述'></span>";
                      html +=			"<input type='hidden' name='filename[]' value='" + data.savename + "'>";
                      html +=		'</div>';
                      html +=	'</div>';
                      $('.imgList').append(html);
                  }
              }
          </script>
          <script src="./static/assets/themes/default/scripts/addClassPicture.js" type="text/javascript"></script>
          <!-- end 内容-->
      </div>
    </div>
  </div>
    <!-- end 模块内容区域 -->

</div>
<!-- end 内容区域 -->
<div></div>
<!-- begin footer -->
<div class="page-footer">
    <div class="page-footer-inner">
        <div class="copyright"></div>
    </div>
    <div class="scroll-to-top"> <i class="icon-arrow-up"></i> </div>
</div>
<!-- end footer -->

<!-- 模态框预加载-->
<div class="modal fade modal-scroll" id="global-modal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true" data-replace="true">

    <div class="modal-dialog">
        <div class="modal-content" id="global-modal-content"></div>
        <!-- /.modal-content -->
    </div>
    <!-- /.modal-dialog -->
</div>
<!-- /.modal -->
<!-- end 模态框预加载 -->
<!--模态框用于多个模态框调用-->
<div class="modal fade modal-scroll bs-modal-lg" id="another-modal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true" data-replace="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                <h4 class="modal-title"></h4>
            </div>
            <div class="modal-body">
            </div>
            <div class="modal-footer display-hide">
                <button type="button" data-dismiss="modal" class="btn default"><i class="fa fa-rotate-left"></i> 关闭</button>
            </div>
        </div>
    </div>
</div>
<!-- 加载js,在这里加载为了提高页面打开速度;必要情况下可将js放到<head></head>之间,视实际情况而定 -->
</body>
<!-- end body -->


<script id="sitemsg-modal-temp" type="text/html">
    <div class="panel-body">
        <div class="portlet">
            <div class="portlet-title">
                <div class="caption">
                    <i class="fa fa-comment-o"></i>{{title}}
                </div>
                <div class="tools">
                    <a href="javascript:;" class="remove" data-dismiss="modal" aria-hidden="true"> </a>
                </div>
            </div>
            <div class="portlet-body">
                <p>{{content}}</p>

                <div class="clearfix">
              <span class="pull-right">
                <small>时间： <cite title="Source Title"> {{ctime}}</cite></small>
              </span>
                </div>
            </div>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn default" data-dismiss="modal">关闭</button>
        </div>
    </div>
</script>

<script>
    $.ajax({
        'dataType': 'JSON',
        'url': '/public/menu.json',
        'async': false,
        'success': function(data){
            if(data.id == '1001'){
                var topMenu = '', sidebar = '';
                for(var i=0, l=data.info.menu.length;i<l;i++){
                    var subdomain = data.info.menu[i].url;
                    var active = (data.info.menu[i].root == reRoot) ? 'active' : '';
                    var selected = (data.info.menu[i].root == reRoot) ? '<span class="selected"> </span>' : '';
                    topMenu += '<li class="classic-menu-dropdown '+active+'"> <a href="'+ subdomain +'"> '+data.info.menu[i].name+ selected + '  </a> ';

                    if(data.info.menu[i].root == reRoot && data.info.menu[i].children){//当前站点
                        //$('#currentSubdomain').html(data.info.menu[i].name);
                        var sidebarData = data.info.menu[i].children;
                        for(var k=0, len=sidebarData.length;k<len;k++){
                            var start = k == 0 ? 'menustart' : '';
                            var last = (k == (len - 1)) ? 'last' : '';
                            sidebar += '<li class="'+start+last+'" id="menu_'+sidebarData[k].id+'">';
                            if(sidebarData[k].children){//有子菜单
                                var children = sidebarData[k].children;
                                sidebar += '<a href="javascript:void(0);"> <i class="fa fa-map-marker"></i> <span class="title"> '+sidebarData[k].name+' </span> <span class="selected "> </span> <span class="arrow "> </span> </a>';
                                sidebar += '<ul class="sub-menu">';
                                for(var j=0, jen=children.length;j<jen;j++){
                                    sidebar += '<li class="'+start+last+'" id="menu_'+children[j].id+'"> <a href="'+children[j].url+'" class="ajaxify"> <i class="fa fa-angle-right"></i> <span class="title"> '+children[j].name+' </span> </a> </li>';
                                }
                                sidebar += '</ul>';

                            }else{//没有子菜单
                                sidebar += ' <a href="'+sidebarData[k].url+'" class="ajaxify"> <i class="fa fa-user"></i> <span class="title"> '+sidebarData[k].name+' </span> </a>';
                            }
                            sidebar += '</li>';
                        }
                    }
                };
                $('.page-sidebar-menu').append($(sidebar));
                $('#topMenu').html($(topMenu));
            }else{

            }
        }
    });
</script>

</html>

