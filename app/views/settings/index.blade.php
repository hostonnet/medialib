<form method="post" action="">
Autoforward Duration: <input name="autoforward_duration" value="<?php echo Settings::get('autoforward_duration') ?>"> (in Seconds. 0 = disable)<br>
skip_to_bookmark: <input name="skip_to_bookmark" value="<?php echo Settings::get('skip_to_bookmark') ?>"><br>
<input type="submit" name="submit" value="Save">
</form>