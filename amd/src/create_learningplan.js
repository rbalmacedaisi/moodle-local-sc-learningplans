import * as Ajax from 'core/ajax';
import * as Str from 'core/str';

//Get name and shortname of the custom field
const learningshortid = document.getElementById('learningshortid');
const plannameelement = document.getElementById('learningname');
const btnadd = document.getElementById('addplan');

//Get period items to be used
const addperiod = document.getElementById('addperiod');
const notperiod = document.getElementById('notperiod');
const optperiod = document.getElementById('periodslist');
const addingperiods = document.getElementById('addingperiods');
const parent = document.getElementById('listperiods');
const parent_enrol = document.getElementById('type_enrol');

//Get courses items to be used
const addcourses = document.getElementById('addcourses');
const listcoursesplan =document.getElementById('listcoursesplan');
const addrequired = document.getElementById('addrequired');
const addoptional = document.getElementById('addooptional');
const requiredlist = document.getElementById('formCurrentCourseToLearningPlan');
const optionallist = document.getElementById('formOptionalCourseToLearningPlan');
const selectedcourse = document.getElementById('selectAddCourseToLearningPlan');

//Get user items to be used
const listusersplan=document.getElementById('listusersplan');
const adduser = document.getElementById('adduser');
const listusers = document.getElementById('listusers');
const listroles = document.getElementById('listroles');
const listgroups = document.getElementById('listgroups');

//Get plan items to be used
const learningimage = document.getElementById('id_learningplan_image');

//Get custom fields items to be used
const careercost = document.getElementById('careercost');
const careerduration = document.getElementById('careerduration');
const careername = document.getElementById('careername');

export const init = (str_name_period_config, default_period_months) => {
    addLearningPlan();
    addUsers();
    addCourses();
    addPeriodsOrNot(str_name_period_config, default_period_months);
};

let addLearningPlan = () => {
    if (btnadd) {
        btnadd.addEventListener('click', e => {
            e.preventDefault();
            let desc_plan =document.getElementById('id_desc_planeditable');
            if (!learningshortid.validity.valid) {
                learningshortid.reportValidity();
                return;
            }
            else if (!plannameelement.validity.valid) {
                plannameelement.reportValidity();
                return;
            }
            else if (!careercost.validity.valid) {
                careercost.reportValidity();
                return;
            }
            else if (!careerduration.validity.valid) {
                careerduration.reportValidity();
                return;
            }
            else if (!careername.validity.valid) {
                careername.reportValidity();
                return;
            }
            const btnscourse = document.querySelectorAll('button[courseid]');
            const btnsusers = document.querySelectorAll('button[userid]');
            const listperiods = document.querySelectorAll('.period_name');
            const type_enrol_manual = document.getElementById("type_manual");
            const type_enrol_automatic = document.getElementById("type_automatic");
            const hasperiod = addperiod.checked ? 1 : 0;
            const type_enrol = setTypeEnrol(type_enrol_manual,type_enrol_automatic);
            const periods = [];
            const courses = [];
            const users = [];
            listperiods.forEach(e => {
                const index = e.getAttribute('index');
                const name = e.value == '' ? index : e.value;
                const monthsElement = document.querySelector(`#inputPeriodMonth${index}`);
                let months = 0;
                if (monthsElement) {
                    months = monthsElement.value == '' ? 0 : monthsElement.value;
                }
                periods.push({
                    name,
                    months
                });
            });
            if (btnscourse.length != 0 && hasperiod == 0) {
                btnscourse.forEach(e => {
                    courses.push({
                        courseid: parseInt(e.getAttribute('courseid')),
                        required: parseInt(e.getAttribute('isrequired')),
                        credits: -1,
                    });
                });
            }
            if (btnsusers.length != 0 && hasperiod == 0) {
                btnsusers.forEach(e => {
                    users.push({
                        userid: parseInt(e.getAttribute('userid')),
                        roleid: parseInt(e.getAttribute('roleid')),
                        group: e.getAttribute('group'),
                    });
                });
            }
            if (window.NodeList && !window.NodeList.prototype.map) {
                window.NodeList.prototype.map = Array.prototype.map;
            }
            const requirements = document.querySelectorAll('input[name=learningrequirements]:checked').map(el => el.value).join();
            const customfields = [];
            document.getElementsByClassName('customfield').forEach(({id,value}) => {
                // eslint-disable-next-line babel/no-unused-expressions
                value? customfields.push({id,value}):undefined;
            });
            const args = {
                learningshortid: learningshortid.value,
                learningname: plannameelement.value,
                periods,
                courses,
                users,
                fileimage: learningimage.value,
                description: desc_plan.innerHTML,
                hasperiod: hasperiod,
                enroltype: type_enrol,
                requirements,
                customfields
            };
            const promise = Ajax.call([{
                methodname: 'local_sc_learningplans_save_learning_plan',
                args
            },]);
            promise[0].done(function (response) {
                window.console.log('local_sc_learningplans_save_learning_plan', response);
                window.location.href = '/local/sc_learningplans/index.php';
            }).fail(function (response) {
                window.console.error(response);
            });
        });
    }
};

let addUsers = () => {

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
                const group = listgroups.value;
                const username = selectedoption.innerHTML;
                selectedoption.disabled = true;
                const divcontainer = document.createElement('div');
                const div = document.createElement('div');
                div.classList.add('lp_user_list', 'p-2', 'alert-primary', 'rounded-sm', 'mb-1');
                div.innerHTML = username;
                const deletebtn = document.createElement('button');
                deletebtn.setAttribute('roleid', roleid);
                deletebtn.setAttribute('userid', userid);
                deletebtn.setAttribute('group', group);
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
    }
};

let addCourses = async () => {
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
                div.classList.add('lp_course_list', 'p-2', 'alert-primary', 'rounded-sm', 'mb-1');
                div.innerHTML = coursename;
                const deletebtn = document.createElement('button');

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
                div.classList.add('lp_course_list', 'p-2', 'alert-info', 'rounded-sm', 'mb-1');
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
};

let addPeriodsOrNot = async (str_name_period_config, default_period_months) => {
    if (addperiod) {
        const periodname = await Str.get_string('periodname', 'local_sc_learningplans');
        //const period = await Str.get_string('period', 'local_sc_learningplans');
        const typeperiod = await Str.get_string('typeperiod', 'local_sc_learningplans');
        const manual = await Str.get_string('manual', 'local_sc_learningplans');
        const auto = await Str.get_string('auto', 'local_sc_learningplans');
        const periodmonths = await Str.get_string('periodmonths', 'local_sc_learningplans');
        const divInput = document.createElement('div');
        const divCol = document.createElement('div');
        const label = document.createElement('label');
        const input = document.createElement('input');
        addperiod.addEventListener('click', () => {
            addingperiods.disabled = false;
            optperiod.disabled = false;
            if (addingperiods) {
                addingperiods.addEventListener('click', () => {
                    parent.innerHTML = '';
                    //Disabled the careerduration customfield, the duration will be calculated based on the periods
                    careerduration.value = null;
                    careerduration.disabled = true;
                    // The next line was causing an error
                    // let value = optperiod.isoptions[optperiod.selectedIndex].value;
                    let value =  optperiod.value;
                    for (let i = 1; i <= value; i++) {
                        const labelForName = label.cloneNode();
                        labelForName.setAttribute('for', `period_${i}`);
                        labelForName.innerHTML = `${periodname} ${i}:&nbsp;`;
                        const inputForName = input.cloneNode();
                        inputForName.classList.add('period_name');
                        inputForName.classList.add('form-control');
                        inputForName.setAttribute('value', `${str_name_period_config} ${i}`);
                        inputForName.setAttribute('index', i);
                        inputForName.placeholder = `${str_name_period_config} ${i}`;
                        inputForName.id = `inputPeriodName${i}`;
                        const labelForMonths = label.cloneNode();
                        labelForMonths.setAttribute('for', `period_months_${i}`);
                        labelForMonths.innerHTML = `&nbsp;&nbsp;${periodmonths}:&nbsp;`;
                        const inputForMonths = input.cloneNode();
                        inputForMonths.classList.add('form-control');
                        inputForMonths.setAttribute('value', default_period_months);
                        inputForMonths.placeholder = default_period_months;
                        inputForMonths.setAttribute('index', i);
                        inputForMonths.id = `inputPeriodMonth${i}`;

                        const div_col = divCol.cloneNode();
                        div_col.classList.add('col-sm-6');
                        div_col.append(labelForName);
                        div_col.append(inputForName);

                        const div_col_months = divCol.cloneNode();
                        div_col_months.classList.add('col-sm-6');
                        div_col_months.append(labelForMonths);
                        div_col_months.append(inputForMonths);


                        const divToPut = divInput.cloneNode();
                        divToPut.classList.add('row');
                        divToPut.append(div_col);
                        divToPut.append(div_col_months);
                        /*divToPut.append(labelForName);
                        divToPut.append(inputForName);
                        divToPut.append(labelForMonths);
                        divToPut.append(inputForMonths);*/
                        parent.append(divToPut);
                    }
                    parent_enrol.innerHTML = `<div><label class="w-100">${typeperiod}</label>
                                              <input type="radio" id="type_manual" name="enrol_period" value="1">
                                              <label for="type_manual">${manual}</label>
                                              <input type="radio" id="type_automatic" name="enrol_period" value="2">
                                              <label for="css">${auto}</label></div>`;

                    const listperiods = document.querySelectorAll('.period_name');
                    // eslint-disable-next-line no-unused-vars
                    let months = 0;
                    listperiods.forEach(e => {
                        const index = e.getAttribute('index');
                        const monthsElement = document.querySelector(`#inputPeriodMonth${index}`);
                        let monthsvalue = monthsElement && monthsElement.value == '' ? 0 : parseInt(monthsElement.value);
                        months += monthsvalue;
                    });
                });
                addcourses.classList.remove("d-block");
                addcourses.classList.add("d-none");
                listcoursesplan.classList.remove("d-block");
                listcoursesplan.classList.add("d-none");
                listusersplan.classList.remove("d-block");
                listusersplan.classList.add("d-none");
            }
        });
    }
    if (notperiod) {
        notperiod.addEventListener('click', () => {
            //Enable the careerduration customfield
            careerduration.disabled = false;
            parent.innerHTML = '';
            parent_enrol.innerHTML = '';
            addingperiods.disabled = true;
            addcourses.classList.remove("d-none");
            addcourses.classList.add("d-block");
            listcoursesplan.classList.remove("d-none");
            listcoursesplan.classList.add("d-block");
            listusersplan.classList.remove("d-none");
            listusersplan.classList.add("d-block");
        });
    }
};

const setTypeEnrol = (type_enrol_manual,type_enrol_automatic) => {
    if (type_enrol_manual && type_enrol_manual.checked) {return 1;}
    else if (type_enrol_automatic && type_enrol_automatic.checked) {return 2;}
    return 0;
};