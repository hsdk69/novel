$(function(){
	$(".lazyimg").lazyload({threshold:100,effect:"fadeIn",skip_invisible:!1});

	$("#human").click(function(e) {
		$('.tipshover').show();
    	e.stopPropagation();
	});

	$('body').on('click',function(){
	    $('.tipshover').hide();
	});

	if($('.tipshover a').hasClass('cur')){
		$(".human").addClass('cur');
		$("#human").text($(".tipshover .cur").text());
	}

	$('.j-content-tab a span').click(function(event) {
		var index=$(this).parents().index();
		$('.j-content-tab a span').removeClass('on');
		$(this).addClass('on');
		$('.j-content-main').children("div").hide();
		$('.j-content-main').children("div").eq(index).show();
		if(index==1){
			$('.header').width(1200);
		}else{
			$('.header').width(950);
		}
	});

	$(".comment").click(function(event) {
		$('.j-content-tab a span').removeClass('on');
		$('.j-content-tab a span').eq(0).addClass('on');
		$('.j-content-main').children("div").hide();
		$('.j-content-main').children("div").eq(0).show();
		$('.header').width(950);
	});

	$('.isLogged').mouseDelay(false).hover(function() {
		$('.suspend').removeClass('hidden');
	});

	$('.suspend').hover(function() {
		$(this).removeClass('hidden');
	}, function() {
		$(this).addClass('hidden');
	});

    var topReturn = function() {
	    $("body").append('<div class="to_top"></div>');
	    $("body").find(".to_top").addClass("ui-toTop");
	    $(window).scroll(function() { 
	    	(document.documentElement.scrollTop || document.body.scrollTop) > 500 ? $(".to_top").fadeIn(300) : $(".to_top").fadeOut(300)
	    });
	    $(".to_top").hover(function() {
	        var t = $(".firstnav").css("background-color");
	        $(this).addClass("to_top_on").css("background-color",t)
	    },
	    function() {
	        $(this).removeClass("to_top_on").css("background-color", "#fff")
	    }), $(".to_top").on("click",
	    function() {
	        $(document).scrollTop(0)
	    })
	};

	topReturn();
});