import * as notification from 'core/notification';
import * as Str from 'core/str';
export const init = () => {
    const deleteBtns = document.querySelectorAll('.deleteLearningPlan');
    if (deleteBtns) {
        for (const el of deleteBtns) {
            const titleconfirm = Str.get_string('titleconfirm', 'local_sc_learningplans');
            const msgconfirm = Str.get_string('msgconfirm', 'local_sc_learningplans');
            const yesconfirm = Str.get_string('yesconfirm', 'local_sc_learningplans');
            el.addEventListener('click', e => {
                e.preventDefault();
                const elementId = e.target.parentElement.getAttribute('learning-plan-id');
                notification.saveCancel(titleconfirm, msgconfirm, yesconfirm, () => {
                    window.location.href =
                        `/local/sc_learningplans/delete.php?id=${elementId}&b=${window.location.pathname + window.location.search}`;
                });
            });
        }
    }
};