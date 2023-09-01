jQuery(document).ready(function ($) {
	if (atmSaviorproParams.is_admin) {
		if(window.location.href.indexOf("atm_saviorpro_") == -1) {
			jQuery("#skeletabsTab1").trigger("click");
		}
		/*Load all roles from discord server*/
		$.ajax({
			type: "POST",
			dataType: "JSON",
			url: atmSaviorproParams.admin_ajax,
			data: { 'action': 'atm_saviorpro_discord_load_discord_roles', 'atm_discord_nonce': atmSaviorproParams.atm_discord_nonce, },
			beforeSend: function () {
				$(".saviorpro-discord-roles .spinner").addClass("is-active");
				$(".initialtab.spinner").addClass("is-active");
			},
			success: function (response) {
				if (response != null && response.hasOwnProperty('code') && response.code == 50001 && response.message == 'Missing Access') {
					$(".saviorpro-btn-connect-to-bot").show();
				} else if (response == null || response.message == '401: Unauthorized' || response.hasOwnProperty('code') || response == 0) {
					$("#saviorpro-connect-discord-bot").show().html("Error: Please check all details are correct").addClass('error-bk');
				} else {
					if ($('.atm-tabs button[data-identity="level-mapping"]').length) {
						$('.atm-tabs button[data-identity="level-mapping"]').show();
					}
					$("#saviorpro-connect-discord-bot").show().html("Bot Connected <i class='fab fa-discord'></i>").addClass('not-active');

					var activeTab = localStorage.getItem('activeTab');
					if ($('.atm-tabs button[data-identity="level-mapping"]').length == 0 && activeTab == 'level-mapping') {
						$('.atm-tabs button[data-identity="settings"]').trigger('click');
					}
					$.each(response, function (key, val) {
						var isbot = false;
						if (val.hasOwnProperty('tags')) {
							if (val.tags.hasOwnProperty('bot_id')) {
								isbot = true;
							}
						}

						if (key != 'previous_mapping' && isbot == false && val.name != '@everyone') {
							$('.saviorpro-discord-roles').append('<div class="makeMeDraggable" style="background-color:#'+val.color.toString(16)+'" data-saviorpro_role_id="' + val.id + '" >' + val.name + '</div>');
							$('#saviorpro-defaultRole').append('<option value="' + val.id + '" >' + val.name + '</option>');
							makeDrag($('.makeMeDraggable'));
						}
					});
					var defaultRole = $('#selected_default_role').val();
					if (defaultRole) {
						$('#saviorpro-defaultRole option[value=' + defaultRole + ']').prop('selected', true);
					}

					if (response.previous_mapping) {
						var mapjson = response.previous_mapping;
					} else {
						var mapjson = localStorage.getItem('saviorpro_mappingjson');
					}

					$("#saviorpro_maaping_json_val").html(mapjson);
					$.each(JSON.parse(mapjson), function (key, val) {
						var arrayofkey = key.split('id_');
						var preclone = $('*[data-saviorpro_role_id="' + val + '"]').clone();
						if(preclone.length>1){
							preclone.slice(1).hide();
						}
						//$('*[data-saviorpro_level_id="' + arrayofkey[1] + '"]').append(preclone).attr('data-drop-saviorpro_role_id', val).find('span').css({ 'order': '2' });
						if (jQuery('*[data-saviorpro_level_id="' + arrayofkey[1] + '"]').find('*[data-saviorpro_role_id="' + val + '"]').length == 0) {
							$('*[data-saviorpro_level_id="' + arrayofkey[1] + '"]').append(preclone).attr('data-drop-saviorpro_role_id', val).find('span').css({ 'order': '2' });
						}
						if ($('*[data-saviorpro_level_id="' + arrayofkey[1] + '"]').find('.makeMeDraggable').length >= 1) {
							$('*[data-saviorpro_level_id="' + arrayofkey[1] + '"]').droppable("destroy");
						}
						// if (jQuery('*[data-saviorpro_level_id="' + arrayofkey[1] + '"]').find('.makeMeDraggable').length >= 1) {
						// 	$('*[data-saviorpro_level_id="' + arrayofkey[1] + '"]').droppable("destroy");
						// }
						preclone.css({ 'width': '100%', 'left': '0', 'top': '0', 'margin-bottom': '0px', 'order': '1' }).attr('data-saviorpro_level_id', arrayofkey[1]);
						makeDrag(preclone);
					});
				}

			},
			error: function (response) {
				$("#saviorpro-connect-discord-bot").show().html("Error: Please check all details are correct").addClass('error-bk');
				console.error(response);
			},
			complete: function () {
				$(".saviorpro-discord-roles .spinner").removeClass("is-active").css({ "float": "right" });
				$("#skeletabsTab1 .spinner").removeClass("is-active").css({ "float": "right", "display": "none" });
			}
		});


		/*Clear log log call-back*/
		$('#saviorpro-clrbtn').click(function (e) {
			e.preventDefault();
			$.ajax({
				url: atmSaviorproParams.admin_ajax,
				type: "POST",
				data: { 'action': 'atm_saviorpro_discord_clear_logs', 'atm_discord_nonce': atmSaviorproParams.atm_discord_nonce, },
				beforeSend: function () {
					$(".clr-log.spinner").addClass("is-active").show();
				},
				success: function (data) {
					if (data.error) {
						// handle the error
						alert(data.error.msg);
					} else {
						$('.error-log').html("Clear logs Sucesssfully !");
					}
				},
				error: function (response) {
					console.error(response);
				},
				complete: function () {
					$(".clr-log.spinner").removeClass("is-active").hide();
				}
			});
		});

		/*Flush settings from local storage*/
		$("#revertMapping").on('click', function () {
			localStorage.removeItem('saviorpro_mapArray');
			localStorage.removeItem('saviorpro_mappingjson');
			window.location.href = window.location.href;
		});

		/*Create droppable element*/
		function init() {
			$('.makeMeDroppable').droppable({
				drop: handleDropEvent,
				hoverClass: 'hoverActive',
			});
			$('.saviorpro-discord-roles-col').droppable({
				drop: handlePreviousDropEvent,
				hoverClass: 'hoverActive',
			});
		}

		$(init);

		/*Create draggable element*/
		function makeDrag(el) {
			// Pass me an object, and I will make it draggable
			el.draggable({
				revert: "invalid",
				helper: 'clone',
				start: function(e, ui) {
				ui.helper.css({"width":"45%"});
				}
			});
		}

		/*Handel droppable event for saved mapping*/
		function handlePreviousDropEvent(event, ui) {
			var draggable = ui.draggable;
			if(draggable.data('saviorpro_level_id')){
				$(ui.draggable).remove().hide();
			}
			$(this).append(draggable);
			$('*[data-drop-saviorpro_role_id="' + draggable.data('saviorpro_role_id') + '"]').droppable({
				drop: handleDropEvent,
				hoverClass: 'hoverActive',
			});
			$('*[data-drop-saviorpro_role_id="' + draggable.data('saviorpro_role_id') + '"]').attr('data-drop-saviorpro_role_id', '');

			var oldItems = JSON.parse(localStorage.getItem('saviorpro_mapArray')) || [];
			$.each(oldItems, function (key, val) {
				if (val) {
					var arrayofval = val.split(',');
					if (arrayofval[0] == 'saviorpro_level_id_' + draggable.data('saviorpro_level_id') && arrayofval[1] == draggable.data('saviorpro_role_id')) {
						delete oldItems[key];
					}
				}
			});
			var jsonStart = "{";
			$.each(oldItems, function (key, val) {
				if (val) {
					var arrayofval = val.split(',');
					if (arrayofval[0] != 'saviorpro_level_id_' + draggable.data('saviorpro_level_id') || arrayofval[1] != draggable.data('saviorpro_role_id')) {
						jsonStart = jsonStart + '"' + arrayofval[0] + '":' + '"' + arrayofval[1] + '",';
					}
				}
			});
			localStorage.setItem('saviorpro_mapArray', JSON.stringify(oldItems));
			var lastChar = jsonStart.slice(-1);
			if (lastChar == ',') {
				jsonStart = jsonStart.slice(0, -1);
			}

			var saviorpro_mappingjson = jsonStart + '}';
			$("#saviorpro_maaping_json_val").html(saviorpro_mappingjson);
			localStorage.setItem('saviorpro_mappingjson', saviorpro_mappingjson);
			draggable.css({ 'width': '100%', 'left': '0', 'top': '0', 'margin-bottom': '10px' });
		}

		/*Handel droppable area for current mapping*/
		function handleDropEvent(event, ui) {
			var draggable = ui.draggable;
			var newItem = [];
			var newClone = $(ui.helper).clone();
			if($(this).find(".makeMeDraggable").length >= 1){
				return false;
			}
			$('*[data-drop-saviorpro_role_id="' + newClone.data('saviorpro_role_id') + '"]').droppable({
				drop: handleDropEvent,
				hoverClass: 'hoverActive',
			});
			$('*[data-drop-saviorpro_role_id="' + newClone.data('saviorpro_role_id') + '"]').attr('data-drop-saviorpro_role_id', '');
			if ($(this).data('drop-saviorpro_role_id') != newClone.data('saviorpro_role_id')) {
				var oldItems = JSON.parse(localStorage.getItem('saviorpro_mapArray')) || [];
				$(this).attr('data-drop-saviorpro_role_id', newClone.data('saviorpro_role_id'));
				newClone.attr('data-saviorpro_level_id', $(this).data('saviorpro_level_id'));

				$.each(oldItems, function (key, val) {
					if (val) {
						var arrayofval = val.split(',');
						if (arrayofval[0] == 'saviorpro_level_id_' + $(this).data('saviorpro_level_id')) {
							delete oldItems[key];
						}
					}
				});

				var newkey = 'saviorpro_level_id_' + $(this).data('saviorpro_level_id');
				oldItems.push(newkey + ',' + newClone.data('saviorpro_role_id'));
				var jsonStart = "{";
				$.each(oldItems, function (key, val) {
					if (val) {
						var arrayofval = val.split(',');
						if (arrayofval[0] == 'saviorpro_level_id_' + $(this).data('saviorpro_level_id') || arrayofval[1] != newClone.data('saviorpro_role_id') && arrayofval[0] != 'saviorpro_level_id_' + $(this).data('saviorpro_level_id') || arrayofval[1] == newClone.data('saviorpro_role_id')) {
							jsonStart = jsonStart + '"' + arrayofval[0] + '":' + '"' + arrayofval[1] + '",';
						}
					}
				});

				localStorage.setItem('saviorpro_mapArray', JSON.stringify(oldItems));
				var lastChar = jsonStart.slice(-1);
				if (lastChar == ',') {
					jsonStart = jsonStart.slice(0, -1);
				}

				var saviorpro_mappingjson = jsonStart + '}';
				localStorage.setItem('saviorpro_mappingjson', saviorpro_mappingjson);
				$("#saviorpro_maaping_json_val").html(saviorpro_mappingjson);
			}

			// $(this).append(ui.draggable);
			// $(this).find('span').css({ 'order': '2' });
			$(this).append(newClone);
			$(this).find('span').css({ 'order': '2' });
			if (jQuery(this).find('.makeMeDraggable').length >= 1) {
				$(this).droppable("destroy");
			}
			makeDrag($('.makeMeDraggable'));
			newClone.css({ 'width': '100%', 'left': '0', 'top': '0', 'margin-bottom': '0px', 'position':'unset', 'order': '1' });
		}
	}

	/*Call-back on disconnect from discord*/
	$('#saviorpro-disconnect-discord').on('click', function (e) {
		e.preventDefault();
		var userId = $(this).data('user-id');
		$.ajax({
			type: "POST",
			dataType: "JSON",
			url: atmSaviorproParams.admin_ajax,
			data: { 'action': 'disconnect_from_discord', 'user_id': userId, 'atm_discord_nonce': atmSaviorproParams.atm_discord_nonce, },
			beforeSend: function () {
				$(".atm-spinner").addClass("atm-is-active");
			},
			success: function (response) {
				if (response.status == 1) {
					window.location = window.location.href.split("?")[0];
				}
			},
			error: function (response) {
				console.error(response);
			}
		});
	});
	/*Call-back to manage member connection with discord from saviorpro members-list*/
	$('.atm-run-api').on('click', function (e) {
		e.preventDefault();
		var userId = $(this).data('uid');
		$.ajax({
			type: "POST",
			dataType: "JSON",
			url: atmSaviorproParams.admin_ajax,
			data: { 'action': 'atm_saviorpro_discord_member_table_run_api', 'user_id': userId, 'atm_discord_nonce': atmSaviorproParams.atm_discord_nonce, },
			beforeSend: function () {
				$("." + userId + ".spinner").addClass("is-active").show();
			},
			success: function (response) {
				if (response.status == 1) {
					$("." + userId + ".atm-save-success").show();;
				}
			},
			error: function (response) {
				console.error(response);
			},
			complete: function () {
				$("." + userId + ".spinner").removeClass("is-active").hide();
			}
		});
	});
	if (atmSaviorproParams.is_admin) {  
        $('#atm_saviorpro_btn_color').wpColorPicker();
        $('#atm_saviorpro_btn_disconnect_color').wpColorPicker();
    }
});
if (atmSaviorproParams.is_admin) {
	/*Tab options*/
	jQuery.skeletabs.setDefaults({
		keyboard: false,
	});
}