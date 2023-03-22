import { Component, OnInit } from '@angular/core';
import { AuthService } from '../../services/auth.service';
import { FormGroup, FormBuilder, Validators, FormControl } from '@angular/forms';
import { Router } from '@angular/router';
import { DynamicDialogConfig, DynamicDialogRef } from 'primeng/dynamicdialog';
import { MessageService } from 'primeng/api';

@Component({
  selector: 'app-login',
  templateUrl: './login.component.html',
  styleUrls: ['./login.component.scss']
})
export class LoginComponent implements OnInit {
  loginForm!: FormGroup;
  submitStat = true;
  constructor(private messageService: MessageService,private authService : AuthService, private refDialog: DynamicDialogRef, private config: DynamicDialogConfig, private formBuilder: FormBuilder, private router : Router) { }

  ngOnInit(): void {
    this.loginForm = this.formBuilder.group({
      username : new FormControl('', Validators.compose([Validators.required, Validators.email])),
      password : new FormControl('', Validators.required)
    })
  }

  login() {
    this.submitStat = false;
    this.authService.login(this.loginForm.value).subscribe((response: any) => {
      localStorage.setItem('token', response.token);
      localStorage.setItem('user_data', JSON.stringify(response.data));
      this.messageService.add({severity:'success', summary: 'Succès', detail: 'Vous avez été connecté avec succès'});
      this.submitStat = true;
        this.refDialog.close();
        if(this.config.data){
          this.router.navigate([this.config.data]);
        } else {
          if(response.data.roles.includes('ROLE_ADMIN')) {
          this.router.navigate(['/admin']);
          } else if (response.data.roles.includes('ROLE_APPRENTI')) {
            this.router.navigate(['/apprenti']);
          }
        }
    }, error => {
      this.messageService.add({severity:'error', summary: 'Erreur', detail: error.error.message});
      this.submitStat = true;
    });
  }

  onClose() {
    console.log("closed");
}

}
