jQuery(document).ready(function($) {

	$('#save-course-data').on('click', function(e) {
		e.preventDefault();
		$('.crlms-loader').html('<div class="spinner is-active"></div>').show();

		let post_id = $('#post_ID').val();
		let course_duration = $('#crlms_course_duration').val();
		let course_price = $('#crlms_course_price').val();
		let course_sale_price = $('#crlms_course_sale_price').val();
		let course_max_student_allowed = $('#crlms_course_max_student_allowed').val();
		let course_max_retake_allowed = $('#crlms_course_max_retake_allowed').val();
		let course_passing_grade = $('#crlms_course_passing_grade').val();

		$.ajax({
			type: 'POST',
			url: crlms_course_params.ajax_url,
			data: {
				action: 'crlms_save_course_general_settings',
				nonce: crlms_course_params.nonce,
				post_id: post_id,
				course_duration: course_duration,
				course_price: course_price,
				course_sale_price: course_sale_price,
				course_max_student_allowed: course_max_student_allowed,
				course_max_retake_allowed: course_max_retake_allowed,
				course_passing_grade: course_passing_grade,
			},
			success: function (response) {
				$('.crlms-loader').hide();

				if(response.status === "success") {
					$('.crlms-notice').html('<div class="notice notice-success"><p>'+ response.message +'</p></div>').show();
				}
				else {
					$('.crlms-notice').html('<div class="notice notice-error"><p>'+ response.message +'</p></div>').show();
				}

				setTimeout(function() {
					$('.crlms-notice').fadeOut('slow');
				}, 3000);
			},
			error: function (xhr, status, error) {
				console.log(xhr.responseText);
			}
		});

	});
});
