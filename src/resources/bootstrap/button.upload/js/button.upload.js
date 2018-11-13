

$(document).ready(function()
{
	$('form').on('reset', function()
	{
		$(this).find('.btn-upload').each(function()
		{
			$(this).removeClass('btn-success').find('span.badge').html('');
		});
	});
});



$(document).on('change', '.btn-upload :file', function()
{
	var input = $(this);
    var countFiles = input.get(0).files ? input.get(0).files.length : 1;
	input.parent('.btn-upload').addClass('btn-success').find('span.badge').html(countFiles);
});
