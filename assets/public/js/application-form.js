jQuery(document).ready(function($) {
    $("form#jobpress-application").validate({
        // Specify validation rules
        rules: {
            fullname: {
                required: true,
                minlength: 3
            },
            email: {
                required: true,
                email: true
            },
            phone: {
                required: true,
                jobpressPhone: true,
				minlength: 8,
            },
            resume: {
                required: true,
                // accept: "image/x-eps,application/pdf"
            },
        },
        // Specify validation error messages
        messages: {
            fullname: {
                required: "Please enter your fullname.",
                minlength: "Your name must be at least 3 characters long."
            },
            email: "Please enter a valid email address.",
            phone: {
                required: "Please enter your phone number.",
                minlength: "Please enter valid phone number.",
                digits: "You can enter only digits. Ex: 01737 269147"
            },
            resume: {
                required: "Please upload your resume.",
                // accept: "Only .png, .jpg, .doc, .docx & .pdf file allowed."
            }
        },
        // Make sure the form is submitted to the destination defined
        submitHandler: function(form) {
          form.submit();
        }
    });

    //Custom Phone numbe validation
    $.validator.addMethod('jobpressPhone', function (value, element){
        return this.optional(element) || /^[0-9 +-]+$/.test(value);
    }, "Please enter a valid phone number");

    // console.log(jobpress_data.ajax_url);
    // console.log(jobpress_data.jobpress_nonce);
});