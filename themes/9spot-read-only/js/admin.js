	jQuery(function(){
		jQuery('.row select').change(function(){
			var el = jQuery(this);
			var grid = parseInt(el.val());
			var row = el.parents('.row');

			var total_slots = 0;
			var max_slots = ninespot_max_slots;
			var hide_the_rest = false;
			
			jQuery('select', row).each(function(i){
				var select_box = jQuery(this);
				var box = jQuery('.block_' + (i+1), row);
				
				if(hide_the_rest){
					ninespot.colClearClass(box);
					box.addClass('grid_0');
				}
				else{
					var slot = parseInt(select_box.val());
					
					if(total_slots + slot <= max_slots){
						total_slots += slot;
						ninespot.colClearClass(box);
						box.addClass('grid_' + slot);
					}
					else{
						ninespot.colClearClass(box);
						box.addClass('grid_0');
						hide_the_rest = true;
					}
				}
			});
			
			if(total_slots == 0) row.addClass('empty');
			else row.removeClass('empty');
		});
	});
	
	var ninespot = {
		calcSize: function(elements, total_slots){
			if(!total_slots) total_slots = 0;
			elements.each(function(){
				var el = jQuery(this);
				if(total_slots < 16)
				{
					var slot = el.attr('class');
					slot = slot.match(/grid_([0-9]+)/);
					total_slots += parseInt(slot[1]);
				}//end if
				else
				{
					ninespot.colClearClass(el);
					el.addClass('grid_0');
				}//end else
			});
			
			return total_slots;
		},
		colClearClass: function(el){
			el.removeClass('grid_0');
			el.removeClass('grid_1');
			el.removeClass('grid_2');
			el.removeClass('grid_3');
			el.removeClass('grid_4');
			el.removeClass('grid_5');
			el.removeClass('grid_6');
			el.removeClass('grid_7');
			el.removeClass('grid_8');
			el.removeClass('grid_9');
			el.removeClass('grid_10');
			el.removeClass('grid_11');
			el.removeClass('grid_12');
			el.removeClass('grid_13');
			el.removeClass('grid_14');
			el.removeClass('grid_15');
			el.removeClass('grid_16');
		}
	};