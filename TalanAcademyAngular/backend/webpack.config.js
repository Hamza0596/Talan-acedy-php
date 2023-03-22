var Encore = require('@symfony/webpack-encore');
require('dotenv').config();
    Encore.configureRuntimeEnvironment(process.env.NODE_ENV || 'dev');
Encore
// directory where compiled assets will be stored
    .setOutputPath('public/build/')
    // public path used by the web server to access the output path
    .setPublicPath((process.env.BASE_URI || '') + '/build')
    // only needed for CDN's or sub-directory deploy
    .setManifestKeyPrefix('build')
    /*
     * ENTRY CONFIG
     *
     * Add 1 entry for each "page" of your app
     * (including one that's included on every page - e.g. "app")
     *
     * Each entry will result in one JavaScript file (e.g. app.js)
     * and one CSS file (e.g. app.css) if you JavaScript imports CSS.
     */

    /*
     * Entry for Showcase
     */
    .addEntry('appShowcase', './assets/showcase/js/app.js')
    .addEntry('validationRegistration', './assets/showcase/js/registration/form_validation.js')
    .addEntry('goRegistration', './assets/showcase/js/login/goRegistration')

    /*
   * Entry for Dashboard
   */
    .addEntry('appDashboard', './assets/dashboard/js/app.js')
    // .addEntry('btnswitch', './assets/dashboard/js/jquery.btnswitch.js')
    .addEntry('cursus', './assets/dashboard/js/cursus/cursus.js')
    .addEntry('moduleJs', './assets/dashboard/js/module.js')
    .addEntry('dayCourse', './assets/dashboard/js/dayCourse/dayCourse.js')
    .addEntry('formValidation', './assets/dashboard/js/resources/form_validation.js')
    .addEntry('add.ressources', './assets/dashboard/js/resources/add.ressources.js')
    .addEntry('delete.ressource', './assets/dashboard/js/resources/delete.ressource.js')
    .addEntry('edit.ressources', './assets/dashboard/js/resources/edit.ressources.js')
    .addEntry('addPreparcours', './assets/dashboard/js/preparcours/add.preparcours.js')
    .addEntry('validationFormProfile', './assets/dashboard/js/profile/validationFormProfile.js')
    .addEntry('editProfileStudent', './assets/dashboard/js/profile/editProfileStudent.js')
    .addEntry('editProfileAdmin', './assets/dashboard/js/profile/editProfileAdmin.js')
    .addEntry('editProfileMentor', './assets/dashboard/js/profile/editProfileMentor.js')
    .addEntry('editEmail', './assets/dashboard/js/profile/editEmail.js')
    .addEntry('editPassword', './assets/dashboard/js/profile/editPassword.js')
    .addEntry('addImageProfile', './assets/dashboard/js/profile/addImageProfile.js')
    .addEntry('applyCursus', './assets/dashboard/js/candidates/applyCursus.js')
    .addEntry('formValidationRegistration', './assets/dashboard/js/registration/form_validation.js')
    .addEntry('summernotejs', 'summernote/dist/summernote-bs4')
    .addEntry('langFrSummernotejs', 'summernote/lang/summernote-fr-FR')
    .addEntry('template_content_activityjs', './assets/dashboard/js/activity/template_content_activity.js')
    .addEntry('addActivity', './assets/dashboard/js/activity/addActivity.js')
    .addEntry('addActivityTabs', './assets/dashboard/js/edit_day/addActivityDay.js')
    .addEntry('addSActivityTabs', './assets/dashboard/js/session/edit_day/addActivitySDay.js')
    .addEntry('addOrder', './assets/dashboard/js/edit_day/addOrder.js')
    .addEntry('editActivityTabs', './assets/dashboard/js/edit_day/editActivityDay.js')
    .addEntry('editSActivityTabs', './assets/dashboard/js/session/edit_day/editActivitySDay.js')
    .addEntry('deleteActivityTabs', './assets/dashboard/js/edit_day/deleteActivityDay.js')
    .addEntry('deleteSActivityTabs', './assets/dashboard/js/session/edit_day/deleteActivitySDay.js')
    .addEntry('editActivity', './assets/dashboard/js/activity/editActivity.js')
    .addEntry('deleteActivity', './assets/dashboard/js/activity/deleteActivity.js')
    .addEntry('editDay', './assets/dashboard/js/edit_day/edit_day.js')
    .addEntry('datatable', 'datatables.net/js/jquery.dataTables.js')
    .addEntry('datatable_dt', 'datatables.net-dt/js/dataTables.dataTables.min.js')
    .addEntry('jszip', 'jszip/dist/jszip.js')
    .addEntry('datatable_button_html5', 'datatables.net-buttons/js/buttons.html5.min.js')
    .addEntry('datatable_button', 'datatables.net-buttons/js/dataTables.buttons.min.js')
    .addEntry('datatables', 'datatables.net-bs4/js/dataTables.bootstrap4.js')
    .addEntry('datatableJs', './assets/dashboard/js/data-tables.js')
    .addEntry('staffJs', './assets/dashboard/js/users/staff.js')
    .addEntry('session', './assets/dashboard/js/session/sessionAdd.js')
    .addEntry('passwordJs', './assets/dashboard/js/users/password.js')
    .addEntry('registredJs', './assets/dashboard/js/users/registred.js')
    .addEntry('candidateJs', './assets/dashboard/js/users/candidate.js')
    .addEntry('ApprenticeJs', './assets/dashboard/js/users/session_aprentis_list.js')
    .addEntry('session-edit-js', './assets/dashboard/js/session/sessionEdit.js')
    .addEntry('moduleSession', './assets/dashboard/js/session/module/module.js')
    .addEntry('dayCourseSession', './assets/dashboard/js/session/dayCourse/sessionDayCourse.js')
    .addEntry('addSessionRessources', './assets/dashboard/js/session/resource/addSessionRessources.js')
    .addEntry('editSessionRessources', './assets/dashboard/js/session/resource/editSessionRessources.js')
    .addEntry('deleteSessionRessources', './assets/dashboard/js/session/resource/deleteSessionRessources.js')
    .addEntry('addSessionActivity', './assets/dashboard/js/session/activity/addSessionActivity.js')
    .addEntry('editSessionActivity', './assets/dashboard/js/session/activity/editSessionActivity.js')
    .addEntry('deleteSessionActivity', './assets/dashboard/js/session/activity/deleteSessionActivity.js')
    .addEntry('editSDay', './assets/dashboard/js/session/edit_day/edit_session_day.js')
    .addEntry('addSOrder', './assets/dashboard/js/session/edit_day/addSOrder.js')
    .addEntry('datetimepicker-js', 'bootstrap4-datetimepicker/build/js/bootstrap-datetimepicker.min.js')
    .addEntry('errorPage', './assets/dashboard/js/errorPage.js')
    .addEntry('chartDashboardadmin-js', 'chartist/dist/chartist.js')
    .addEntry('formValidationSession-js', './assets/dashboard/js/session/formValidation.js')
    .addEntry('curriculumViewerJs', './assets/dashboard/js/curriculumViewer/curriculumViewer.js')
    .addEntry('fullCalendar_js', 'fullcalendar/dist/fullcalendar.js')
    .addEntry('calculRemainingTime', './assets/dashboard/js/apprenti/calculRemainingTime.js')
    .addEntry('fullCalendarDrag_js', './assets/dashboard/js/calendar/external-dragging.js')
    .addEntry('week_plannning_js', './assets/dashboard/js/calendar/week_plannning.js')
    .addEntry('addHolidays_js', './assets/dashboard/js/calendar/addHolidays.js')
    .addEntry('mentorsList_js', './assets/dashboard/js/session/MentorsList/mentorsList.js')
    .addEntry('resetPwd_js', './assets/showcase/js/login/resetPwd.js')
    .addEntry('apprentiCorrection_js', './assets/dashboard/js/apprenti/correction.js')
    .addEntry('validationsApprenti_js', './assets/dashboard/js/session/apprentice_performance/validationsApprenti.js')
    .addEntry('evaluationsApprenti_js', './assets/dashboard/js/session/apprentice_performance/evaluationsApprenti.js')
    .addEntry('session_management_evaluation_js', './assets/dashboard/js/session/session_management/session_management_evaluation.js')
    .addEntry('admin_session_list_datatable_js', './assets/dashboard/js/session/session_management/admin_session_list_datatable.js')
    .addEntry('mentor_session_list_datatable_js', './assets/dashboard/js/session/session_management/mentor_session_list_datatable.js')
    .addEntry('apprentice_performanceJs', './assets/dashboard/js/session/apprentice_performance.js')
    .addEntry('dashboardApprenti', './assets/dashboard/js/apprenti/dashboard.js')
    .addEntry('validationSessionManagement', './assets/dashboard/js/session/validationSessionManagement.js')
    .addEntry('passedSession', './assets/dashboard/js/apprenti/passedSession.js')
    .addEntry('tagsinput_js', 'bootstrap-tagsinput/dist/bootstrap-tagsinput.min.js')
    .addEntry('applyApplication_js', './assets/dashboard/js/candidates/applyApplication.js')
    .addEntry('projectSubject', './assets/dashboard/js/project/projectSubject.js')
    .addEntry('specSubjectJs', './assets/dashboard/js/project/specSubject.js')
    .addEntry('daySubjectContentJs', './assets/dashboard/js/project/dayContent.js')
    .addEntry('projectSessionSubject', './assets/dashboard/js/session/session-project/projectSessionSubject.js')
    .addEntry('specSessionSubject', './assets/dashboard/js/session/session-project/specSessionSubject.js')
    // .addEntry('multiSelect','multiselect/js/jquery.multi-select.js')
    .addEntry('affectation','./assets/dashboard/js/session/edit_day/affectation.js')

    .addEntry('preparcoursRemainingTime','./assets/dashboard/js/candidates/calculPreparcoursRemainingTime.js')
    .addEntry('preparcoursSubmitWork','./assets/dashboard/js/candidates/preparcoursSubmitWork.js')

    .addEntry('assignMentorSubject','./assets/dashboard/js/session/assignMentorSubject/assignMentorSubject.js')
    .addEntry('comment_show','./assets/dashboard/js/users/comment_show.js')
    .addEntry('progress_bar_circle', './assets/dashboard/js/progress-bar-circle.js')

    // .addEntry('switchbtn', './assets/dashboard/css/jquery.btnswitch.css')
    // When enabled, Webpack "splits" your files into smaller pieces for greater optimization.
    .splitEntryChunks()

    .addStyleEntry('cursusStyle', './assets/dashboard/css/cursus.css')
    .addStyleEntry('dayStyle', './assets/dashboard/css/day.css')
    .addStyleEntry('editDayCss', './assets/dashboard/css/edit_day.css')
    .addStyleEntry('contactUs', './assets/showcase/css/contactUs.css')
    .addStyleEntry('cursusDetail', './assets/dashboard/css/candidates/cursusDetail.css')
    .addStyleEntry('listCursusCandidat', './assets/dashboard/css/candidates/listCursusCandidat.css')
    .addStyleEntry('module', './assets/dashboard/css/module.css')
    .addStyleEntry('summernotecss', 'summernote/dist/summernote-bs4.css')
    .addStyleEntry('template_content_activitycss', './assets/dashboard/css/activity/template_content_activity.css')
    .addStyleEntry('view_profile', './assets/dashboard/css/profile/view_profile.css')
    .addStyleEntry('listCursus', './assets/dashboard/css/listCursusCandidates/listCursus.css')
    .addStyleEntry('candidatures', './assets/dashboard/css/listCursusCandidates/candidatures.css')
    .addStyleEntry('dataTable', 'datatables.net-bs4/css/dataTables.bootstrap4.css')
    .addStyleEntry('dataTable_button', 'datatables.net-buttons-dt/css/buttons.dataTables.min.css')
    .addStyleEntry('staffCss', './assets/dashboard/css/users/staff.css')
    .addStyleEntry('datetimepicker-css', 'bootstrap4-datetimepicker/build/css/bootstrap-datetimepicker.min.css')
    .addStyleEntry('session-unit-css', './assets/dashboard/css/session/session-unit.css')
    .addStyleEntry('registredCss', './assets/dashboard/css/users/registred.css')
    .addStyleEntry('candidateCss', './assets/dashboard/css/users/candidate.css')
    .addStyleEntry('chartDashboardadmin-css', 'chartist/dist/chartist.css')
    .addStyleEntry('candidatureCss', './assets/dashboard/css/candidature/candidature.css')
    .addStyleEntry('dashboardAdminCss', './assets/dashboard/css/users/dashboardAdmin.css')
    .addStyleEntry('fullCalendar_css', 'fullcalendar/dist/fullcalendar.min.css')
    .addStyleEntry('calendar_css', './assets/dashboard/css/calendar/calendar.css')
    .addStyleEntry('error', './assets/dashboard/css/error.css')
    .addStyleEntry('commonStyle', './assets/dashboard/css/commonStyle.css')
    .addStyleEntry('user_profile', './assets/dashboard/css/users/user_profile.css')
    .addStyleEntry('tooltip_datatable', './assets/dashboard/css/session/tooltip_datatable.css')


    .addStyleEntry('curriculumViewer', './assets/dashboard/css/curriculumViewer/curriculumViewer.css')
    .addStyleEntry('curriculumViewer_sideBare2', './assets/dashboard/css/curriculumViewer/curriculumViewer_sideBare2.css')
    .addStyleEntry('apprentice_performance', './assets/dashboard/css/session/apprentice_performance.css')
    .addStyleEntry('moduleSessionCss', './assets/dashboard/css/session/moduleSession.css')
    .addStyleEntry('daySessionCss', './assets/dashboard/css/session/sessionDayList.css')
    .addStyleEntry('editDaySessionCss', './assets/dashboard/css/session/editDaySession.css')
    .addStyleEntry('templateContentActivitySessionCss', './assets/dashboard/css/session/templateContentActivitySession.css')
    .addStyleEntry('apprentiDashboard_css', './assets/dashboard/css/apprenti/apprentiDashboard.css')
    .addStyleEntry('correction', './assets/dashboard/css/apprenti/correction.css')
    .addStyleEntry('applyAppCss', './assets/dashboard/css/candidature/applyApp.css')
    .addStyleEntry('tagsinput_css', 'bootstrap-tagsinput/dist/bootstrap-tagsinput-typeahead.css')
    .addStyleEntry('holidayCalendar_css', './assets/dashboard/css/holiday/holiday-calendar.css')
    // slack reaction script
    .addStyleEntry('reaction', './assets/dashboard/css/slack_reaction_css/reaction.css')
    .addStyleEntry('subject', './assets/dashboard/css/session/subject.css')
    // .addStyleEntry('multiSelectCss','multiselect/css/multi-select.css')

    // will require an extra script tag for runtime.js
    // will require an extra script tag for runtime.js
    // but, you probably want this, unless you're building a single-page app
    .enableSingleRuntimeChunk()

    /*
     * FEATURE CONFIG
     *
     * Enable & configure other features below. For a full
     * list of features, see:
     * https://symfony.com/doc/current/frontend.html#adding-more-features
     */
    .cleanupOutputBeforeBuild()
    .enableBuildNotifications()
    .enableSourceMaps(!Encore.isProduction())
    // enables hashed filenames (e.g. app.abc123.css)
    .enableVersioning(Encore.isProduction())

    // enables Sass/SCSS support
    .enableSassLoader()

    // uncomment if you use TypeScript
    //.enableTypeScriptLoader()

    // uncomment if you're having problems with a jQuery plugin
    // .autoProvidejQuery()

    // uncomment if you use API Platform Admin (composer req api-admin)
    //.enableReactPreset()
    //.addEntry('admin', './assets/dashboard/js/admin.js')
    .copyFiles({
        from: './assets/dashboard/images',

        // optional target path, relative to the output dir
        to: 'images/dashboard/[path][name].[ext]',

        // if versioning is enabled, add the file hash too
        //to: 'images/[path][name].[hash:8].[ext]',

        // only copy files matching this pattern
        //pattern: /\.(png|jpg|jpeg)$/
    })
    .copyFiles({
        from: './assets/showcase/img',

        // optional target path, relative to the output dir
        to: 'images/showcase/[path][name].[ext]',

        // if versioning is enabled, add the file hash too
        //to: 'images/[path][name].[hash:8].[ext]',

        // only copy files matching this pattern
        //pattern: /\.(png|jpg|jpeg)$/
    })

;

module.exports = Encore.getWebpackConfig();
