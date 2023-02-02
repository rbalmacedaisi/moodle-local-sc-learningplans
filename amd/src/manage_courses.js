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
                const course_record_id = e.target.parentElement.getAttribute('course-record-id');
                const isrequired = e.target.parentElement.getAttribute('course-required');
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
                callAddCourse(learningplanid, -1, courseid, 1, -1);
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
                callAddCourse(learningplanid, -1, courseid, 0, -1);
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
                const course_record_id = e.target.getAttribute('course-record-id');
                const credits = e.target.getAttribute('credits') ?? -1;
                const periodid = e.target.getAttribute('periodid') ?? -1;
                if (course_record_id) {
                    const recordid = course_record_id;
                    notification.saveCancel(titleconfirm, msgconfirm, yesconfirm, () => {
                        callDeleteCourse(learningplanid, recordid, 0, false, credits, periodid, e.target.getAttribute('course-id'));
                    });
                }
            });
        }
    }
};


const callUpdateCourse = (learningid, courseorder, periodid = null) => {
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
        location.reload();
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
    learningid, recordid, isrequired, notaddingcourse = true, credits = -1, periodid = -1, courseid = null
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
            let periodid = e.target.attributes.periodid.value;
            let lpid = e.target.attributes.lpid.value;
            clickUpdateCoursePeriod(lpid, periodid);
        });
    }
};

const clickUpdateCoursePeriod = (lpid, periodid) => {
    const itemsPeriod = document.querySelectorAll(`li[lpid="${lpid}"][periodid="${periodid}"]`);
    const courseData = [];
    itemsPeriod.forEach(function (item) {
        let listCoursesid = item.attributes.datacourse.value;
        let listCoursePosition = item.attributes.poscourse.value;
        courseData.push({
            courseid: listCoursesid.replace('courses[', '').replace(']', ''),
            position: listCoursePosition,
        });
    });
    const coursePosition = [];
    courseData.forEach((index) => {
        coursePosition.push({
            courseid: index.courseid,
            position: index.position,
            periodid: index.periodid
        });
    });
    callUpdateCourse(lpid, coursePosition, periodid);
};

const clickUpdateCourses = (learningplanid) => {
    const courseData = [];
    items.forEach(function (item) {
        let listCoursesid = item.attributes.datacourse.value;
        let listCoursePosition = item.attributes.poscourse.value;
        courseData.push({
            courseid: listCoursesid.replace('courses[', '').replace(']', ''),
            position: listCoursePosition,
        });
    });

    const coursePosition = [];
    courseData.forEach((index) => {
        coursePosition.push({
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
        let draggedItemAttr = dragSrcEl.attributes["datacourse"].value;
        this.setAttribute("datacourse", draggedItemAttr);
        dragSrcEl.setAttribute("datacourse", itemToReplaceAttr);
        const lpid = document.querySelector(".coursesrequired");
        let learningplanid = lpid.getAttribute('learningplanid');
        let periodid = dragSrcEl.attributes['periodid']?.value;
        if (periodid) {
            clickUpdateCoursePeriod(learningplanid, periodid);
        } else {
            clickUpdateCourses(learningplanid);
        }
        window.console.log(learningplanid, periodid,
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