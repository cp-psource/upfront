;(function($){
var check_email = function(email){
		return /^([a-zA-Z0-9_\.\-])+\@(([a-zA-Z0-9\-])+\.)+([a-zA-Z0-9]{2,4})+$/.test(email);
	},
	show_message = function($form, message){
		$form.find('.ucontact-message-container').html(
			$('<div>').addClass('ucontact-msg msg error').html(message)
		);
	},
	hide_message = function($form){
		$form.find('.ucontact-message-container').html('');
	},
	add_error = function(error, errors, $elem){
		$elem.addClass('ucontact-field-error');
		return errors.push(error);
	}
;
if(!String.prototype.trim){
	String.prototype.trim = function(){
		return this.replace(/^\s+|\s+$/g, '');
	};
}
jQuery(function($){
	var $form = $('div.upfront-contact-form');
	$form.on('blur', '.ucontact-validate-field', function(e){
		var $elem = $(this),
			field = $elem.attr('name'),
			errors = []
		;
		switch(field){
			case 'sendername':
				error = $elem.val().trim() ? false : 'Du mussen Deinen Namen angeben.';
				break;
			case 'senderemail':
				error = check_email($elem.val().trim()) ? false : 'Die E-Mail-Adresse ist nicht gültig.';
				break;
			case 'subject':
				error = $elem.val().trim() ? false : 'Du musst einen Betreff für die Nachricht eingeben.';
				break;
			case 'sendermessage':
				error = $elem.val().trim() ? false : 'Du hast vergessen, eine Nachricht zu schreiben.';
		}
		if(error){
			$elem.addClass('ucontact-field-error');
			show_message($form, error);
		}
		else{
			$elem.removeClass('ucontact-field-error');
			hide_message($form);
		}
	});

	$form.find('form').on('submit', function(e){
		var name = $form.find('input[name=sendername]'),
			email = $form.find('input[name=senderemail]'),
			subject = $form.find('input[name=senderemail]'),
			message = $form.find('textarea[name=sendermessage]'),
			errors = []
		;

		if(!name.val().trim())
			add_error('Du mussen Deinen Namen angeben.', errors, name);
		if(!check_email(email.val().trim()))
			add_error('Die E-Mail-Adresse ist nicht gültig.', errors, email);
		if(subject.length > 0 && !subject.val().trim())
			add_error('Du musst einen Betreff für die Nachricht eingeben.', errors, subject);
		if(!message.val().trim())
			add_error('Du hast vergessen, eine Nachricht zu schreiben.', errors, message);

		if(errors.length > 0){
			//Stop sending
			e.preventDefault();
			show_message($form, errors.join('<br />'));
		}
	});
});
})(jQuery);
