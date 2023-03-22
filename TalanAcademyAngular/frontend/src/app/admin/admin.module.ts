import { NgModule } from '@angular/core';
import { CommonModule } from '@angular/common';
import { AdminRoutingModule } from './admin-routing.module';
import { AdminComponent } from './admin.component';
import { SharedModule } from '../shared/shared.module';
import { CursusAdminComponent } from './cursus-admin/cursus-admin.component';
import { UsersComponent } from './users/users.component';
import { ModuleAdminComponent } from './module-admin/module-admin.component';
import { SessiondetailsComponent } from './sessiondetails/sessiondetails.component';
import { MessageService, ConfirmationService } from 'primeng/api';
import { AngularEmojisModule } from 'angular-emojis';
import { LessonAdminComponent } from './lesson-admin/lesson-admin.component';
import { AngularEditorModule } from '@kolkov/angular-editor';
import { TimelineapplicationComponent } from './timelineapplication/timelineapplication.component';
import { TranslateLoader, TranslateModule } from '@ngx-translate/core';
import { TranslateHttpLoader } from '@ngx-translate/http-loader';
import { HttpClient } from '@angular/common/http';

@NgModule({
  declarations: [
    ModuleAdminComponent,
    AdminComponent,
    CursusAdminComponent,
    UsersComponent,
    SessiondetailsComponent,
    LessonAdminComponent,
    TimelineapplicationComponent,
  ],
  imports: [
    AngularEditorModule,
    CommonModule,
    AdminRoutingModule,
    SharedModule,
    TranslateModule.forChild({
      loader: {
        provide: TranslateLoader,
        useFactory: (http: HttpClient) => {
          return new TranslateHttpLoader(http, './assets/i18n/', '.json');
        },
        deps: [HttpClient],
      },
    }),

    AngularEmojisModule,
  ],
  providers: [MessageService, ConfirmationService],
})
export class AdminModule {}
