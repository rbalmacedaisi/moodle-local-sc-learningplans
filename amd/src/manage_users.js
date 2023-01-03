import * as notification from 'core/notification';
import * as Str from 'core/str';
import * as Ajax from 'core/ajax';

export const init = (learningplanid) => {
    addUserAction(learningplanid);
    deleteUserAction(learningplanid);
};

const deleteUserAction = (learningplanid) => {
    const deletebtn = document.querySelectorAll('.btn.deleteuser');
    const titleconfirm = Str.get_string('titleconfirm', 'local_sc_learningplans');
    const msgconfirm = Str.get_string('msgconfirm_user', 'local_sc_learningplans');
    const yesconfirm = Str.get_string('yesconfirm', 'local_sc_learningplans');
    deletebtn.forEach(el => {
        el.addEventListener('click', e => {
            e.preventDefault();
            const userid = e.target.parentElement.getAttribute('userid');
            notification.saveCancel(titleconfirm, msgconfirm, yesconfirm, () => {
                const removeenrol = document.querySelector('#checkRemoveCourses');
                window.console.log(removeenrol, removeenrol.checked);
                callDeleteUser(learningplanid, userid, removeenrol ? removeenrol.checked : false);
            });
        });
    });
};

const addUserAction = (learningplanid) => {
    const userselected = document.querySelector('#listusers');
    const itemvalue = document.querySelector('#listusers-hidden');
    const roleselected = document.querySelector('#listroles');
    userselected.addEventListener('change', () => {
        const val = userselected.value;
        const userList = document.querySelector(`#users-list option[value='${val}']`);
        if (userList) {
            itemvalue.value = userList.getAttribute('data-id');
        }
    });

    const btnadduser = document.querySelector('#adduser');
    if (btnadduser) {
        btnadduser.addEventListener('click', e => {
            e.preventDefault();
            if (userselected && roleselected) {
                const userid = itemvalue.value;
                const roleid = roleselected.value;
                callAddUser(learningplanid, userid, roleid);
            }
        });
    }
};

const callDeleteUser = (learningplan, userid, unenrol) => {
    learningplan = parseInt(learningplan);
    userid = parseInt(userid);
    const promise = Ajax.call([{
        methodname: 'local_sc_learningplans_delete_learning_user',
        args: {
            learningplan,
            userid,
            unenrol,
        }
    },]);

    promise[0].done(function (response) {
        window.console.log('local_sc_learningplans_delete_learning_user', response);
        window.location.reload();
    }).fail(function (response) {
        window.console.error(response);
    });
};

const callAddUser = (learningplan, userid, roleid) => {
    learningplan = parseInt(learningplan);
    userid = parseInt(userid);
    roleid = parseInt(roleid);
    const promise = Ajax.call([{
        methodname: 'local_sc_learningplans_add_learning_user',
        args: {
            learningplan,
            userid,
            roleid,
        }
    },]);

    promise[0].done(function (response) {
        window.console.log('local_sc_learningplans_add_learning_user', response);
        window.location.reload();
    }).fail(function (response) {
        window.console.error(response);
    });
};