jQuery(function($) {
    $(document).ready(function() {
        $.ajax({
            url: wp_vars.ajax_url,
            data: {
                action: "cn_get_sets"
            },
            type: "POST",
            timeout: 30000,
            success: function (res) {
                // $("#cn-loader").hide();
                $("#cn-sets-list").html(res);
                $(".cn-sets[value=all]").prop("checked", true).trigger("change");
            },
            error: function(){
                $("#cn-loader").hide();
            }
        });

        var selectedImage = {
            path: "",
            name: "",
            ext: "",
            size: "",
            dim: "",
            uploaded: ""
        };
        var ajaxTimeout = 30000,
            setId,
            pageNo,
            hasThumbs = true;

        $(document).on("click", ".cn-thumb", function() {
            selectedImage.path = $(this).data("cnpath");
            selectedImage.name = $(this).data("cnname");
            selectedImage.ext = $(this).data("cnext");
            selectedImage.size = $(this).data("cnsize");
            selectedImage.dim = $(this).data("cndim");
            selectedImage.domain = $(this).data("cndomain");
            selectedImage.uploaded = $(this).data("cnuploaded");
            $(".cn-thumb").removeClass("cn-active");
            $(this).addClass("cn-active");
            $("#cn-form-insert").removeAttr("disabled");

            clearForm();
            $("#cn-sidebar-right .cn-sidebar-wrap").show();
            $("#cn-form-img").attr("src", $(this).data("web"));
            $("#cn-form-name").text(selectedImage.name + '.' + selectedImage.ext);
            $("#cn-form-size").text(selectedImage.size);
            $("#cn-form-dim").text(selectedImage.dim);
            $("#cn-form-uploaded").text(selectedImage.uploaded);
            $("#cn-form-url").val(selectedImage.path);
            $("#cn-form-thumb-size option:last").text("Full Size - " + selectedImage.dim);
        });

        $(document).on("change", ".cn-sets", function() {
            setId = $(this).val();
            pageNo = 0;
            hasThumbs = true;
            getThumbs(pageNo);
        });

        $("#cn-content").on("scroll", function() {
            if($("#cn-content").scrollTop() ===  $("#cn-content")[0].scrollHeight - $("#cn-content").outerHeight()) {
                pageNo++;
                getThumbs(pageNo);
            }
        });

        var getThumbs = function(page) {
            if(hasThumbs) {
                $("#cn-loader").show();
                $("#cn-form-insert").attr("disabled", true);
                $.ajax({
                    url: wp_vars.ajax_url,
                    data: {
                        action: "get_files_by_set",
                        setId: setId,
                        keyword: $("#cn-form-search").val(),
                        pageNo: page ? page : 0
                    },
                    type: "POST",
                    timeout: ajaxTimeout,
                    success: function (res) {
                        if(!res) {
                            hasThumbs = false;
                        }
                        $("#cn-loader").hide();
                        if (page === 0) {
                            $("#cn-content").scrollTop(0);
                            $("#cn-content").html(res);
                        } else {
                            $("#cn-content").append(res);
                        }
                    },
                    error: function () {
                        $("#cn-loader").hide();
                    }
                });
            }
        };

        $("#cn-form-insert").on("click", function() {
            $("#cn-loader").show();
            var thumoType = $("#cn-form-thumb-types").val();
            var uploadUrl = selectedImage.domain + thumoType + '/' + selectedImage.name + '.' + selectedImage.ext;
            $.ajax({
                url: wp_vars.ajax_url,
                data: {
                    action: "cn_upload_image",
                    path: uploadUrl,
                    ext: selectedImage.ext,
                    name: selectedImage.name,
                    title: $("#cn-form-title").val(),
                    caption: $("#cn-form-caption").val(),
                    alt: $("#cn-form-alt").val(),
                    size: $("#cn-form-thumb-size").val()
                },
                type: "POST",
                timeout: ajaxTimeout,
                success: function (res) {
                    $("#cn-loader").hide();
                    var resObj = JSON.parse(res);
                    window.parent.send_to_editor(resObj['data']);
                },
                error: function(){
                    $("#cn-loader").hide();
                }
            });
        });

        var cnErrMsg = document.getElementById("cn-error-msg");
        if(!cnErrMsg){
            $.ajax({
                url: wp_vars.ajax_url,
                data: {
                    action: "check_creds"
                },
                type: "POST",
                timeout: ajaxTimeout,
                success: function (res) {
                    var resObj = JSON.parse(res);
                    if(resObj.status === 'OK') {
                        $("#cn-thumb-up").show();
                        $("#cn-thumb-down").hide();
                    } else if(resObj.status === 'error') {
                        $("#cn-thumb-down").show();
                        $("#cn-thumb-up").hide();
                    }
                },
                error: function(){
                    $("#cn-loader").hide();
                }
            });
        }

        function clearForm() {
            $("#cn-form-img").attr("src", "");
            $("#cn-form-name").text("");
            $("#cn-form-size").text("");
            $("#cn-form-dim").text("");
            $("#cn-form-uploaded").text("");
            $("#cn-form-url").val("");
            $("#cn-form-title").val("");
            $("#cn-form-caption").val("");
            $("#cn-form-alt").val("");
        }

        $("#cn-form-search").bind("enterKey",function(e){
            setId = "search";
            pageNo = 0;
            hasThumbs = true;
            getThumbs(pageNo);
        });
        $("#cn-form-search").keyup(function(e){
            if(e.keyCode == 13) {
                $(this).trigger("enterKey");
            }
        });
    })
});