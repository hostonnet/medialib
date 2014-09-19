<script type="text/javascript">var media_volume = {{ $media->volume }};</script>
<script language="JavaScript" type="text/javascript" src="/js/watch.js"></script>
<script language="JavaScript" type="text/javascript" src="/js/jquery.autocomplete.js"></script>
<script language="JavaScript" type="text/javascript" src="/js/textarea_caret.js"></script>

<p>
    <?php echo $media->file_name; ?> [<span id="media_id">{{ $media->id }}</span>]
    [Likes: {{ $media->likes }}]
    [Views: {{ $media->views }}]
    <?php if ($ref_page == 'random-search') echo ' ' . Playlist::getTotalVideos(Playlist::getId('WATCH')); ?>
    <span id="edit_button" class="btn btn-success btn-sm">Edit</span>
</p>

<form method="post" action="/media/save" id="media_edit" class="form-horizontal">


<div id="watch_col_form">
        <input type="hidden" name="media_id" value="{{ $media->id }}">
        <input type="hidden" name="ref_page" value="{{ $ref_page }}">
        <textarea name="description" id="txt_description" rows="5" cols="70" disabled>{{ Tags::sort_bookmark($media->description) }}</textarea>
</div>

<div id="watch_col_player">
    <embed type="application/x-vlc-plugin" name="VLC" id="vlcp"
           autoplay="no"
           loop="no"
           toolbar="no"
           volume="100"
           width="720"
           height="406"
           target="file:///{{ Config::get('app.video_folder') }}/tmp/live.m3u">

	<br />

	<a id=playbutton href="#">Play</a> &nbsp;
	<a id=pausebutton href="#">Pause</a> &nbsp;
	<a id=seekten href="#">Seek 10</a> &nbsp;
	<a id=seek_ten href="#">Seek -10</a> &nbsp;
	<a id=thumb_link href="#">Thumb</a> &nbsp;
    <a id=media_info  href="#" alt="{{ $media->id }}">Info</a>

	<div id="watch_time">
        <div id="currentTime">00:00:00.000</div> &nbsp; &nbsp;
        <div id="wt_2"><?php echo "$media->time_start_hms - $media->time_end_hms"; ?>  &nbsp; <?php echo $last_viewed; ?></div>
        <div id="skip_time_remaining"></div>
        <div id="skip_time_more"></div>
    </div>

    <div>{{ Tags::get_bookmarks($media->description, $media->id) }}</div>

    <div id="watch_controls">
        <div class="form-inline">
            <input type="number" id="volume_input" name="volume" value="<?php echo $media->volume; ?>" min=20 max=200 class="form-control" style="width:90px">
            <input type="submit" name="submit" value="Next >>" class="btn btn-default">
            {{ $videos_in_playlist }}
        </div>
    </div>


</div>


</forum>

<?php

$auto_forward = Settings::get('auto_forward');
$auto_play = Medias::isAutoPlay($ref_page);
$time_start_ms = 0;
$time_start = '';
$skip_to_bookmark_done = 0;

if ($auto_play)
{
    $skip_to_bookmark = Settings::get('skip_to_bookmark');
    $autoForwardDuration = Settings::get('autoForwardDuration');
    $autoforward =  ($autoForwardDuration > 0) ? 1 : 0;

    if (strlen($skip_to_bookmark) > 2)
    {
        if ($skip_to_bookmark == 'auto')
        {
            $media_tag_time = DB::table('media_tag_time')->where('media_id','=',$media->id)->first();
            $time_start = $media_tag_time->time_start;
        }
        else
        {
            $skip_to_bookmark_done = 1;
            $time_start = Tags::getTagTime($media->description, $skip_to_bookmark, $media->id);
        }
    }
    else if (strpos($ref_page, 'x'))
    {
        $ref_page_parts = explode('x',$ref_page);
        $pm_id = $ref_page_parts[1];
        $media_tag_time_record = DB::table('playlist_media')->where('pm_id','=',$pm_id)->first();
        $time_start = $media_tag_time_record->pm_time_start;
    }

    $time_start_ms = Time::hmsms2ms($time_start);
    $time_end_ms = $time_start_ms + ($autoForwardDuration * 1000);

}

?>


<div id="media_info_display"></div>

<script type="text/javascript">
jQuery(function () {
	var ac_tags;
    ac_tags = $('#txt_description').autocomplete({
        width: 150,
        delimiter: /(,|;|=)\s*/,
	    serviceUrl: '/ajax_tag_suggest'
    });
});
</script>

<?php

if ($time_start_ms > 10 && $autoforward) {

?>

<script type="text/javascript">
jQuery(function () {

    var gotEvent = false;

    player.playlist.play();

	$("#playbutton").html('Stop');

	function seekOnStart(event) {
        gotEvent = true;
		setTimeout(function() { player.input.time = <?php echo $time_start_ms; ?>;  }, 400);
    }

    end_time = <?php echo $time_end_ms; ?>;
	player.addEventListener('MediaPlayerOpening', seekOnStart, false);

    // check if we got event, if not reload
    setTimeout(function() {  if (gotEvent==false) location.reload(); }, 200);

    $('#skip_time_more').html('more');

    $('#skip_time_more').click(function() {
        end_time = end_time + <?php echo $autoForwardDuration * 1000; ?>;
    });

});

</script>

<?php

}

if ($skip_to_bookmark_done == 1)
{

?>

<script type="text/javascript">
jQuery(function () {

    var description = "<?php echo $media->description; ?>";
    var current_playlist_tag_time = "<?php echo $time_start; ?>";
    var bookmarks = description.split("|");
    var bookmark = '';
    var num_bookmakrs = bookmarks.length;

    for (var i = 0; i < num_bookmakrs; i++)
    {
        bookmark = bookmarks[i];
        var bookmark_parts = bookmark.split("=");
        var bookmark_time = $.trim(bookmark_parts[0]);

        if (bookmark.indexOf("<?php echo $skip_to_bookmark; ?>") != -1)
        {
            if (bookmark_time == current_playlist_tag_time)
            {
                $("a[alt='" + bookmark_time + "']").addClass("current_playlist_tag current_playlist_tag_more");
            }
            else
            {
                $("a[alt='" + bookmark_time + "']").addClass("current_playlist_tag_more");
            }
        }
    }

});
</script>
<?php
}
?>
