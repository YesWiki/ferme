/**
* @author Florestan Bredow <florestan.bredow@supagro.inra.fr>
*/


$(document).ready(function(){

	//Premiere image dans le header passe en background
	var image;
	image = $("header img.bgheader:first-child");
	if (!image) {
		image = $("header > div > span.attach_bgheader > img:first-child");
	}

	if (image) {
		$(image).remove();		
		$("header").css("background-image","url(" + $(image).attr("src") + ")");
		$("header").css("background-position", "center center");
		$("header").css("background-repeat", "no-repeat");
		$("header").css("background-size", "cover");
		//$("body").css("opacity", "1");
	}

});

/*$(document).ready(function(){
	
	$('span + a')
		.each(function(){
			
			$(this).html("Modifier");
			$(this).addClass("att_mod");


			$(this).prev("span")
				.prepend(this);

			//$(this).parent("span")
			//	.wrap("<div></div>");

			$(this).parent("span")
				.replaceWith('<div>' + $(this).parent("span").html() + '</div>');
		});

	$("a.att_mod").wrap("<div class='att_mod'></div>");
}); /**/

