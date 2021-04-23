$("#announcements-icon").click(function () {
    $.post(read_announcements_endpoint);
    $(".announcements .activity-badge").remove();
});
