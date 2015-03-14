<?php
/*
Plugin Name: Publish Confirmation
Plugin URI: http://www.bloggingbookshelf.com/wordpress/publish-confirmation-plugin/
Description: When you hit the "Publish" button, a small dialog box appears asking you if you're sure you want to publish the post.
Version: 1.0
Author: Tristan Higbee
Author URI: http://www.bloggingbookshelf.com
License: GPL2



*/


/*  Copyright 2010 Tristna Higbee from BloggingBookshelf.com (email : tristan@bloggingbookshelf.com)

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License, version 2, as 
    published by the Free Software Foundation.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/


$c_message = 'Are you SURE you want to publish this post?'; // This is the confirmation message that will appear.
function confirm_publish(){
	global $c_message;
	echo '
<script type="text/javascript"><!--
var publish = document.getElementById("publish");
if (publish !== null) publish.onclick = function(){
	return confirm("'.$c_message.'");
};
// --></script>';
}

add_action('admin_footer', 'confirm_publish');
?>