import { CommonModule } from '@angular/common';
import { NgModule } from '@angular/core';
import { ApprentiRoutingModule } from './apprenti-routing.module';
import { ApprentiComponent } from './apprenti.component';
import { SharedModule } from '../shared/shared.module';
import { TreeModule } from 'primeng/tree';
import { ToastModule } from 'primeng/toast';
import { SidebarModule } from 'primeng/sidebar';
import { CourseComponent } from './course/course.component';
import { FormsModule, ReactiveFormsModule } from '@angular/forms';
import { DashboardComponent } from '../shared/components/dashboard/dashboard.component';
import { SoumissionComponent } from './soumission/soumission.component';
import { ProgrammeComponent } from './programme/programme.component';
import { CorrectionsComponent } from './corrections/corrections.component';
import { DialogModule } from 'primeng/dialog';
import Swal from 'sweetalert2';
import { BilanComponent } from './bilan/bilan.component';
import {AccordionModule} from 'primeng/accordion';
import { FullCalendarModule } from '@fullcalendar/angular';

@NgModule({
  declarations: [
    ProgrammeComponent,
    ApprentiComponent,
    CourseComponent,
    DashboardComponent,
    SoumissionComponent,
    CorrectionsComponent,
    BilanComponent,
  ],

  imports: [
    CommonModule,
    ApprentiRoutingModule,
    ReactiveFormsModule,
    SharedModule,
    TreeModule,
    ToastModule,
    SidebarModule,
    FormsModule,
    AccordionModule,
    DialogModule,
    FullCalendarModule
  ],
})
export class ApprentiModule {}
