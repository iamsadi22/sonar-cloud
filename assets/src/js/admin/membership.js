jQuery(document).ready(function($) {

	let planIndex = $('#membership_plans_container .membership_plan').length;

	let courseOptions = '';
	$.each(crlms_membership_params.courses, function(index, course) {
		courseOptions += `<option value="${course.id}">${course.title}</option>`;
	});

	let subscriptionOptions = '';
	$.each(crlms_membership_params.subscription_options, function(key, item) {
		subscriptionOptions += `<option value="${key}">${item}</option>`;
	});

	$('#add_membership_plan').on('click', function(e) {
		e.preventDefault();

		let newPlanHtml = `
            <div class="membership_plan">
                <p>
                    <label for="membership_plan_title_${planIndex}">Plan Title</label>
                    <input type="text" id="membership_plan_title_${planIndex}" name="membership_plan_title[]" value="" />
                </p>
                <p>
                    <label for="membership_plan_price_${planIndex}">Plan Price</label>
                    <input type="number" id="membership_plan_price_${planIndex}" name="membership_plan_price[]" value="" step="0.01" />
                </p>
                <p>
                   <label for="subscription_type_${planIndex}">Subscription Type</label>
                   <select id="subscription_type_${planIndex}" name="subscription_type[${planIndex}]" class="subscription_type">
                            ${subscriptionOptions}
                   </select>
                </p>
                <p>
                    <label for="connected_courses_${planIndex}">Connected Courses</label>
                    <select id="connected_courses_${planIndex}" name="connected_courses[${planIndex}][]" multiple="multiple" class="connected_courses" style="width: 100%;">
                        ${courseOptions}
                    </select>
                </p>
                <button type="button" class="button button-secondary remove_membership_plan">Remove</button>
                <hr>
            </div>
        `;

		$('#membership_plans_container').append(newPlanHtml);
		planIndex++;
	});

	$('#membership_plans_container').on('click', '.remove_membership_plan', function(e) {
		e.preventDefault();
		$(this).closest('.membership_plan').remove();
	});

	$('#membership_save_button').on('click', function(e) {
		e.preventDefault();

		$('.crlms-loader').html('<div class="spinner is-active"></div>').show();

		let post_id = $('#post_ID').val();
		let membership_plans = [];


		$('#membership_plans_container .membership_plan').each(function() {
			let title = $(this).find('input[name="membership_plan_title[]"]').val();
			let price = $(this).find('input[name="membership_plan_price[]"]').val();
			let plan_id = $(this).find('input[name="membership_plan_id[]"]').val();
			let connectedCourses = $(this).find('.connected_courses').val();
			let subscription = $(this).find('.subscription_type').val();

			membership_plans.push({
				plan_id: plan_id,
				title: title,
				price: price,
				courses: connectedCourses,
				subscription: subscription
			});
		});

		$.ajax({
			type: 'POST',
			url: crlms_membership_params.ajax_url,
			data: {
				action: 'crlms_save_membership_details',
				nonce: crlms_membership_params.nonce,
				post_id: post_id,
				membership_plans: membership_plans
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
