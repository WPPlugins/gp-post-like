jQuery(document).ready(function() {
 
    jQuery(".post-like a").click(function(){
     
        like_button = jQuery('.post-like a');
		liked_msg = jQuery('.liked_msg');
     
        // Retrieve post ID from data attribute
        post_id = like_button.data("post_id");
         
        // Ajax call
        jQuery.ajax({
            type: "post",
            url: ajaxurl,
            data: "action=gppl_post_like&nonce="+nonce+"&post_like=&post_id="+post_id,
            success: function(count){
                // If vote successful
                if(count != "already")
                {
                    like_button.addClass("voted");
                    like_button.siblings(".count").text(count);
                }
				if(count == "already")
                {
                    liked_msg.html('<span>You have Already liked this post</span>');
					liked_msg.fadeIn(500);
					liked_msg.fadeOut(3500);
                }
            }
        });
         
        return false;
    })
})