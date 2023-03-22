import { NgModule } from '@angular/core';
import { RouterModule, Routes } from '@angular/router';
import { ProfileComponent } from '../shared/components/profile/profile.component';
import { ApprentiComponent } from './apprenti.component';
import { RessourceComponent } from './ressource/ressource.component';
import { DashboardComponent } from '../shared/components/dashboard/dashboard.component';
import { SoumissionComponent } from './soumission/soumission.component';
import { CourseComponent } from './course/course.component';
import { ProgrammeComponent } from './programme/programme.component';
import { BilanComponent } from './bilan/bilan.component';

const routes: Routes = [
  {
    path: '',
    component: ApprentiComponent,
    children: [
      { path: '', component: DashboardComponent },
      { path: 'profile', component: ProfileComponent },
      { path: 'dashboard', component: BilanComponent },
      { path: 'ressource', component: RessourceComponent },
      { path: 'course', component: CourseComponent },
      { path: 'soumission', component: SoumissionComponent },
      { path: 'programme', component: ProgrammeComponent}
    ],
  },
];

@NgModule({
  imports: [RouterModule.forChild(routes)],
  exports: [RouterModule],
})
export class ApprentiRoutingModule {
  window = window;
}
