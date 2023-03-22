import { NgModule } from '@angular/core';
import { CommonModule } from '@angular/common';
import { SharedModule } from '../shared/shared.module';
import { AccueilComponent } from './components/accueil/accueil.component';
import { SignInComponent } from './components/sign-in/sign-in.component';
import { LoginComponent } from './components/login/login.component';
import { ReactiveFormsModule } from '@angular/forms';
import { HttpClientModule } from '@angular/common/http';
import { JwtHelperService, JWT_OPTIONS } from '@auth0/angular-jwt';
import { HeaderComponent } from './components/header/header.component';



@NgModule({
  declarations: [
    AccueilComponent,
    SignInComponent,
    LoginComponent,
    HeaderComponent
  ],
  imports: [
    CommonModule,
    SharedModule,
    ReactiveFormsModule,
    HttpClientModule
  ],
  exports: [
    HeaderComponent,
    AccueilComponent
  ],
  providers: [
    {provide: JWT_OPTIONS, useValue: JWT_OPTIONS},
    JwtHelperService]
})
export class CoreModule { }
