import { Component, OnInit } from '@angular/core';
import { DashboardService } from '../service/dashboard.service';
import {
  FormGroup,
  FormBuilder,
  Validators,
  FormControl,
} from '@angular/forms';
import {
  MessageService,
  ConfirmationService,
  PrimeNGConfig,
} from 'primeng/api';
import { TranslateService } from '@ngx-translate/core';

@Component({
  selector: 'app-users',
  templateUrl: './users.component.html',
  styleUrls: ['./users.component.scss'],
  providers: [MessageService, ConfirmationService],
})
export class UsersComponent implements OnInit {
  constructor(
    private dashboardService: DashboardService,
    private formBuilder: FormBuilder,
    private messageService: MessageService,
    private confirmationService: ConfirmationService,
    private translateService: TranslateService,
    private config: PrimeNGConfig
  ) {
    this.translateService.setDefaultLang('fr');
  }

  emailForm!: FormGroup;
  addUserForm!: FormGroup;
  users: any;
  selectedRole!: any;
  searchText: any;
  cursusOptions!: any[];
  displayBasic!: boolean;
  userDialog!: boolean;
  userTochange!: any;

  filterRoles: any[] = [
    { name: 'Candidat', value: ['ROLE_CANDIDAT'] },
    { name: 'Admin', value: ['ROLE_ADMIN'] },
    { name: 'Apprenti', value: ['ROLE_APPRENTI'] },
    { name: 'Mentor', value: ['ROLE_MENTOR'] },
    { name: 'Inscrit', value: ['ROLE_INSCRIT'] },
  ];

  newUserRoles: any[] = [
    { name: 'Admin', value: 'Administrator' },
    { name: 'Mentor', value: 'Mentor' },
  ];

  ngOnInit(): void {
    this.getUsers();

    this.emailForm = this.formBuilder.group({
      email: new FormControl(
        '',
        Validators.compose([Validators.required, Validators.email])
      ),
    });

    this.addUserForm = this.formBuilder.group({
      lastName: new FormControl('', [Validators.required]),
      firstName: new FormControl('', [Validators.required]),
      email: new FormControl('', [Validators.required, Validators.email]),
      function: new FormControl('', [Validators.required]),
      cursus: new FormControl(''),
    });
    this.translateService
      .get('primeng')
      .subscribe((res) => this.config.setTranslation(res));
  }

  getUsers() {
    this.dashboardService.getUsers().subscribe((res) => {
      this.users = res;
      this.users.forEach(
        (user: { registrationDate: string | number | Date }) =>
          (user.registrationDate = new Date(user.registrationDate))
      );
    });
  }

  addUser() {
    this.dashboardService.addStaff(this.addUserForm.value).subscribe((data) => {
      this.messageService.add({
        severity: 'success',
        summary: 'Succés',
        detail: data.message,
        life: 2000,
      });
      this.getUsers();
      this.addUserForm.reset();
    });

    this.userDialog = false;
  }

  changeActivation(element: any) {
    this.dashboardService.changeActivation(element.id).subscribe((data) => {
      element.isActivated = data.isActivated;
      this.messageService.add({
        severity: 'success',
        summary: 'Succés',
        detail: data.message,
        life: 2000,
      });
    });
  }

  changeEmail() {
    let email = { email: this.emailForm.value.email };
    this.dashboardService.changeEmail(this.userTochange.id, email).subscribe(
      (data) => {
        this.users.find(
          (el: { id: any }) => el.id == this.userTochange.id
        ).email = data.email;
        this.messageService.add({
          severity: 'success',
          summary: 'Succés',
          detail: data.message,
          life: 2000,
        });
      },
      (err) =>
        this.messageService.add({
          severity: 'error',
          summary: 'Erreur',
          detail: err.error.message,
          life: 2000,
        })
    );
    this.displayBasic = false;
  }

  showBasicDialog(selected: any) {
    this.displayBasic = true;
    this.emailForm.setValue({ email: selected.email });
    this.userTochange = selected;
    console.log(this.userTochange);
  }

  openNew() {
    this.userDialog = true;
  }

  hideDialog() {
    this.displayBasic = false;

    this.userDialog = false;
  }

  getAllCursus() {
    this.selectedRole = this.addUserForm.value.function;
    if (this.selectedRole == 'Mentor') {
      this.addUserForm.get('cursus')?.setValidators(Validators.required);
      this.dashboardService.getAllCursus().subscribe((data) => {
        this.cursusOptions = data.cursusList;
      });
    }
  }

  confirmUpdateEmail() {
    this.confirmationService.confirm({
      message: 'Vous êtes sûr de vouloir mettre à jour cet email ?',
      header: 'Confirmation',
      icon: 'pi pi-exclamation-triangle',
      accept: () => {
        this.changeEmail();
      },
    });
  }

  confirmUpdateActivation(element: any) {
    let activationAction;
    if (element.isActivated) {
      activationAction = 'désactiver';
    } else activationAction = 'activer';
    this.confirmationService.confirm({
      message: 'Etes-vous sûr de vouloir ' + activationAction + ' le compte ?',
      header: 'Confirmation',
      icon: 'pi pi-exclamation-triangle',
      accept: () => {
        this.changeActivation(element);
      },
    });
  }



  
}
