/* import * as Ajax from 'core/ajax';*/
import * as Str from 'core/str';

export const init = (str_name_period_config, default_period_months) => {
    /*const btnadd = document.querySelector('#addplan');
     if (btnadd) {
        btnadd.addEventListener('click', e => {
            e.preventDefault();
            const plannameelement = document.querySelector('#learningname');
            if (!plannameelement.validity.valid) {
                plannameelement.reportValidity();
                return;
            }
            const learningshortid = document.querySelector('#learningshortid');
            if (!learningshortid.validity.valid) {
                learningshortid.reportValidity();
                return;
            }
            const learningname = document.querySelector('#learningname');
            const desc_plan = document.querySelector('#id_desc_plan');
            const learningimage = document.querySelector('#id_learningplan_image');
            const btnscourse = document.querySelectorAll('button[courseid]');
            const btnsusers = document.querySelectorAll('button[userid]');
            const listperiods = document.querySelectorAll('.period_name');
            const periods = [];
            const courses = [];
            const users = [];
            const type_enrol_manual = document.getElementById("type_manual");
            const type_enrol_automatic = document.getElementById("type_automatic");
            let hasperiod = 0;
            let type_enrol = 0;
            if (type_enrol_manual != null) {
                if (type_enrol_manual.checked) {
                    type_enrol = 1;
                }
            } else {
                type_enrol = 0;
            }
            if (type_enrol_automatic != null) {
                if (type_enrol_automatic.checked) {
                    type_enrol = 2;
                }
            } else {
                type_enrol = 0;
            }
            if (document.getElementById('addperiod').checked) {
                hasperiod = 1;
            }
            listperiods.forEach(e => {
                periods.push({
                    name: e.value,
                    vigency: 0
                });
            });
            if (btnscourse.length != 0) {
                btnscourse.forEach(e => {
                    courses.push({
                        courseid: parseInt(e.getAttribute('courseid')),
                        required: parseInt(e.getAttribute('isrequired')),
                        credits: -1,
                    });
                });
            }
            if (btnsusers.length != 0) {
                btnsusers.forEach(e => {
                    users.push({
                        userid: parseInt(e.getAttribute('userid')),
                        roleid: parseInt(e.getAttribute('roleid')),
                    });
                });
            }
            const promise = Ajax.call([{
                methodname: 'local_sc_learningplans_save_learning_plan',
                args: {
                    learningname: learningname.value,
                    periods,
                    courses,
                    users,
                    fileimage: learningimage.value,
                    description: desc_plan.value,
                    hasperiod: hasperiod,
                    enroltype: type_enrol,
                }
            },]);
            promise[0].done(function (response) {
                window.console.log('local_sc_learningplans_save_learning_plan', response);
                location.href = '/local/sc_learningplans/index.php';
            }).fail(function (response) {
                window.console.error(response);
            });
        });
    }

    const selectedcourse = document.querySelector('#selectAddCourseToLearningPlan');
    const addrequired = document.querySelector('#addrequired');
    const requiredlist = document.querySelector('#formCurrentCourseToLearningPlan');
    if (addrequired && requiredlist) {
        addrequired.addEventListener('click', async e => {
            e.preventDefault();
            if (!selectedcourse.value) {
                return;
            }
            const selectedoption = selectedcourse.querySelector(`option[value="${selectedcourse.value}"]`);
            window.console.log(selectedoption, `option[value="${selectedcourse.value}"]`);
            if (selectedoption) {
                selectedoption.disabled = true;
                const coursename = selectedoption.innerHTML;
                const courseid = selectedcourse.value;

                const divcontainer = document.createElement('div');
                const div = document.createElement('div');
                div.id = `optionalcourse-${courseid}`;
                div.classList.add('lp_course_list', 'p-1', 'alert-primary');
                div.innerHTML = coursename;
                const deletebtn = document.createElement('button');
                const strdelete = await Str.get_string('delete_current_course', 'local_sc_learningplans');
                window.console.log(strdelete);
                deletebtn.setAttribute('isrequired', 1);
                deletebtn.setAttribute('courseid', courseid);
                deletebtn.setAttribute('aria-label', 'Close');
                deletebtn.setAttribute('type', 'button');
                deletebtn.classList.add('close');
                deletebtn.onclick = e => {
                    e.preventDefault();
                    window.console.log(e.target, e.target.getAttribute('courseid'));
                    selectedoption.disabled = false;
                    divcontainer.remove();
                };
                const deleteicon = document.createElement('i');
                deleteicon.classList.add('fa', 'fa-close');
                divcontainer.append(div);
                div.append(deletebtn);
                requiredlist.append(divcontainer);
                deletebtn.append(deleteicon);
                selectedcourse.value = '';
            }
        });
    }

    const addoptional = document.querySelector('#addooptional');
    const optionallist = document.querySelector('#formOptionalCourseToLearningPlan');
    if (addoptional && optionallist) {
        addoptional.addEventListener('click', async e => {
            e.preventDefault();
            if (!selectedcourse.value) {
                return;
            }
            const selectedoption = selectedcourse.querySelector(`option[value="${selectedcourse.value}"]`);
            window.console.log(selectedoption, `option[value="${selectedcourse.value}"]`);
            if (selectedoption) {
                selectedoption.disabled = true;
                const coursename = selectedoption.innerHTML;
                const courseid = selectedcourse.value;

                const divcontainer = document.createElement('div');
                const div = document.createElement('div');
                div.id = `requiredcourse-${courseid}`;
                div.classList.add('lp_course_list', 'p-1', 'alert-info');
                div.innerHTML = coursename;
                const deletebtn = document.createElement('button');
                deletebtn.setAttribute('isrequired', 0);
                deletebtn.setAttribute('courseid', courseid);
                deletebtn.setAttribute('aria-label', 'Close');
                deletebtn.setAttribute('type', 'button');
                deletebtn.classList.add('close');
                deletebtn.onclick = e => {
                    e.preventDefault();
                    window.console.log(e.target, e.target.getAttribute('courseid'));
                    selectedoption.disabled = false;
                    divcontainer.remove();
                };
                const deleteicon = document.createElement('i');
                deleteicon.classList.add('fa', 'fa-close');
                divcontainer.append(div);
                div.append(deletebtn);
                optionallist.append(divcontainer);
                deletebtn.append(deleteicon);
                selectedcourse.value = '';
            }
        });
    }

    const adduser = document.querySelector('#adduser');
    const listusers = document.querySelector('#listusers');
    const listroles = document.querySelector('#listroles');
    const listlearningusers = document.querySelector('.listlearningusers');
    if (adduser && listusers && listroles) {
        adduser.addEventListener('click', async e => {
            e.preventDefault();
            if (!listusers.validity.valid) {
                listusers.reportValidity();
                return;
            }
            if (!listroles.validity.valid) {
                listroles.reportValidity();
                return;
            }
            const selectedoption = listusers.querySelector(`option[value="${listusers.value}"]`);
            if (selectedoption) {
                const userid = listusers.value;
                const roleid = listroles.value;
                const username = selectedoption.innerHTML;
                selectedoption.disabled = true;
                const divcontainer = document.createElement('div');
                const div = document.createElement('div');
                div.classList.add('lp_user_list', 'p-1', 'alert-primary');
                div.innerHTML = username;
                const deletebtn = document.createElement('button');
                deletebtn.setAttribute('roleid', roleid);
                deletebtn.setAttribute('userid', userid);
                deletebtn.setAttribute('aria-label', 'Close');
                deletebtn.setAttribute('type', 'button');
                deletebtn.classList.add('close');
                deletebtn.onclick = e => {
                    e.preventDefault();
                    selectedoption.disabled = false;
                    divcontainer.remove();
                };
                const deleteicon = document.createElement('i');
                deleteicon.classList.add('fa', 'fa-close');
                divcontainer.append(div);
                div.append(deletebtn);
                listlearningusers.append(divcontainer);
                deletebtn.append(deleteicon);
                listusers.value = '';
                listroles.value = '';
            }
        });
    } */
    addPeriodsOrNot(str_name_period_config, default_period_months);
};

let addPeriodsOrNot = async (str_name_period_config, default_period_months) => {
    const addperiod = document.querySelector('#addperiod');
    if (addperiod) {
        const periodname = await Str.get_string('periodname', 'local_sc_learningplans');
        //const period = await Str.get_string('period', 'local_sc_learningplans');
        const typeperiod = await Str.get_string('typeperiod', 'local_sc_learningplans');
        const manual = await Str.get_string('manual', 'local_sc_learningplans');
        const auto = await Str.get_string('auto', 'local_sc_learningplans');
        const periodmonths = await Str.get_string('periodmonths', 'local_sc_learningplans');

        const divInput = document.createElement('div');
        const label = document.createElement('label');
        const input = document.createElement('input');
        input.classList.add('period_name');

        addperiod.addEventListener('click', () => {
            let addingperiods = document.getElementById('addingperiods');
            addingperiods.disabled = false;
            let optperiod = document.getElementById('periodslist');
            optperiod.disabled = false;
            let parent = document.getElementById('listperiods');
            let parent_enrol = document.getElementById('type_enrol');
            if (addingperiods) {
                addingperiods.addEventListener('click', () => {
                    parent.innerHTML = '';
                    let value = optperiod.options[optperiod.selectedIndex].value;
                    for (let i = 1; i <= value; i++) {
                        const labelForName = label.cloneNode();
                        labelForName.setAttribute('for', `period_${i}`);
                        labelForName.innerHTML = `${periodname} ${i}:&nbsp;`;

                        const inputForName = input.cloneNode();
                        inputForName.setAttribute('value', `${str_name_period_config} ${i}`);
                        inputForName.placeholder = `${str_name_period_config} ${i}`;
                        inputForName.id = `inputPeriodName${i}`;

                        const labelForMonths = label.cloneNode();
                        labelForMonths.setAttribute('for', `period_months_${i}`);
                        labelForMonths.innerHTML = `&nbsp;&nbsp;${periodmonths}:&nbsp;`;

                        const inputForMonths = input.cloneNode();
                        inputForMonths.setAttribute('value', default_period_months);
                        inputForMonths.placeholder = default_period_months;
                        inputForMonths.id = `inputPeriodMonth${i}`;

                        const divToPut = divInput.cloneNode();
                        divToPut.append(labelForName);
                        divToPut.append(inputForName);
                        divToPut.append(labelForMonths);
                        divToPut.append(inputForMonths);
                        parent.append(divToPut);
                    }
                    parent_enrol.innerHTML = `<div><label class="w-100">${typeperiod}</label>
                                              <input type="radio" id="type_manual" name="enrol_period" value="1">
                                              <label for="type_manual">${manual}</label>
                                              <input type="radio" id="type_automatic" name="enrol_period" value="2">
                                              <label for="css">${auto}</label></div>`;

                });
                document.getElementById('addcourses').classList.remove("d-block");
                document.getElementById('listcoursesplan').classList.remove("d-block");
                document.getElementById('listusersplan').classList.remove("d-block");
                document.getElementById('addcourses').classList.add("d-none");
                document.getElementById('listcoursesplan').classList.add("d-none");
                document.getElementById('listusersplan').classList.add("d-none");
            }
        });
    }
    const notperiod = document.querySelector('#notperiod');
    if (notperiod) {
        notperiod.addEventListener('click', () => {
            document.getElementById('listperiods').innerHTML = '';
            document.getElementById('type_enrol').innerHTML = '';
            let addingperiods = document.getElementById('addingperiods');
            addingperiods.disabled = true;
            document.getElementById('addcourses').classList.remove("d-none");
            document.getElementById('listcoursesplan').classList.remove("d-none");
            document.getElementById('listusersplan').classList.remove("d-none");
            document.getElementById('addcourses').classList.add("d-block");
            document.getElementById('listcoursesplan').classList.add("d-block");
            document.getElementById('listusersplan').classList.add("d-block");
        });
    }
};