<?php
// This file is part of Moodle - https://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <https://www.gnu.org/licenses/>.

/**
 * Plugin strings are defined here.
 *
 * @package     local_sc_learningplans
 * @category    string
 * @copyright   2022 Solutto <>
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$string['pluginname'] = 'Planes de aprendizajes';
$string['plugincustomfields'] = 'Campos personalizados Planes de aprendizaje';

$string['sc_learningplans:manage']  = 'Manager - learning plans in local sc_learningplans';
$string['sc_learningplans:teach']   = 'Teacher - learning plans in local sc_learningplans';

$string['id']           = 'ID';
$string['shortname']    = 'Nombre corto';
$string['name']         = 'Nombre';
$string['coursecount']  = 'Cursos';
$string['usercount']    = 'Usuarios';
$string['periodcount']  = 'Periodos';
$string['created_at']   = 'Creación';
$string['updated_at']   = 'Actualización';
$string['actions']      = 'Acciones';

$string['manage_users']         = 'Gestionar usuarios';
$string['manage_courses']       = 'Gestionar cursos';
$string['delete_learningplan']  = 'Eliminar plan de aprendizaje';
$string['edit_plan']            = 'Editar plan de aprendizaje';
$string['duplicate_plan']       = 'Duplicar plan';
$string['report']               = "Reporte del Plan de Aprendizaje";
$string['requirement_title']    = 'Agregar nuevo requerimiento';
$string['manage_periods']       = 'Gestión de Períodos';
$string['title_current_users']  = 'Usuarios actuales';

$string['new_learning_plan'] = 'Nuevo plan de aprendizaje';
$string['plan_name'] = 'Nombre del plan de aprendizaje';
$string['addingperiods'] = 'Desea agregar Períodos';
$string['select_periods'] = 'Seleccione el número de períodos:';
$string['btnaddperiods'] = 'Añadir periodos';
$string['not_periods'] = 'No se han agregado períodos';
$string['title_add_course'] = 'Agregar nuevo curso';
$string['select_course'] = 'Selecciona un curso';
$string['btn_add_course'] = 'Agregar';
$string['btn_addopt_course'] = 'Agregar Opcional';
$string['title_current_course'] = 'Cursos obligatorios';
$string['title_optional_course'] = 'Cursos opcionales';
$string['delete_current_course'] = 'Eliminar curso';
$string['title_add_users'] = 'Gestionar usuarios';
$string['title_add_roles'] = 'Gestionar roles';
$string['title_add_groups'] = 'Gestionar grupos';
$string['title_career_name'] = 'Nombre carrera';
$string['title_career_cost'] = 'Costo carrera';
$string['title_career_duration'] = 'Duración carrera (semanas)';

$string['add_user_lp'] = 'Agregar nuevos usuarios';
$string['select_user'] = 'Selecciona un usuario';
$string['select_role'] = 'Selecciona un rol';
$string['select_career_name'] = 'Selecciona una carrera';
$string['btn_add_user'] = 'Agregar';
$string['title_desc_plan'] = 'Descripción del Plan';
$string['plan_image'] = 'Imagen del plan';
$string['add_plan'] = 'Añadir plan de aprendizaje';
$string['career_info'] = 'Información de la carrera';

$string['bulk_users'] = 'Cargar usuarios';

$string['periodname']   = 'Nombre del Período';
$string['period']       = 'Periodo';
$string['typeperiod']   = 'Seleccione el tipo de Inscripción a los Períodos';
$string['manual']       = 'Manual';
$string['auto']         = 'Automatico';

$string['periodnamesetting'] = 'Nombre de los periodos';
$string['periodnamesetting_desc'] = 'Ingresa un nombre por defecto para los periodos';
$string['default_period_months'] = 'Duración de cada periodo';
$string['default_period_months_desc'] = 'Ingresa la duración en meses para cada periodo';

$string['periodmonths'] = 'Duración (Meses)';

$string['student']          = 'Estudiante';
$string['teacher']          = 'Instructor';
$string['editingteacher']   = 'Instructor editor';
$string['manager']          = 'Gestor';
$string['coursecreator']    = 'coursecreator';
$string['guest']            = 'guest';
$string['user']             = 'user';
$string['frontpage']        = 'frontpage';
$string['scteachrole']      = 'scteachrole';
$string['scmanagerrole']    = 'scmanagerrole';

$string['addnewperiods'] = 'Añadir un nuevo periodo';
$string['name_period'] = 'Asigne un nombre al periodo';
$string['close_modal'] = 'Cerrar';

$string['titleconfirm'] = 'Confirmar eliminación';
$string['msgconfirm_period'] = 'La eliminación de periodos de los planes de aprendizaje no se puede deshacer. ¿Está seguro de que desea eliminar el periodo seleccionado?';
$string['yesconfirm'] = 'Eliminar';
$string['msgconfirm_course'] = 'La eliminación de cursos de los planes de aprendizaje no se puede deshacer. ¿Está seguro de que desea eliminar el curso seleccionado?';

$string['edit_period'] = 'Editar periodos';

$string['managecourses'] = 'Gestión de cursos';
$string['lpname'] = 'Plan de aprendizaje {$a->name} ';
$string['available_courses'] = 'Cursos Disponibles';
$string['courses_required'] = 'Cursos Requeridos';
$string['courses_optional'] = 'Cursos Opcionales';
$string['add_courses_required'] = 'Agregar Cursos Requeridos';
$string['add_courses_optional'] = 'Agregar Cursos Opcionales';
$string['btn_save_coursepos'] = 'Guardar Posiciones en Cursos';
$string['add_courses_period'] = 'Agregar nuevos Cursos';
$string['select_credits'] = 'Seleccione el número de créditos';
$string['select_one_period'] = 'Seleccione un Período';

$string['titleconfirmmove'] = 'Mover curso a requerido';
$string['msgconfirm_mmove'] = '¿Realmente deseas que el curso {$a->cname} sea un curso requerido?';
$string['yesmmoveconfirm'] = 'Mover';

$string['save'] = 'Guardar cambios';

$string['manageuser'] = 'Plan de aprendizaje - Gestión de usuarios';
$string['id_user'] = 'Id';
$string['name_user'] = 'Nombre';
$string['email_user'] = 'Correo Electrónico';
$string['roles_user'] = 'Roles';
$string['action_user'] = 'Acciones';
$string['bulk_users'] = 'Cargar usuarios';
$string['search_users'] = 'Buscar usuarios';
$string['assign_users'] = 'Asignar Usuarios';
$string['assign_rol'] = 'Asignar Rol';

$string['msgconfirm_user'] = 'La eliminación de usuarios de los planes de aprendizaje no se puede deshacer. Seleccione la casilla a continuación, si adicionalmente quiere eliminar la matriculación del usuario en todos los cursos<br/><input type="checkbox" id="checkRemoveCourses" name="checkRemoveCourses"/>';

$string['msgconfirm'] = '¿Está seguro de querer eliminar de forma definitiva este plan de aprendizaje y toda la información relacionada?';

$string['period_enrol'] = 'Periodo Actual Inscrito';

$string['copy'] = 'Copia';
$string['duplicate_courses'] = 'Duplicar los cursos del plan (Usar los mismos cursos)';
$string['copy_courses'] = 'Copiar los cursos del plan (Crear nuevos cursos)';
$string['duplicate_users'] = 'Duplicar los usuarios del plan';

$string['plan_requirements'] = 'Requisitos';

$string['pending_user'] = 'Usuarios Pendientes de Inscripción';
$string['nextperiodname'] = 'Próximo periodo';
$string['enrolnextperiod'] = 'Inscribir';

$string['massive_nodata'] = 'Necesitas subir información';
$string['massive_usernamenotexist'] = 'El usuario ({$a->username}) no existe y no podemos crearlo.';
$string['massive_created_user'] = 'Se creo el usuario {$a->username}.';
$string['massive_update'] = 'Se actualizó la información del usuario {$a->username} de acuerdo con el archivo.';
$string['massive_lpnotexist'] = 'El plan de aprendizaje ({$a->lpname}) no existe.';
$string['massive_succes'] = 'El usuario {$a->username} se matriculó al plan de aprendizaje {$a->lpname}.';
$string['massive_done'] = 'Información subida';

$string['report'] = "Reporte del Plan de Aprendizaje";
$string['email'] = 'Correo';
$string['currentcourse'] = 'Nombre de curso actual';
$string['completedcourse'] = 'Ultimo curso completado';
$string['progress'] = 'Progreso';
$string['currentperiod'] = 'Periodo actual';

$string['assign_group'] = 'Asignar grupo';
$string['select_group'] = 'Selecciona un grupo';
$string['cancel'] = 'Cancelar';

$string['search_user_btn'] = 'Buscar';
$string['search_user'] = 'Buscar usuario:&nbsp;';

$string['alert_not_course'] = 'Aún no hay cursos asignados';
$string['enroledheadinguser'] = 'Correo de matriculación';
$string['sendmailenrol'] = 'Enviar correo al matricular un usuario en un plan de aprendizaje';
$string['sendmailenrol_desc'] = 'Enviar la notificación al correo del usuario cuando es matriculado en un plan de aprendizaje';
$string['emailsubjectenrol'] = 'Asunto personalizado';
$string['emailsubjectenrol_desc'] = 'Asunto personalizado del correo';
$string['templatemailenrol'] = 'Plantilla de correo personalizada cuando un usuario es matriculado';
$string['templatemailenrol_desc'] = '{{fullusername}}: Nombre completo de usuario<br/>{{lpname}}: Nombre del plan de aprendizaje<br/>{{firstcoursename}}: Nombre del primer curso<br/>{{firsturlcourse}}: Enlace del primer curso';

$string['updatelpheading'] = 'Correo de actualización';
$string['sendupdatelp'] = 'Enviar correo cuando un plan de aprendizaje se actualice';
$string['sendupdatelp_desc'] = 'Enviar notificación al correo usuario cuando se actualiza un plan de aprendizaje';
$string['emailsubjectupdatelp'] = 'Asunto personalizado';
$string['emailsubjectupdatelp_desc'] = 'Asunto personalizado del correo';
$string['templatemailupdatelp'] = 'Plantilla de correo cuando el plan de aprendizaje es actualizado';
$string['templatemailupdatelp_desc'] = '{{fullusername}}: Nombre completo de usuario<br/>{{lpname}}: Nombre del plan de aprendizaje<br/>{{firstcoursename}}: Nombre del primer curso<br/>{{firsturlcourse}}: Enlace del primer curso';

$string['add_relation_course'] = 'Agregar Relación';
$string['add_relation'] = 'Correlación';
$string['remove_relation_course'] = 'Eliminar relación';
$string['related_courses'] = 'Cursos relacionados';
$string['customfield_regexpattern'] = 'Expresion regular campo personalizado';
$string['customfield_datatype'] = 'Tipo de dato campo personzalizado';
$string['customfieldsettings'] = 'Configuraciones para campos personalizados del learning plan.';
$string['datatypenumber'] = 'Número';
$string['datatypetext'] = 'Texto';
$string['datatypeemail'] = 'Correo electrónico';

$string['Informacion_carrera'] = 'Información carrera';
$string['Campos_prueba'] = 'Campos test';
$string['careercost'] = 'Costo carrera';
$string['careerduration'] = 'Duración carrera (meses)';
$string['careername'] = 'Nombre carrera';
