import * as notification from 'core/notification';
import * as Str from 'core/str';
import * as Ajax from 'core/ajax';

export const init = (learningplanid) => {
    callGetUsers(learningplanid, 1, 5, null, true);

    addUserAction(learningplanid);

    const searchUsers = document.querySelector('#searchUsers');
    if (searchUsers) {
        let timeout;
        searchUsers.addEventListener('keyup', e => {
            clearTimeout(timeout);
            timeout = setTimeout(() => {
                window.console.log('Buscar', e.target.value);
                callGetUsers(learningplanid, 1, 5, e.target.value, true);
            }, 500);
        });
    }
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

const createUserTableRow = (userid, firstname, lastname, email, rolename, learningplanid, nameperiod) => {
    const el_tr = document.createElement('tr');
    const el_td_userid = document.createElement('td');
    const el_td_username = document.createElement('td');
    const el_td_email = document.createElement('td');
    const el_td_periodname = document.createElement('td');
    const el_td_rolename = document.createElement('td');
    const el_td_btn = document.createElement('td');
    const el_btn = document.createElement('button');
    const el_i = document.createElement('i');

    el_td_userid.innerHTML = userid;
    el_td_username.innerHTML = `${firstname} ${lastname}`;
    el_td_email.innerHTML = email;
    el_td_rolename.innerHTML = rolename;
    el_btn.id = 'deleteuser';
    el_btn.classList.add('btn');
    el_btn.setAttribute('userid', userid);
    el_btn.setAttribute('data-toggle', 'tooltip');
    el_btn.setAttribute('data-placement', 'bottom');
    el_btn.setAttribute('data-original-title', 'Delete User');
    el_i.classList.add('lp_icon', 'fa', 'fa-trash', 'fa-fw');
    el_btn.append(el_i);
    el_td_btn.append(el_btn);
    el_btn.addEventListener('click', e => {
        e.preventDefault();
        const userid = e.target.parentElement.getAttribute('userid');
        const titleconfirm = Str.get_string('titleconfirm', 'local_sc_learningplans');
        const msgconfirm = Str.get_string('msgconfirm_user', 'local_sc_learningplans');
        const yesconfirm = Str.get_string('yesconfirm', 'local_sc_learningplans');
        notification.saveCancel(titleconfirm, msgconfirm, yesconfirm, () => {
            const removeenrol = document.querySelector('#checkRemoveCourses');
            callDeleteUser(learningplanid, userid, removeenrol ? removeenrol.checked : false);
        });
    });
    el_tr.append(el_td_userid);
    el_tr.append(el_td_username);
    el_tr.append(el_td_email);
    if (nameperiod != undefined) {
        el_td_periodname.innerHTML = nameperiod;
        el_tr.append(el_td_periodname);
    } else {
        el_td_periodname.remove();
    }
    el_tr.append(el_td_rolename);
    el_tr.append(el_td_btn);
    return el_tr;
};

const callGetUsers = (learningid, page, recordsperpage, search = null, renderpages = false) => {
    learningid = parseInt(learningid);
    page = parseInt(page);
    recordsperpage = parseInt(recordsperpage);
    const promise = Ajax.call([{
        methodname: 'local_sc_learningplans_get_learning_users',
        args: {
            learningid,
            page,
            recordsperpage,
            search
        }
    },]);

    promise[0].done(function (response) {
        window.console.log('local_sc_learningplans_get_learning_users', response);
        const usersTable = document.querySelector('#usersTable tbody');
        usersTable.innerHTML = '';
        if (usersTable) {
            for (const element of response.learningusers) {
                const tr = createUserTableRow(
                    element.userid,
                    element.firstname,
                    element.lastname,
                    element.email,
                    element.userrolename,
                    learningid,
                    element.nameperiod,
                );
                usersTable.append(tr);
            }
        }
        if (renderpages) {
            const userpaginationsc = document.querySelector('#userpaginationsc');
            if (userpaginationsc) {
                userpaginationsc.innerHTML = '';
                const totalpages = Math.ceil(response.totalusers / recordsperpage);
                for (let index = 1; index <= totalpages; index++) {
                    const elpage = document.createElement('a');
                    elpage.innerHTML = `${index}`;
                    if (index == 1) {
                        elpage.classList.add('activelp');
                    }
                    elpage.addEventListener('click', el => {
                        let pageactive = document.querySelector('a.activelp');
                        if (pageactive) {
                            pageactive.classList.remove('activelp');
                        }
                        el.preventDefault();
                        el.target.classList.add('activelp');
                        usersTable.innerHTML = '';
                        callGetUsers(learningid, index, recordsperpage, search, false);
                    });
                    userpaginationsc.append(elpage);
                }
            }
        }
    }).fail(function (response) {
        window.console.error(response);
    });
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