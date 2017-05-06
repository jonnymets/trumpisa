$(document).ready(function() {
	//$("#word-data").load("/words/data");	
});

function word_go(form)
{
	$("#word-done").hide();
	
	//validate form
	var word = jQuery.trim($("#word").val());
	if(word == "")
	{
		swal({
			title: "Whoops..",
			text: "Don't forget to type in your word(s).",
			type: "error",
			timer: 2000,
			showConfirmButton: false
		});
		
		return false;
	}
	else if(word.length < 3)
	{
		swal({
			title: "Whoops..",
			text: "Only one or two characters? You can do better than that.",
			type: "error",
			timer: 2500,
			showConfirmButton: false
		});
		
		return false;
	}
	else
	{
		var word_split = word.split(' ');
		if(word_split.length > 2 || word.length > 75)
		{
			reqs_error();
			return false;
		}
	}
	
	//load up robot protection
	grecaptcha.execute();
	
	return false;
}

function word_go_go(token)
{
	$("word-btn").button("loading");
	
    $.ajax({
        type: "POST",
        url: "/words/new",
        data: {word : $("#word").val(), token: token},
		dataType: "json",
        success: function(resp)
		{
			$("word-btn").button("reset");
			grecaptcha.reset();
			
			if(resp.success)
			{
				$("#word-data").load("/words/data");
				$("#word").val("");
				if(resp.data.count > 1)
				{
					$("#word-people span").text(resp.data.count);
					$("#word-people").show();					
				}
				else
					$("#word-person").show();					
				$("#word-done").show();
			}
			else if(resp.error_code == "reqs")
				reqs_error();				
			else
				ajax_error();
		},
		error: ajax_error
    });
}

function ajax_error(resp)
{
	$("word-btn").button("reset");
	swal({
		title: "Doh!",
		text: "Something went wrong here. Try again when you can.",
		type: "error"
	});	
}

function reqs_error()
{
	$("word-btn").button("reset");
	swal({
		title: "Regulation is good sometimes.",
		text: "We found this is more fun when we limit entries to just 2 words and a maximum of 75 characters.",
		type: "error"
	});	
}