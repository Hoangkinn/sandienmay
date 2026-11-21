$('img.lazy-load').lazyload();

jQuery.ajaxSetup({
	headers: {
		'X-CSRF-TOKEN': jQuery('meta[name="csrf-token"]').attr('content')
	}
});

jQuery(document).ready(function($){
	if(window.matchMedia('(max-width: 1199px)').matches){
		$('.sort-select').on('click', function(){
			$(this).find('ul').slideToggle();
		});
	}
	if(window.matchMedia('(max-width: 991px)').matches){

	}

	if(window.matchMedia('(min-width: 1200px)').matches){
        if (! document.querySelector('body.home')){
            $('.danh-muc-box').hover(function () {
                clearTimeout($.data(this, 'timer'));
                $(this).children('.danh-muc-menu').stop(true, true).slideDown();
            }, function () {
                $.data(this, 'timer', setTimeout($.proxy(function() {
                    $(this).children('.danh-muc-menu').stop(true, true).slideUp();
                }, this), 100));
            });
        }
	}

	/* ---------------------------------------------------------------------- */
	/*	View Menu
	/* ---------------------------------------------------------------------- */
	$('.view-menu').on('click', function(e){
		if($('.main-menu').is(':hidden')){
			$('.main-menu').slideDown();
			$(this).html('<i class="fa fa-times"></i>');
		}else{
			$('.main-menu').slideUp();
			$(this).html('<i class="fa fa-list"></i>');
		}
	});

	/* ---------------------------------------------------------------------- */
	/*	View Menu Sub
	/* ---------------------------------------------------------------------- */
	$('.main-menu > ul > li.li-parent > a').append('<span class="view-sub-menu"><i class="fa fa-chevron-down"></i></span>');
	$('.view-sub-menu').on('click', function(e){
		e.preventDefault();
		var subMenu = $(this).parents('li').find('ul');
		if($(subMenu).is(':hidden')){
			$(subMenu).slideDown();
			$(this).html('<i class="fa fa-chevron-up"></i>');
		}else{
			$(subMenu).slideUp();
			$(this).html('<i class="fa fa-chevron-down"></i>');
		}
	});

	/* ---------------------------------------------------------------------- */
	/*	View Search
	/* ---------------------------------------------------------------------- */
	$('.view-search').on('click', function(e){
		$('.search-box').slideToggle();
	});

	/* ---------------------------------------------------------------------- */
	/*	Zing Tab
	/* ---------------------------------------------------------------------- */
	$('.tab-header a').on('click', function(e){
		e.preventDefault();
		var id = $(this).attr('href');
		$(this).parents('.zing-tab').find('.tab-header a').removeClass('active');
		$(this).addClass('active');
		$(this).parents('.zing-tab').find('.tab-content .tab').hide();
		$(this).parents('.zing-tab').find('.tab-content '+id).fadeIn();
	});

	var clickElem = $('a.accord-link');
    clickElem.on('click', function(e){
        e.preventDefault();

        var $this = $(this),
            parentCheck = $this.parents('.accord-elem'),
            accordItems = $('.accord-elem'),
            accordContent = $('.accord-content');

        if( !parentCheck.hasClass('active')) {
            accordContent.slideUp(300, function(){
                accordItems.removeClass('active');
            });
            parentCheck.find('.accord-content').slideDown(300, function(){
                parentCheck.addClass('active');
            });
        } else {
            accordContent.slideUp(300, function(){
                accordItems.removeClass('active');
            });
        }
        var windowTop = $(window).scrollTop();
        setTimeout(function(){
            tourTop = parentCheck.offset().top;
            if(windowTop - tourTop > 50){
                $('html, body').animate({scrollTop:tourTop},0);
            }
        }, 300);
    });

	/* ---------------------------------------------------------------------- */
	/*	Add Cart
	/* ---------------------------------------------------------------------- */
	$(document).on('click', '.btn-add-cart', function(e){
		e.preventDefault();
		var id = $(this).attr('data-id');
		if ( $(this).hasClass('added') ){
			$(this).removeClass('added');
			$('.btn-add-cart[data-id='+id+']').text('Đang xử lý ...');
			$.post( '/cart/remove/'+id, {}, function( data ) {
				var obj = Object.entries(data);
				$(".cart-mini span").html(obj.length);
				$('.btn-add-cart[data-id='+id+']').text('Thêm vào giỏ hàng');
			});
		}else{
			$(this).addClass('added');
			$('.btn-add-cart[data-id='+id+']').text('Đang xử lý ...');
			$.post( '/cart/add/'+id, {}, function( data ) {
				var obj = Object.entries(data);
				$(".cart-mini span").html(obj.length);
				$('.btn-add-cart[data-id='+id+']').text('Đã thêm vào giỏ hàng');
			});
		}
	});

	$('.btn-buy').click(function(e){
		e.preventDefault();
		var id = $(this).attr('data-id');
		$.post( '/cart/add/'+id, {}, function( data ) {
			window.location.href = '/gio-hang';
		});
	});

	$('.cart-qty').change(function(){
		var id = $(this).attr('data-id');
		var num = $(this).val();
		$('.cart_tong[data-id='+id+']').text('Đang xử lý ...');
		if (isNaN(parseInt(num)) || parseInt(num) <= 0) {
			return false;
		}
		$.post( '/cart/update/'+id, { quantity: num}, function( data ) {

			var carts = Object.entries(data);
			var total = 0;
			var thisTotal = 0;

			carts.forEach(function(cart) {
				let vat = Number.parseInt(cart[1].regular_price * 0.1);
				let quantity = Number.parseInt(cart[1].quantity);
				let regular_price = Number.parseInt(cart[1].regular_price);
				total += (cart[1].is_vat == 0 ? quantity * (regular_price + vat) : quantity * regular_price);

				if (cart[1].id == id) {
					thisTotal += cart[1].regular_price;
				}
			});

			$(".txtTotal").val(total);
			total = new Intl.NumberFormat().format(total);
			thisTotal = new Intl.NumberFormat().format(thisTotal);

			$(".cart-total span").html(total);
			$('.cart_tong[data-id='+id+']').html(thisTotal);
			if(carts.length == 0){
				location.reload();
			}
		});
	});

	$('.btn-delete-cart').click(function(e){
		e.preventDefault();
		var id = $(this).attr('data-id');
		$('.cart_tong[data-id='+id+']').text('Đang xử lý ...');
		$.post( '/cart/remove/'+id, { }, function( data ) {

			var obj = Object.entries(data);
			var total = 0;
			obj.forEach(element => total += element[1].quantity * element[1].regular_price);
			$(".txtTotal").val(total);
			total = new Intl.NumberFormat().format(total);
			$(".cart-total span").html(total);
			$('.cart_line[data-id='+id+']').remove();
			$(".cart-mini span").html(obj.length);
			if(obj.length == 0){
				location.reload();
			}
		});
	});

	/* ---------------------------------------------------------------------- */
	/*	Seach Suggets
	/* ---------------------------------------------------------------------- */
	$('.txt-search').keyup(function(){
		var name = $(this).val();
		$.get( '/ajax/search/product?query='+name, function( data ) {
			let html = '';

			if (data.suggestions && data.suggestions.length) {
				data.suggestions.forEach(function(product) {
					let imgProduct = '';
					if (product.image2 != null) {
						imgProduct = product.image2;
					}

					if (product.image != null) {
						imgProduct = "/storage/" + product.image;
					}

					let txtPrice = '';
					if (product.sale_price != null) {
						txtPrice = Intl.NumberFormat().format(product.sale_price) + 'đ';
					} else if (product.regular_price != null){
						txtPrice = Intl.NumberFormat().format(product.regular_price) + 'đ';
					} else {
						txtPrice = 'Liên hệ';
					}

					html += `<div class="suggest-item">
						<a href="/`+product.url+`"><img width="50" height="50" src="`+ imgProduct +`" /></a>
						<h2><a href="/`+product.url+`">`+product.value+`</a></h2>
						<span class="suggest-price">`+ txtPrice +`</span>
					</div>`;
				});
			}

			$('.suggest-box').html(html);
			$('.suggest-box').slideDown();
		});
	});

	$(document).mouseup(function (e)
	{
	    var container = $(".search-box");
	    if (!container.is(e.target)
	        && container.has(e.target).length === 0)
	    {
	        $('.suggest-box').hide();
	    }
	});

	/* menu image */
	$(".danh-muc-menu li a").on('click', function(e){
		var href = $(this).attr('href');
		if(href == '#'){
			e.preventDefault();
		}
	});
	var danh_muc_menu_height = $('.danh-muc-menu').outerHeight();
	$('.danh-muc-menu > ul > li > ul.sub-menu').css({
		'min-height' : danh_muc_menu_height + 'px'
	});

	$('.menu-disable > a').on('click', function(){
    	return false;
    });

	$(window).scroll(function() {
		if ($(this).scrollTop() > 200) {
			$('#scrollUp').show();
		} else {
			$('#scrollUp').hide();
		}
	});
	$('#scrollUp').click(function() {
		$('html, body').animate({
			scrollTop: 0
		});
		return false;
	});
});
