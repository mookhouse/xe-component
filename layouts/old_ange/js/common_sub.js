(function($) {		
    // lnb 메뉴 클릭 시 active 클래스
    $(document).on("click", "#lnb-list > li > a", function(e) {
        if ($(this).parent().hasClass('active')) {
            $(this).parent().removeClass('active');
        } else {
            $(this).parent().siblings('.active').removeClass('active');
            $(this).parent().addClass('active');
        }
    });

    // aside 버튼 클릭시 aside-opne 클래스
    $(document).on('click', '#aside .btn-open .btn-default', function() {
        $('#aside').addClass('aside-open');
    });
    $(document).on('click', '.aside-open .btn-open .btn-default', function() {
        $('#aside').removeClass('aside-open');
    });
}(jQuery));