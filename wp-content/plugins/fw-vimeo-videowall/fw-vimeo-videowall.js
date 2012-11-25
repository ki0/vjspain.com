jQuery(document).ready( function() {
        jQuery(".fwvvw_vthumb").live('click',
		function() {

			var idreq = jQuery(this).attr('id');
                        var expl_idreq = idreq.split('_');
                        var idvideo = expl_idreq[1];
                         jQuery("#fwvvw_full_video").remove();
                         jQuery("#fwvvw_bg_video").remove();

			var newdiv = document.createElement("div");
                        var insidediv = document.createElement("div");
                        var vcontain = document.getElementsByTagName("body");

                       newdiv.setAttribute('id','fwvvw_bg_video');
                       insidediv.setAttribute('id','fwvvw_full_video');
                       
                       jQuery(newdiv).append(insidediv);
                       jQuery(vcontain).append(newdiv);

			var responsediv = '#fwvvw_full_video';
			
			jQuery.post( fwvvw_ajax_handler, {
				
				action: 'show_video',
				'id': idvideo

				},
							
				function(response) {
					jQuery(responsediv).html(response);	
				});						
	});

        jQuery("img.closewindow").live('click',
		function() {
                     
                     jQuery("#fwvvw_full_video").remove();
                     jQuery("#fwvvw_bg_video").remove();
                });


         jQuery(".fwvvw_pagelink").live('click',
		function() {

			var idreq = jQuery(this).attr('id');
                        var expl_idreq = idreq.split('_');
                        var source = expl_idreq[1];
                        var idsrc = expl_idreq[2];
                        var typesrc = expl_idreq[3];
                        var w = expl_idreq[4];
                        var h = expl_idreq[5];
                        var number = expl_idreq[6];
                       
                        var page = expl_idreq[7];
                        
                       

			var responsediv = '#wall-fwvvw-'+source+'-'+idsrc;

			jQuery.post( fwvvw_ajax_handler, {

				action: 'show_page',
				'id': idsrc,
                                'source' : source,
                                'type' : typesrc,
                                'width' : w,
                                'height' : h,
                                'number' : number,
                               
                                'page' : page

				},

				function(response) {
					jQuery(responsediv).html(response);
				});
	});

}
);
