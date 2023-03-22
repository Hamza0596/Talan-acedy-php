import { NgModule } from '@angular/core';
import { RouterModule, Routes } from '@angular/router';
import { AccueilComponent } from './core/components/accueil/accueil.component';
import { AdminGuard } from './core/guards/admin.guard';
import { ApprentiGuard } from './core/guards/apprenti.guard';

const routes: Routes = [
  { path: '', component: AccueilComponent},
  { path: 'apprenti', loadChildren: () => import('./apprenti/apprenti.module').then(m => m.ApprentiModule) , canActivate : [ApprentiGuard]},
  { path: 'admin', loadChildren: () => import('./admin/admin.module').then(m => m.AdminModule) , canActivate: [AdminGuard] },
  { path: '**', redirectTo: 'apprenti', pathMatch: 'full' },
];

@NgModule({
  imports: [RouterModule.forRoot(routes)],
  exports: [RouterModule]
})
export class AppRoutingModule { }
