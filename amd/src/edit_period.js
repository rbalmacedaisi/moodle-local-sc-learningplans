import * as Ajax from 'core/ajax';
import * as Str from 'core/str';
import * as notification from 'core/notification';

export const init = (learningid) => {
    const btnsavenewperiod = document.querySelector('#save_new_period');
    if (btnsavenewperiod) {
        btnsavenewperiod.addEventListener('click', e => {
            e.preventDefault();
            const periodname = document.querySelector('#name_new_period');
            const periodvigency = document.querySelector('#vigency_new_period');
            const addSubperiodRadioButton = document.getElementById('addsubperiod');
            if (!periodname.validity.valid) {
                periodname.reportValidity();
                return;
            }
            if (!periodvigency.validity.valid) {
                periodvigency.reportValidity();
                return;
            }
            const promise = Ajax.call([{
                methodname: 'local_sc_learningplans_addperiod_learning_plan',
                args: {
                    learningplanid: learningid,
                    name: periodname.value,
                    vigency: periodvigency.value,
                    hassubperiods: addSubperiodRadioButton.checked ? 1 : 0,
                    subperiods:[]
                }
            },]);

            promise[0].done(function (response) {
                window.console.log('local_sc_learningplans_addperiod_learning_plan', response);
                window.location.reload(true);
            }).fail(function (response) {
                window.console.error(response);
            });
        });
    }
    const btndeleteperiod = document.querySelectorAll('#btndeleteperiod');
    if (btndeleteperiod) {
        for (const el of btndeleteperiod) {
            const titleconfirm = Str.get_string('titleconfirm', 'local_sc_learningplans');
            const msgconfirm = Str.get_string('msgconfirm_period', 'local_sc_learningplans');
            const yesconfirm = Str.get_string('yesconfirm', 'local_sc_learningplans');
            el.addEventListener('click', e => {
                e.preventDefault();
                const periodid = e.target.parentElement.getAttribute('period-id');
                notification.saveCancel(titleconfirm, msgconfirm, yesconfirm, () => {
                    callDeletePeriod(learningid, periodid);
                });
            });
        }
    }
    const editperiod = document.querySelectorAll('.modalEditPeriod');
    if (editperiod) {
        for (const btnedit of editperiod) {
            let periodid = btnedit.getAttribute('data-id');
            const btnsavename = document.querySelector('#save_edit_period_' + periodid);
            btnsavename.addEventListener('click', e => {
                window.console.log(e);
                let nameperiod = document.querySelector('#name_new_period_' + periodid).value;
                let vigencyperiod = document.querySelector('#vigency_new_period_' + periodid).value;
                let hassubperiodsCheck = document.querySelector('#addsubperiod-' + periodid);
                let hassubperiods = hassubperiodsCheck.checked ? 1 : 0
                callEditPeriod(periodid, nameperiod, vigencyperiod, hassubperiods);
            });
        }
    }
};
const callDeletePeriod = (learningid, periodid) => {
    learningid = parseInt(learningid);
    periodid = parseInt(periodid);
    const promise = Ajax.call([{
        methodname: 'local_sc_learningplans_delete_period_learning_plan',
        args: {
            learningplan: learningid,
            periodid: periodid,
        }
    },]);
    promise[0].done(function (response) {
        window.console.log('local_sc_learningplans_delete_period_learning_plan', response);
        window.location.reload(true);
    }).fail(function (response) {
        window.console.error(response);
    });
};

const callEditPeriod = (periodid, nameperiod, vigencyperiod, hassubperiods) => {
    periodid = parseInt(periodid);
    const promise = Ajax.call([{
        methodname: 'local_sc_learningplans_edit_period_learning_plan',
        args: {
            periodid: periodid,
            nameperiod: nameperiod,
            vigency: vigencyperiod,
            hassubperiods: hassubperiods,
            subperiods:[]
            //------------------------------------------------------------------
        }
    },]);
    promise[0].done(function (response) {
        window.console.log('local_sc_learningplans_edit_period_learning_plan', response);
        window.location.reload(true);
    }).fail(function (response) {
        window.console.error(response);
    });
};
document.addEventListener('DOMContentLoaded', function () {
  // Tu código JavaScript aquí
  var modal = document.getElementById('editNamePeriod');
  if (modal) {
    modal.addEventListener('shown.bs.modal', function () {
      // Simula un clic en el radio button con el id "exampleRadios2"
      var radio = document.getElementById('exampleRadios2');
      if (radio) {
        radio.click();
      }
    });
  }
});





