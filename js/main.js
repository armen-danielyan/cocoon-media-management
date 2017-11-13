jQuery(function($) {
    $(document).ready(function() {
        var selectedImage = {
            path: "",
            name: "",
            ext: ""
        };
        var ajaxTimeout = 10000;
        var setId;

        $(document).on("click", ".cn-thumb", function() {
            selectedImage.path = $(this).data("cnpath");
            selectedImage.name = $(this).data("cnname");
            selectedImage.ext = $(this).data("cnext");
            $(".cn-thumb").removeClass("cn-active");
            $(this).addClass("cn-active");
            $("#cn-form-insert").removeAttr("disabled");

            clearForm();
            $("#cn-sidebar-right-wrap").show();
            $("#cn-form-img").attr("src", $(this).data("web"));
            $("#cn-form-name").text(selectedImage.name);
            $("#cn-form-url").val(selectedImage.path);
        });

        $(document).on("change", ".cn-sets", function() {
            setId = $(this).val();
            getThumbs();
        });

        var getThumbs = function(page) {
            $("#cn-loader").show();
            $("#cn-form-insert").attr("disabled", true);
            $.ajax({
                url: wp_vars.ajax_url,
                data: {
                    action: "get_files_by_set",
                    setId: setId,
                    page: page ? page : 1
                },
                type: "POST",
                success: function (res) {
                    $("#cn-loader").hide();
                    $("#cn-content").html(res);
                }
            });
        };


        $("#cn-form-insert").on("click", function() {
            $("#cn-loader").show();
            $.ajax({
                url: wp_vars.ajax_url,
                data: {
                    action: "cn_upload_image",
                    path: selectedImage.path,
                    ext: selectedImage.ext,
                    name: selectedImage.name,
                    title: $("#cn-form-title").val(),
                    caption: $("#cn-form-caption").val(),
                    alt: $("#cn-form-alt").val()
                },
                type: "POST",
                success: function (res) {
                    $("#cn-loader").hide();
                    var resObj = JSON.parse(res);
                    window.parent.send_to_editor(resObj['data']);
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
                success: function (res) {
                    var resObj = JSON.parse(res);
                    if(resObj.status === 'OK') {
                        $("#cn-thumb-up").show();
                        $("#cn-thumb-down").hide();
                    } else if(resObj.status === 'error') {
                        $("#cn-thumb-down").show();
                        $("#cn-thumb-up").hide();
                    }
                }
            });
        }

        function clearForm() {
            $("#cn-form-img").attr("src", "");
            $("#cn-form-name").text("");
            $("#cn-form-url").val("");
            $("#cn-form-title").val("");
            $("#cn-form-caption").val("");
            $("#cn-form-alt").val("");
        }

        $("#cn-form-search-submit").on("click", function() {
            $("#cn-loader").show();
            $("#cn-form-insert").attr("disabled", true);
            $.ajax({
                url: wp_vars.ajax_url,
                data: {
                    action: "cn_search",
                    keyword: $("#cn-form-search").val()
                },
                type: "POST",
                success: function (res) {
                    $("#cn-loader").hide();
                    $("#cn-content").html(res);
                    console.log(res);
                }
            })
        })
    })
});