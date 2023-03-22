import { Component, OnInit } from '@angular/core';
import {
  AbstractControl,
  FormBuilder,
  FormControl,
  FormGroup,
  Validators,
} from '@angular/forms';
import { Router } from '@angular/router';
import { MessageService } from 'primeng/api';
import { Apprenti } from '../../models/apprenti';
import { ProfilService } from '../../services/profil.service';

@Component({
  selector: 'app-profile',
  templateUrl: './profile.component.html',
  styleUrls: ['./profile.component.scss'],
})
export class ProfileComponent implements OnInit {
  apprenti: Apprenti = {};
  apprentiForm!: FormGroup;
  passwordForm!: FormGroup;
  changedImage = false;
  imageToShow: any;
  errorImageType = false;

  constructor(
    private messageService: MessageService,
    private formBuilder: FormBuilder,
    private profilService: ProfilService,    private router: Router,
  ) {}

  ngOnInit(): void {
    this.apprenti = JSON.parse(localStorage.getItem('user_data') || '{}');
    if (this.apprenti.image) {
      this.getImage();
    }
    this.apprentiForm = this.formBuilder.group({
      firstName: new FormControl('', [
        Validators.required,
        Validators.minLength(3),
      ]),
      lastName: new FormControl('', [
        Validators.required,
        Validators.minLength(3),
      ]),
      tel: new FormControl('', [
        Validators.required,
        Validators.min(10000000),
        Validators.max(99999999),
      ]),
      linkedin: new FormControl('', [
        Validators.pattern(
          /^http(s)?:\/\/([\w]+\.)?linkedin\.com\/[A-Za-z0-9_-]+\/?/
        ),
      ]),
      email: new FormControl(
        { value: '', disabled: true },
        Validators.required
      ),
    });
    this.apprentiForm.patchValue({ ...this.apprenti });

    this.passwordForm = this.formBuilder.group({
      oldpassword: new FormControl('', [
        (c: AbstractControl) => Validators.required(c),
        Validators.pattern(
          /(?=.*\d)(?=.*[a-z])(?=.*[A-Z])(?=.*[@$!%*#?&^_-]).{8,}/
        ),
      ]),
      password: new FormControl('',
        [
          (c: AbstractControl) => Validators.required(c),
          Validators.pattern(
            /(?=.*\d)(?=.*[a-z])(?=.*[A-Z])(?=.*[@$!%*#?&^_-]).{8,}/
          ),
        ]),

      confirmPassword: new FormControl('', Validators.required),
    });
  }

  getImage() {
    this.profilService.getImage().subscribe((image) => {
      this.createImageFromBlob(image);
    });
  }

  changeImage(event: any) {
    const file = event.target.files[0];
    let allImages: Array<string> = ['png', 'jpg', 'jpeg', 'gif', 'tiff', 'bpg'];
    if (allImages.indexOf(file.type.split('/')[1]) === -1) {
      this.errorImageType = true;
      setTimeout(() => {
        this.errorImageType = false;
      }, 3000);
    } else {
      this.errorImageType = false;
      this.changedImage = true;
      this.createImageFromBlob(file);
      const formData = new FormData();
      formData.append('image', file);
      this.profilService.changeImage(formData).subscribe((response: any) => {
        this.messageService.add({
          severity: 'success',
          summary: 'Succès',
          detail: response.message,
        });
      });
    }
  }

  createImageFromBlob(image: Blob) {
    let reader = new FileReader();
    reader.addEventListener(
      'load',
      () => {
        this.imageToShow = reader.result;
      },
      false
    );

    if (image) {
      reader.readAsDataURL(image);
    }
  }

  submit() {
    if (this.apprentiForm.touched) {
      if (this.apprentiForm.valid) {
        this.profilService
          .updateApprentiProfil(this.apprentiForm.value)
          .subscribe(
            (response: any) => {
              localStorage.setItem(
                'user_data',
                JSON.stringify(response.result)
              );
              this.messageService.add({
                severity: 'success',
                summary: 'Succès',
                detail: response.message,
              });
            },
            (error) => {
              this.messageService.add({
                severity: 'error',
                summary: 'Erreur',
                detail: error.error.message,
              });
            }
          );
      } else {
        this.apprentiForm.markAllAsTouched();
      }
    }
  }

  changePassword() {
    if (
      this.passwordForm.valid &&
      this.passwordForm.value.password ===
        this.passwordForm.value.confirmPassword
    ) {
      this.profilService
        .updateApprentiPassword(this.passwordForm.value)
        .subscribe(
          (response: any) => {
            console.log('res', response);
            this.messageService.add({
              severity: 'success',
              summary: 'Succès',
              detail: response.message,
            });
            this.passwordForm.reset();
          },
          (error) => {
            console.log('error', error);
            this.messageService.add({
              severity: 'error',
              summary: 'Erreur',
              detail: error.error.message,
            });
          }
        );
    } else {
      this.passwordForm.markAllAsTouched();
    }
  }
  back(){    this.router.navigate(['apprenti/dashboard']);}
}
