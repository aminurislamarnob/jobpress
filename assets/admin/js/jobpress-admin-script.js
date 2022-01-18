/*************
JobPress Plugin Job Application Collect Medium Active/Deactive
 ***************/
var jobpress_collect_md_inputs = document.querySelectorAll("input[name='jobpress_application_collect_medium']");
var jobpress_email = document.querySelector(".jobpress_email");
var jobpress_contact_form_id = document.querySelector(".jobpress_contact_form_id");
jobpress_collect_md_inputs.forEach(function(input){
    input.addEventListener('click', function(el){
        var clicked = el.currentTarget.value;
        if(clicked == 1){
            jobpress_email.classList.remove('d-none');
            jobpress_contact_form_id.classList.add('d-none');
        }else if(clicked == 2){
            jobpress_email.classList.add('d-none');
            jobpress_contact_form_id.classList.remove('d-none');
        }else{
            jobpress_email.classList.add('d-none');
            jobpress_contact_form_id.classList.add('d-none');
        }
    });
});