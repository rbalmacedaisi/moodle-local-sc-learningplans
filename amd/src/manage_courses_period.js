import * as Ajax from 'core/ajax';
export const init = (learningplanid) => {
    window.console.log(learningplanid);
};
const callUpdateCourse = (lpid, courseorder, periodid) => {
    lpid = parseInt(lpid);
    periodid = parseInt(periodid);
    const promise = Ajax.call([{
        methodname: 'local_sc_learningplans_update_required_courses_inperiod',
        args: {
            learningplan: lpid,
            courseorder,
            periodid: periodid,
        }
    },]);

    promise[0].done(function (response) {
        window.console.log('local_sc_learningplans_update_required_courses_inperiod', response);
        window.location.reload();
    }).fail(function (response) {
        window.console.error(response);
    });
};

const updateCoursePosition = (courseData) => {
    const btnupdatecourseorder = document.querySelectorAll('#courseOrderInPeriod');
    for(const btnupdate of btnupdatecourseorder){
        btnupdate.addEventListener('click',e =>{
            e.preventDefault();
            let periodid = e.target.attributes.periodid.value;
            let lpid = e.target.attributes.lpid.value;
            const coursePosition = [];
            courseData.forEach((index) => {
                coursePosition.push({
                    courseid: index.courseid,
                    position: index.position,
                    periodid: index.periodid
                });
            });
            callUpdateCourse(lpid, coursePosition, periodid);
        });
    }
};

var dragSrcEl = null;
const courseData = [];

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
        let listCoursesid = item.attributes.datacourse.value;
        let listCoursePosition = item.attributes.poscourse.value;
        let idPeriod = item.attributes.periodid.value;
        courseData.push({
            courseid: listCoursesid.replace('courses[', '').replace(']', ''),
            position: listCoursePosition,
            periodid: idPeriod
        });
    });
    updateCoursePosition(courseData);
    containercourses.forEach(function (item) {
        item.classList.remove('over');
    });
}

let items = document.querySelectorAll('.tab-pane .list-courses-required-period');
items.forEach(function (item) {
    item.addEventListener('dragstart', handleDragStart, false);
    item.addEventListener('dragenter', handleDragEnter, false);
    item.addEventListener('dragover', handleDragOver, false);
    item.addEventListener('dragleave', handleDragLeave, false);
    item.addEventListener('drop', handleDrop, false);
    item.addEventListener('dragend', handleDragEnd, false);
});

let containercourses = document.querySelectorAll('.coursesrequired');
containercourses.forEach(function (contelement) {
    contelement.addEventListener('dragenter', handleDragEnter, false);
    contelement.addEventListener('dragleave', handleDragLeave, false);
    contelement.addEventListener('dragover', handleDragOver, false);
    contelement.addEventListener('drop', handleDropNew, false);
});