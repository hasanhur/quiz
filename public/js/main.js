const subjects = document.getElementById('subjects');

if (subjects) {
    subjects.addEventListener('click', e => {
        if (e.target.className === 'btn btn-danger delete-subject') {
            if (confirm("Are you sure to delete?")) {
                const slug = e.target.getAttribute('data-id');

                fetch(`/admin/subject/delete/${slug}`, {
                    method: 'DELETE'
                }).then(res => window.location.reload());
            }
        }
    });
}

const tests = document.getElementById('tests');

if (tests) {
    tests.addEventListener('click', e => {
        if (e.target.className === 'btn btn-danger delete-test') {
            if (confirm("Are you sure to delete?")) {
                const slug = e.target.getAttribute('data-id');

                fetch(`/admin/test/delete/${slug}`, {
                    method: 'DELETE'
                }).then(res => window.location.reload());
            }
        }
    });
}

let wrapper = $(".input_wrap>div");
let add_button = $(".add_field");

let counter = 3;
$(add_button).click(function (e) {
    e.preventDefault();

    var newAdd = '<div><label for="form_option' + counter + '">Option' + counter + '</label><input type="text" id="form_option' + counter + '" name="form[option' + counter + ']" required="required" class="form-control" /></div><a href="#" class="remove_field">Remove</a>';


    let el = $('.input_wrap div:last');
    $(el).after(newAdd);

    counter++;
});

// Edit Options And Question Body
// $(".question-edit").on( "click", function() {
//     $(".option").parent().siblings('.option').before(  );
// });

$("#form_time_limit").change(function () {
    if ($(this).is(':checked')) {
        $("#form_max_time :input").prop('disabled', false);
    } else {
        $("#form_max_time :input").prop('disabled', true);
    }
});

let lastQuestionId = 1;
$(".add-question").on('click', function() {
    // alert('asdf' + lastQuestionId);
    let inputClass = '.option-check-'.concat(lastQuestionId);
    lastQuestionId++;
    $(inputClass).after(
        '<div><label for="form_option_' + lastQuestionId + '">Option ' + lastQuestionId + '</label><input type="text" id="form_option_' + lastQuestionId + '" name="form[option_' + lastQuestionId + ']" class="form-control"></div><div class="option-check-' + lastQuestionId + '"><label for="form_option_' + lastQuestionId + '_check">Correct</label><input type="checkbox" id="form_option_' + lastQuestionId + '_check" name="form[option_' + lastQuestionId + '_check]" value="1"></div>');
});

$('.js-datepicker').datepicker({
    format: 'YYYY-MM-DD HH:mm:ss',
    minDate: 0,
});
