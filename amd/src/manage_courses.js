import * as notification from 'core/notification';
import * as Str from 'core/str';
import * as Ajax from 'core/ajax';

export const init = (learningplanid) => {
    deleteCourseAction(learningplanid);
    addCoursePeriodAction(learningplanid);
    addRequiredAction(learningplanid);
    addOptionalAction(learningplanid);
    changeOptionalToRequiredAction(learningplanid);

    updateCoursePositionAction();

    actionAddCourseRelations();
};

let actionAddCourseRelations = () => {
    const allBtn = document.querySelectorAll('#btnAddRelationCourse');
    allBtn.forEach(element => {
        element.addEventListener('click', () => {
            const recordid = element.getAttribute('course-record-id');
            window.lastRecordId = recordid;
            callGetPossibleRelations(recordid);
        });
    });
    const allRemoveBtn = document.querySelectorAll('#btnDeleteRelationCourse');
    allRemoveBtn.forEach(element => {
        element.addEventListener('click', () => {
            const recordid = element.getAttribute('course-record-id');
            window.lastRecordId = recordid;
            callGetPossibleRelations(recordid, true);
        });
    });

    const closerelation = document.querySelector('#closerelation');
    if (closerelation) {
        closerelation.addEventListener('click', () => {
            const modal = document.getElementById('coursesRelatedModal');
            modal.classList.remove('show');
            modal.style.display = 'none';
        });
    }

    const addrelation = document.querySelector('#addrelation');
    if (addrelation) {
        addrelation.addEventListener('click', () => {
            const select = document.querySelector('#selectCourseToRelated');
            if (select) {
                const selectedOptions = select.selectedOptions;
                const selectedValues = [];
                for (let i = 0; i < selectedOptions.length; i++) {
                    selectedValues.push(selectedOptions[i].value);
                }
                const records = selectedValues.join(',');
                const recordid = window.lastRecordId;
                if (window.getToRemove) {
                    callRemoveRelations(recordid, records);
                } else {
                    callAddRelations(recordid, records);
                }
            }
        });
    }

    const delcloserelation = document.querySelector('#delcloserelation');
    if (delcloserelation) {
        delcloserelation.addEventListener('click', () => {
            const modal = document.getElementById('coursesDelRelatedModal');
            modal.classList.remove('show');
            modal.style.display = 'none';
        });
    }

    const delrelation = document.querySelector('#delrelation');
    if (delrelation) {
        delrelation.addEventListener('click', () => {
            const select = document.querySelector('#selectCourseToDelRelated');
            if (select) {
                const selectedOptions = select.selectedOptions;
                const selectedValues = [];
                for (let i = 0; i < selectedOptions.length; i++) {
                    selectedValues.push(selectedOptions[i].value);
                }
                const records = selectedValues.join(',');
                const recordid = window.lastRecordId;
                callRemoveRelations(recordid, records);
            }
        });
    }
};

const callRemoveRelations = (recordid, records) => {
    const promise = Ajax.call([{
        methodname: 'local_sc_learningplans_del_course_relations',
        args: {
            recordid,
            records,
        }
    },]);

    promise[0].done(function (response) {
        window.console.log('local_sc_learningplans_del_course_relations', response);
        location.reload();
    }).fail(function (response) {
        window.console.error(response);
    });
};

const callAddRelations = (recordid, records) => {
    const promise = Ajax.call([{
        methodname: 'local_sc_learningplans_add_course_relations',
        args: {
            recordid,
            records,
        }
    },]);

    promise[0].done(function (response) {
        window.console.log('local_sc_learningplans_add_course_relations', response);
        location.reload();
    }).fail(function (response) {
        window.console.error(response);
    });
};

const callGetPossibleRelations = (recordid, getToRemove = false) => {
    recordid = parseInt(recordid);
    const promise = Ajax.call([{
        methodname: 'local_sc_learningplans_get_possible_relations',
        args: {
            recordid,
        }
    },]);

    promise[0].done(function (response) {
        window.console.log('local_sc_learningplans_get_possible_relations', response);
        window.getToRemove = getToRemove;
        let dataCourses = response.courses;
        let selectCourses;
        let modal;
        if (getToRemove) {
            modal = document.getElementById('coursesDelRelatedModal');
            dataCourses = response.current;
            selectCourses = document.querySelector('#selectCourseToDelRelated');
        }
        else {
            modal = document.getElementById('coursesRelatedModal');
            selectCourses = document.querySelector('#selectCourseToRelated');
        }
        selectCourses.innerHTML = '';
        dataCourses.forEach(element => {
            const newElement = document.createElement("option");
            newElement.value = element.recordid;
            newElement.append(element.coursename);
            selectCourses.append(newElement);
        });

        modal.classList.add('show');
        modal.style.display = 'block';
    }).fail(function (response) {
        window.console.error(response);
    });
};

let deleteCourseAction = (learningplanid) => {
    const deleteBtns = document.querySelectorAll('#btndeletecourse');
    if (deleteBtns) {
        const titleconfirm = Str.get_string('titleconfirm', 'local_sc_learningplans');
        const msgconfirm = Str.get_string('msgconfirm_course', 'local_sc_learningplans');
        const yesconfirm = Str.get_string('yesconfirm', 'local_sc_learningplans');
        for (const el of deleteBtns) {
            el.addEventListener('click', e => {
                e.preventDefault();
                const course_record_id = el.getAttribute('course-record-id');
                const isrequired = el.getAttribute('course-required');
                notification.saveCancel(titleconfirm, msgconfirm, yesconfirm, () => {
                    callDeleteCourse(learningplanid, course_record_id, isrequired);
                });
            });
        }
    }
};

let addCoursePeriodAction = (learningplanid) => {
    const btnaddcourses = document.querySelector('#addcourses');
    if (btnaddcourses) {
        btnaddcourses.addEventListener('click', e => {
            e.preventDefault();
            const courseselected = document.getElementById('selectAddCourseToLearningPlanPeriod').selectedOptions;
            const periodselected = document.getElementById('selectPeriodToLearningPlan').value;
            const creditselected = -1;
            let isrequired = 0;
            if (document.getElementById('required').checked) {
                isrequired = 1;
            } else {
                isrequired = 0;
            }
            if (courseselected) {
                const datacourses = Array.prototype.slice.call(courseselected);
                const courseid = datacourses.map(select => select.value).join(',');
                callAddCourse(learningplanid, periodselected, courseid, isrequired, creditselected);
            }
        });
    }
};

let addRequiredAction = (learningplanid) => {
    const btnaddrequired = document.querySelector('#addrequired');
    if (btnaddrequired) {
        btnaddrequired.addEventListener('click', e => {
            e.preventDefault();
            const courseselected = document.getElementById('selectAddCourseToLearningPlan').selectedOptions;
            if (courseselected) {
                const datacourses = Array.prototype.slice.call(courseselected);
                const courseid = datacourses.map(select => select.value).join(',');
                callAddCourse(learningplanid, null, courseid, 1, -1);
            }
        });
    }
};

let addOptionalAction = (learningplanid) => {
    const btnaddooptional = document.querySelector('#addOptional');
    if (btnaddooptional) {
        btnaddooptional.addEventListener('click', e => {
            e.preventDefault();
            const courseselected = document.getElementById('selectAddCourseOptionalToLearningPlan').selectedOptions;
            if (courseselected) {
                const datacourses = Array.prototype.slice.call(courseselected);
                const courseid = datacourses.map(select => select.value).join(',');
                callAddCourse(learningplanid, null, courseid, 0, -1);
            }
        });
    }
};

let changeOptionalToRequiredAction = (learningplanid) => {
    const addoptionalrequired = document.querySelectorAll('#addoptional_required');
    if (addoptionalrequired) {
        const titleconfirm = Str.get_string('titleconfirmmove', 'local_sc_learningplans');
        const yesconfirm = Str.get_string('yesmmoveconfirm', 'local_sc_learningplans');
        for (const add of addoptionalrequired) {
            const coursename = add.getAttribute('cname');
            const msgconfirm = Str.get_string('msgconfirm_mmove', 'local_sc_learningplans', { cname: coursename });
            add.addEventListener('click', e => {
                e.preventDefault();
                const course_record_id = add.getAttribute('course-record-id');
                const credits = add.getAttribute('credits') ?? -1;
                const periodid = add.getAttribute('periodid') ?? null;
                if (course_record_id) {
                    const recordid = course_record_id;
                    notification.saveCancel(titleconfirm, msgconfirm, yesconfirm, () => {
                        callDeleteCourse(learningplanid, recordid, 0, false, credits, periodid, add.getAttribute('course-id'));
                    });
                }
            });
        }
    }
};


const callUpdateCourse = (learningid, courseorder, periodid = null, reload = true) => {
    learningid = parseInt(learningid);
    const promise = Ajax.call([{
        methodname: 'local_sc_learningplans_update_required_learning_courses',
        args: {
            learningplan: learningid,
            courseorder,
            periodid
        }
    },]);

    promise[0].done(function (response) {
        window.console.log('local_sc_learningplans_update_required_learning_courses', response);
        if(reload) {
            location.reload();
        }
    }).fail(function (response) {
        window.console.error(response);
    });
};

const callAddCourse = (learningid, periodid, courseid, isrequired, credits) => {
    learningid = parseInt(learningid);
    isrequired = parseInt(isrequired);
    const promise = Ajax.call([{
        methodname: 'local_sc_learningplans_save_learning_course',
        args: {
            learningplan: learningid,
            periodid: periodid,
            courseid,
            required: isrequired,
            credits: credits,
        }
    },]);

    promise[0].done(function (response) {
        window.console.log('local_sc_learningplans_save_learning_course', response);
        location.reload();
    }).fail(function (response) {
        window.console.error(response);
    });
};

const callDeleteCourse = (
    learningid, recordid, isrequired, notaddingcourse = true, credits = -1, periodid = null, courseid = null
) => {
    learningid = parseInt(learningid);
    recordid = parseInt(recordid);
    isrequired = parseInt(isrequired);
    const promise = Ajax.call([{
        methodname: 'local_sc_learningplans_delete_learning_course',
        args: {
            learningplan: learningid,
            courseid: recordid,
            required: isrequired,
        }
    },]);

    promise[0].done(function (response) {
        window.console.log('local_sc_learningplans_delete_learning_course', response);
        if (notaddingcourse) {
            location.reload();
        }
        else {
            callAddCourse(learningid, periodid, courseid, 1, credits);
        }

    }).fail(function (response) {
        window.console.error(response);
    });
};

let items = document.querySelectorAll('.list-courses-required');
let itemsPeriod = document.querySelectorAll('.tab-pane .list-courses-required-period');
let containercourses = document.querySelectorAll('.coursesrequired');

const updateCoursePositionAction = () => {
    const btnupdatecourseorder = document.querySelector('#updatecourseorder');

    items.forEach(function (item) {
        item.addEventListener('dragstart', handleDragStart, false);
        item.addEventListener('dragenter', handleDragEnter, false);
        item.addEventListener('dragover', handleDragOver, false);
        item.addEventListener('dragleave', handleDragLeave, false);
        item.addEventListener('drop', handleDrop, false);
        item.addEventListener('dragend', handleDragEnd, false);
    });

    itemsPeriod.forEach(function (item) {
        item.addEventListener('dragstart', handleDragStart, false);
        item.addEventListener('dragenter', handleDragEnter, false);
        item.addEventListener('dragover', handleDragOver, false);
        item.addEventListener('dragleave', handleDragLeave, false);
        item.addEventListener('drop', handleDrop, false);
        item.addEventListener('dragend', handleDragEnd, false);
    });

    containercourses.forEach(function (contelement) {
        contelement.addEventListener('dragenter', handleDragEnter, false);
        contelement.addEventListener('dragleave', handleDragLeave, false);
        contelement.addEventListener('dragover', handleDragOver, false);
        contelement.addEventListener('drop', handleDropNew, false);
    });

    if (btnupdatecourseorder) {
        const lpid = document.querySelector(".coursesrequired");
        let learningplanid = lpid.getAttribute('learningplanid');
        btnupdatecourseorder.addEventListener('click', e => {
            e.preventDefault();
            clickUpdateCourses(learningplanid);
        });
    }

    const btnupdatecourseperiodorder = document.querySelectorAll('#courseOrderInPeriod');
    for (const btnupdate of btnupdatecourseperiodorder) {
        btnupdate.addEventListener('click', e => {
            e.preventDefault();
            let periodid = btnupdate.attributes.periodid.value;
            let lpid = btnupdate.attributes.lpid.value;
            clickUpdateCoursePeriod(lpid, periodid);
        });
    }
};

const clickUpdateCoursePeriod = (lpid, periodid, reload) => {
    const itemsPeriod = document.querySelectorAll(`li[lpid="${lpid}"][periodid="${periodid}"]`);
    const courseData = [];
    itemsPeriod.forEach(function (item) {
        let listRecordID = item.attributes.datacourse.value;
        let listCoursePosition = item.attributes.poscourse.value;
        let listCourseID = item.attributes.courseid.value;
        courseData.push({
            recordid: listRecordID.replace('courses[', '').replace(']', ''),
            position: listCoursePosition,
            courseid: listCourseID,
        });
    });
    const coursePosition = [];
    courseData.forEach((index) => {
        coursePosition.push({
            recordid: index.recordid,
            courseid: index.courseid,
            position: index.position,
            periodid: index.periodid
        });
    });
    callUpdateCourse(lpid, coursePosition, periodid, reload);
};

const clickUpdateCourses = (learningplanid) => {
    const courseData = [];
    items.forEach(function (item) {
        let listRecordID = item.attributes.datacourse.value;
        let listCoursePosition = item.attributes.poscourse.value;
        let listCourseID = item.attributes.courseid.value;
        courseData.push({
            recordid: listRecordID.replace('courses[', '').replace(']', ''),
            position: listCoursePosition,
            courseid: listCourseID,
        });
    });

    const coursePosition = [];
    courseData.forEach((index) => {
        coursePosition.push({
            recordid: index.recordid,
            courseid: index.courseid,
            position: index.position,
            periodid: index.periodid
        });
    });
    callUpdateCourse(learningplanid, coursePosition);
};

var dragSrcEl = null;

/**
 *
 * @param {*} e
 */
function handleDragStart(e) {
    this.style.opacity = '0.4';

    dragSrcEl = this;

    e.dataTransfer.effectAllowed = 'move';
    e.dataTransfer.setData('text/html', this.innerHTML);
}

/**
 *
 * @param {*} e
 */
function handleDragOver(e) {
    if (e.preventDefault) {
        e.preventDefault();
    }

    e.dataTransfer.dropEffect = 'move';

    return false;
}

/**
 *
 */
function handleDragEnter() {
    this.classList.add('over');
}

/**
 *
 */
function handleDragLeave() {
    this.classList.remove('over');
}

/**
 *
 * @param {*} e
 */
function handleDrop(e) {
    if (e.stopPropagation) {
        e.stopPropagation(); // stops the browser from redirecting.
    }
    if (dragSrcEl != this) {
        dragSrcEl.innerHTML = this.innerHTML;
        this.innerHTML = e.dataTransfer.getData('text/html');

        let itemToReplaceAttr = this.attributes["datacourse"].value;
        let itemToReplaceCourseAttr = this.attributes["courseid"].value;
        let draggedItemAttr = dragSrcEl.attributes["datacourse"].value;
        let draggedItemCourseAttr = dragSrcEl.attributes["courseid"].value;
        this.setAttribute("datacourse", draggedItemAttr);
        this.setAttribute("courseid", draggedItemCourseAttr);
        dragSrcEl.setAttribute("datacourse", itemToReplaceAttr);
        dragSrcEl.setAttribute("courseid", itemToReplaceCourseAttr);
        const lpid = document.querySelector(".coursesrequired");
        let learningplanid = lpid.getAttribute('learningplanid');
        let havePeriod = dragSrcEl.attributes['periodid']?.value;
        if (havePeriod) {
            const allPeriodsRequired = document.querySelectorAll('.coursesrequired');
            allPeriodsRequired.forEach(el => {
                const periodid = el.getAttribute('record-periodid');
                window.console.log(periodid);
                clickUpdateCoursePeriod(learningplanid, periodid, false);
            });
        } else {
            clickUpdateCourses(learningplanid);
        }
        window.console.log(learningplanid, havePeriod,
            'Cambiando los cursos draggedItemAttr: ',
            draggedItemAttr, 'itemToReplaceAttr', itemToReplaceAttr);
    }

    return false;
}

/**
 *
 * @param {*} e
 */
function handleDropNew(e) {
    if (e.stopPropagation) {
        e.stopPropagation(); // stops the browser from redirecting.
    }
    const dragParent = dragSrcEl.parentElement;
    if (dragParent != e.target) {
        e.target.append(dragSrcEl);
    }
    return false;
}

/**
 *
 */
function handleDragEnd() {
    this.style.opacity = '1';
    items.forEach(function (item) {
        item.classList.remove('over');
    });
    containercourses.forEach(function (item) {
        item.classList.remove('over');
    });
}