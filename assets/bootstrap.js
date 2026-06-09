// register any custom, 3rd party controllers here
// app.register('some_controller_name', SomeImportedController);
import { startStimulusApp } from '@symfony/stimulus-bundle';
import ThemeController from './controllers/theme_controller.js';
import ReportFormController from './controllers/report_form_controller.js';
import ReportCommentModalController from './controllers/report_comment_modal_controller.js';
import NavbarSearchController from './controllers/navbar_search_controller.js';

const app = startStimulusApp();
app.register('theme', ThemeController);
app.register('report-form', ReportFormController);
app.register('report-comment-modal', ReportCommentModalController);
app.register('navbar-search', NavbarSearchController);
