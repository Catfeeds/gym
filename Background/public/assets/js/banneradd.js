    function change(){
        if ($("#change").text() == "添加" || $("#change").text() == "点击设置") {
            $("#changeArticleThumb").css("opacity","1");
            $("#changeArticleThumb").css("filter","Alpha(opacity=1)");
            $("#changeArticleThumb").css("position","relative");
            $('#change').text('取消添加');
            $('#change').css('background-color',"#eeeeee");
        }else if ($("#change").text() == "取消添加") {
            $("#changeArticleThumb").css("opacity","0");
            $("#changeArticleThumb").css("filter","Alpha(opacity=0)");
            $("#changeArticleThumb").css("position","absolute");
            $('#change').text('添加');
            $('#change').css('background-color',"#cccccc");
        }
    }

    $(function(){
        var $list = $("#fileList");
        // 优化retina, 在retina下这个值是2
        var ratio = window.devicePixelRatio || 1;   
        // 缩略图大小
        var thumbnailWidth = 430 * ratio;
        var thumbnailHeight = 200 * ratio;
        // 初始化Web Uploader
        var uploader = WebUploader.create({
            // 选完文件后，是否自动上传。
            auto: true,
            // swf文件路径
            swf: '__ROOT__/public/static/lib/webuploader/0.1.5/Uploader.swf',
            // 文件接收服务端。
            server: 'addBanner',
            // 选择文件的按钮。可选。
            // 内部根据当前运行是创建，可能是input元素，也可能是flash.
            pick: '#filePicker',
            formData: { 
            //设置传入服务器的参数变量 
            //注意不要在此赋值 
        　　　　banner_sort:$('#banner_sort option:selected').val(),
               banner_active:$('#banner_active option:selected').val(),
            }, 
            // 只允许选择图片文件。
            accept: {
                title: 'Images',
                extensions: 'gif,jpg,jpeg,bmp,png',
                mimeTypes: 'image/*'
            },
            thumb: {
                width: 110,
                height: 110,
                // 图片质量，只有type为`image/jpeg`的时候才有效。  
                quality: 100,
                // 是否允许放大，如果想要生成小图的时候不失真，此选项应该设置为false.  
                allowMagnify: true,
                // 是否允许裁剪。是否采用裁剪模式。如果采用这样可以避免空白内容。  
                crop: true,
                // 为空的话则保留原有图片格式。  
                // 否则强制转换成指定的类型。  
                type: ''
            },
            disableGlobalDnd: true,
            fileNumLimit: 1,
            fileSizeLimit: 200 * 1024,    // 200 K
            fileSingleSizeLimit: 200 * 1024    // 200 K
        });
        // uploader.options ={formData:"userName":"吉安娜","gender":"女"};
        // 当有文件添加进来的时候
        uploader.on( 'fileQueued', function( file ) {
            var $li = $(
                '<div id="' + file.id + '" class="file-item thumbnail">' +
                '<img>' +
                '<div class="info">' + file.name + '</div>' +
                '</div>'
                ),
                $img = $li.find('img');
            // $list为容器jQuery实例
            $list.append( $li );
            // 创建缩略图
            // 如果为非图片文件，可以不用调用此方法。
            // thumbnailWidth x thumbnailHeight 为 100 x 100
            uploader.makeThumb( file, function( error, src ) {
                if ( error ) {
                    $img.replaceWith('<span>不能预览</span>');
                    return;
                }
                $img.attr( 'src', src );
            }, 400, 200);
        });
        // uploadBeforeSend事件可以允许在上传前修改formData的数据
        uploader.on( 'uploadBeforeSend', function( object, data, header ) {
            // 添加data可以控制发送哪些携带数据。
            data.banner_sort = $('#banner_sort option:selected').val();
            data.banner_active = $('#banner_active option:selected').val();
        });
        // 文件上传过程中创建进度条实时显示
        uploader.on( 'uploadProgress', function( file, percentage ) {
            var $li = $( '#'+file.id ),
                $percent = $li.find('.progress span');
            // 避免重复创建
            if ( !$percent.length ) {
                $percent = $('<p class="progress"><span></span></p>')
                    .appendTo( $li )
                    .find('span');
            }
            $percent.css( 'width', percentage * 100 + '%' );
        });
        // 文件上传成功，给item添加成功class, 用样式标记上传成功。
        uploader.onUploadSuccess = function(file,response){
            $( '#'+file.id ).addClass('upload-state-done');
            layer.msg('Banner添加成功!',{icon:1,time:1200});
            // 添加对应的css
            // $("#image").attr("src",response.result);
             // $("#image").show();
            // 添加成功之后将bannershow 隐藏
            setTimeout(function(){
                // $('#bannershow').hide();
                $('#filePicker').hide();
            },1000);
            setTimeout("location.replace(location.href)", 1400);
        };
        uploader.on("error", function (type) {
            if (type == "Q_EXCEED_SIZE_LIMIT") {
                layer.msg("图片大小不能超过200K");
            }else {
                layer.msg("上传出错！请检查后重新上传！错误代码"+type);
            }
        });
        // 文件上传失败，显示上传出错。
        uploader.on( 'uploadError', function( file ) {
            var $li = $( '#'+file.id ),
                $error = $li.find('div.error');
            // 避免重复创建
            if ( !$error.length ) {
                $error = $('<div class="error"></div>').appendTo( $li );
            }
            $error.text('上传失败');
        });
        // 完成上传完了，成功或者失败，先删除进度条。
        uploader.on( 'uploadComplete', function( file ) {
            $( '#'+file.id ).find('.progress').remove();
        });
    })