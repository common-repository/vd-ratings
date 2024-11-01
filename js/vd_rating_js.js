(function ($) {
        $('input.check').on('change', function () {
			
			//alert('Rating: ' + $(this).val());
			var rate = $(this).val();
			var pid = $(this).attr('data-id');
			var $this = $(this);
			$.ajax({
				url:vd_ratings_obj.ajaxurl,
				data:{'rate':rate,'action':'vd_rating','pid':pid},
				success:function(data){		
					$this.rating('rate',data);
					$this.next('.label').text(data);
				}
			});
        }); 
        /*$('.rating').each(function () {
          $('<span class="label label-default"></span>')
            .text($(this).val() || ' ')
            .insertAfter(this);
        });*/

}( jQuery));
