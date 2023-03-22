import { NgModule } from '@angular/core';
import { RouterModule, Routes } from '@angular/router';
import { AdminComponent } from './admin.component';
import { CursusAdminComponent } from './cursus-admin/cursus-admin.component';
import {DashboardAdminComponent} from "./dashboard-admin/dashboard-admin.component";
import { LessonAdminComponent } from './lesson-admin/lesson-admin.component';
import { ModuleAdminComponent } from './module-admin/module-admin.component';
import { SessiondetailsComponent } from './sessiondetails/sessiondetails.component';
import { TimelineapplicationComponent } from './timelineapplication/timelineapplication.component';
import { UsersComponent } from './users/users.component';


const routes: Routes = [
  {
    path: '',
    component: AdminComponent,
    children: [
      { path: '', component: DashboardAdminComponent },
      { path: 'cursus', component: CursusAdminComponent },
      { path: 'timeline', component: TimelineapplicationComponent },
      { path: 'utilisateurs', component: UsersComponent },
      { path: 'cursus/:cursusId/modules', component: ModuleAdminComponent },
      { path: 'lessons/:id', component: LessonAdminComponent },
      { path: 'sessiondetails', component: SessiondetailsComponent },
      { path: 'sessiondetails/:session/:id', component: SessiondetailsComponent }
    ],
  },
];

@NgModule({
  imports: [RouterModule.forChild(routes)],
  exports: [RouterModule],
})
export class AdminRoutingModule {}
