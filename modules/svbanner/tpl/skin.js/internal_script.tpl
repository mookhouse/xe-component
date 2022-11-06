<script type="text/javascript">
getUuid();
$(window).load(function() {
    if(checkImpression(jQuery('#banner_$%sUniqId$%'))) {
        checkDisplayed('banner_$%sUniqId$%', '$%nImpLogSrl$%');
    }
    $('#banner_$%sUniqId$%').click(function() {
        // console.log('banner clicked');
        checkClicked('$%nImpLogSrl$%', '$%nBannerSrl$%', '$%fDuplicatedClickLimitDay$%');
        window.location.href='$%sLandingUrl$%';
    });
});
$(window, window.document).scroll(function () {
    if(checkImpression(jQuery('#banner_$%sUniqId$%'))) {
        checkDisplayed('banner_$%sUniqId$%', '$%nImpLogSrl$%');
    }
});
</script>