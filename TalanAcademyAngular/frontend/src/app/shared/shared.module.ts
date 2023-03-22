import { NgModule } from '@angular/core';
import { CommonModule } from '@angular/common';
import { PrimengModule } from './primeng.modules';
import { ProfileComponent } from './components/profile/profile.component';
import { ReactiveFormsModule } from '@angular/forms';
import { TreeModule } from 'primeng/tree';
import { ToastModule } from 'primeng/toast';
import { SidebarModule } from 'primeng/sidebar';
import { ChartModule } from 'primeng/chart';
import { TranslateModule } from '@ngx-translate/core/public_api';

@NgModule({
  declarations: [ProfileComponent],
  imports: [
    CommonModule,
    ReactiveFormsModule,
    PrimengModule,
    TreeModule,
    ToastModule,
    SidebarModule,
    ChartModule,
  ],
  exports: [PrimengModule],
})
export class SharedModule {}
