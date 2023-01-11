import * as Ajax from 'core/ajax';

export const init = (learningid) => {

    const btnduplicateplan = document.querySelector('#duplicate_learning_plan');
    const duplicate_courses = document.querySelector('#duplicate_courses');
    const copy_courses = document.querySelector('#copy_courses');
    if (btnduplicateplan) {
        btnduplicateplan.addEventListener('click', e => {
            e.preventDefault();
            const learningname = document.querySelector('#learningname');
            if (!learningname.validity.valid) {
                learningname.reportValidity();
                return;
            }
            const learningshortname = document.querySelector('#learningshortname');
            if (!learningshortname.validity.valid) {
                learningshortname.reportValidity();
                return;
            }
            const desc_plan = document.querySelector('#desc_plan');
            const learningimage = document.querySelector('#id_learningplan_image');
            const duplicate_users = document.querySelector('#duplicate_users');
            const promise = Ajax.call([{
                methodname: 'local_sc_learningplans_duplicate_learning_plan',
                args: {
                    learningid: learningid,
                    learningshortname: learningshortname.value,
                    learningname: learningname.value,
                    fileimage: learningimage.value,
                    description: desc_plan.value,
                    courses: duplicate_courses.checked,
                    copycourses: copy_courses.checked,
                    users: duplicate_users.checked,
                }
            },]);

            promise[0].done(function (response) {
                window.console.log('local_sc_learningplans_duplicate_learning_plan', response);
                location.href = '/local/sc_learningplans/index.php';
            }).fail(function (response) {
                window.console.error(response);
            });
        });
    }
    //
    if (duplicate_courses && copy_courses) {
        duplicate_courses.addEventListener('change', e => {
            if (e.target.checked) {
                copy_courses.disabled = true;
            } else {
                copy_courses.disabled = false;
            }
        });
        copy_courses.addEventListener('change', e => {
            if (e.target.checked) {
                duplicate_courses.disabled = true;
            } else {
                duplicate_courses.disabled = false;
            }
        });
    }
};