	$(document).ready(function(){
		var bar = $('.bar');
		var percent = $('.percent');
		var showimg = $('#showimg');
		var progress = $(".progress");
		var files = $(".files");
		var btn = $(".btn span");
		$("#fileupload").wrap("<form id='myupload' action='action.php' method='post' enctype='multipart/form-data'></form>");
		$("#fileupload").change(function(){  //选择文件
			$("#myupload").ajaxSubmit({
				dataType:  'json',	//数据格式为json 
				beforeSend: function() {	//开始上传 
					showimg.empty();	//清空显示的图片
					progress.show();	//显示进度条
					var percentVal = '0%';	//开始进度为0%
					bar.width(percentVal);	//进度条的宽度
					percent.html(percentVal);	//显示进度为0% 
					btn.html("上传中...");	//上传按钮显示上传中
				},
				uploadProgress: function(event, position, total, percentComplete) {
					var percentVal = percentComplete + '%';	//获得进度
					bar.width(percentVal);	//上传进度条宽度变宽
					percent.html(percentVal);	//显示上传进度百分比
				},
				success: function(data) {	//成功
					//获得后台返回的json数据，显示文件名，大小，以及删除按钮
					files.html("<b>"+data.name+"("+data.size+"k)</b> <span class='delimg' rel='"+data.pic+"'>删除</span>");
					//显示上传后的图片
					var img = "upload/face/"+data.pic;
					//判断上传图片的大小 然后设置图片的高与宽的固定宽
					if (data.width>240 && data.height<240){
						showimg.html("<img src='"+img+"' id='cropbox' height='240' />");
					}else if(data.width<240 && data.height>240){
						showimg.html("<img src='"+img+"' id='cropbox' width='240' />");
					}else if(data.width<240 && data.height<240){
						showimg.html("<img src='"+img+"' id='cropbox' width='240' height='240' />");
					}else{
						showimg.html("<img src='"+img+"' id='cropbox' />");
					}
					//传给php页面，进行保存的图片值
					$("#src").val(img);
					//截取图片的js
					$('#cropbox').Jcrop({
						aspectRatio: 1,
						onSelect: updateCoords,
						minSize:[240,240],
						maxSize:[240,240],
						allowSelect:false, //允许选择
						allowResize:false, //是否允许调整大小
						setSelect: [ 0, 0, 240, 240 ]
					});
					btn.html("上传图片");	//上传按钮还原
				},
				error:function(xhr){	//上传失败
					btn.html("上传失败");
					bar.width('0')
					files.html(xhr.responseText);	//返回失败信息
				}
			});
		});
		
		$(".delimg").live('click',function(){
			var pic = $(this).attr("rel");
			$.post("action.php?act=delimg",{imagename:pic},function(msg){
				if(msg==1){
					files.html("删除成功.");
					showimg.empty();	//清空图片
					progress.hide();	//隐藏进度条 
				}else{
					alert(msg);
				}
			});
		});
		
	});
	
	function updateCoords(c){
		$('#x').val(c.x);
		$('#y').val(c.y);
		$('#w').val(c.w);
		$('#h').val(c.h);
	};
	
	function checkCoords(){
		if (parseInt($('#w').val())) return true;
		alert('Please select a crop region then press submit.');
		return false;
	};
