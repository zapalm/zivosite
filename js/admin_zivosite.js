$(document).ready(function() {
	if ($('#widget_id_existence_off').val() == 1)
		showAutoRegOptions(false);
	else
		showAutoRegOptions(true);

	$('#widget_id_existence_off').click(function() {
		showAutoRegOptions(false);
	});

	$('#widget_id_existence_on').click(function() {
		showAutoRegOptions(true);
	});
});

function showAutoRegOptions(param)
{
	if (param) {
		$('#fieldset_1_1').show();
		$('#fieldset_2_2').hide();
	}
	else {
		$('#fieldset_1_1').hide();
		$('#fieldset_2_2').show();
	}
}