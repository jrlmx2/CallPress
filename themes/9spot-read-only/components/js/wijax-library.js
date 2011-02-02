/*
* Library file for Wijax
* 
* Contains several library functions for lazy-loading widgets.
* Widget loading code should not be placed here...
* 
* @author Vasken Hauri
* 
* Includes code written by Matthew Batchelder (borkweb@gmail.com)
*/

;(function($){

$.wijax = {
    load: function(url, type) {
        /******************************************************************
            Function: load
            Desc: loads external content
        *******************************************************************/
        $.getScript(url);
    },
		channelInit: function(el,url) {
				this.channelFetch(url, el.attr('id'));
    },
    channelFetch: function(url, unique_id) {
        var sep = url.indexOf('?') > -1 ? "&" : "?";
        var new_url = url + sep+'output_method=js&channel_id='+unique_id;
        if( document.location.toString().indexOf( 'https://' ) != -1 ) {
            new_url = new_url.replace(/http\:/g, 'https:');
        }
        
				this.load(new_url);
    },
    channelLoad: function(data,id,callback) {
				$('#'+id).prepend(data);
				if(callback) {
            cb_result = eval(callback);

            if( typeof(cb_result) == 'function' ) {
                cb_result(id);
            }
        }//end if
    }
		/* does not work as currently written...maybe add this later -vasken	
		channelRefresh: function(obj, url) {
        var id = $(obj).parents('div.channel-container').attr('id');
        this.channelFetch(url, id);
		},*/
};	

})(jQuery);
