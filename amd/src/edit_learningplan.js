import * as Ajax from 'core/ajax';

export const init = (learningid, is_siteadmin) => {
    const btneditplan = document.querySelector('#edit_learning_plan');
    if (btneditplan) {
        btneditplan.addEventListener('click', e => {
            e.preventDefault();
            const learningname = document.querySelector('#learningname');
            if (!learningname.validity.valid) {
                learningname.reportValidity();
                return;
            }
            const learningshortname = document.querySelector('#learningshortname');
            const desc_plan = document.querySelector('#id_desc_planeditable');
            const learningimage = document.querySelector('#id_learningplan_image');
            if (window.NodeList && !NodeList.prototype.map) {
                NodeList.prototype.map = Array.prototype.map;
            }
            const requirements = document.querySelectorAll('input[name=learningrequirements]:checked').map(el => el.value).join();
            const promise = Ajax.call([{
                methodname: 'local_sc_learningplans_edit_learning_plan',
                args: {
                    learningid: learningid,
                    learningname: learningname.value,
                    learningshortname: learningshortname.value,
                    fileimage: learningimage.value,
                    description: desc_plan.innerHTML,
                    requirements,
                }
            },]);

            promise[0].done(function () {
                window.console.log(is_siteadmin, 'local_sc_learningplans_edit_learning_plan');
                if (!is_siteadmin) {
                    window.location.href = '/';
                }
                else {
                    window.location.href = '/local/sc_learningplans/index.php';
                }
            }).fail(function (response) {
                window.console.error(response);
            });
        });
    }
};