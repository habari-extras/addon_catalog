<pre>
<?php

foreach ( $posts as $post ) {
	echo "$post->title: $post->content <br />";
	foreach ( $post->versions as $version ) {
		echo "\t\tVersion $version->version ($version->status): $version->url\n\t\t\t" . str_replace( "\n", "\n\t\t\t", $version->description ) . "<br />";
	}
	echo "<br /><br /><br />";
}


?>
</pre>
