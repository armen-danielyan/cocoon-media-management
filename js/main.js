jQuery(function($) {
    $(document).ready(function($) {
        $('.cn-sets').on("change", function() {
            $.ajax({
                url: wp_vars.ajax_url,
                data: {
                    action: "get_files_by_set",
                    setId: $(this).val()
                },
                type: "POST",
                success: function (res) {
                    $("#cn-content").html(res);
                }
            });
        })
    })
});