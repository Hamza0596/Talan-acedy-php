import { Component, Input, OnInit } from '@angular/core';
import {
  FormBuilder,
  FormControl,
  FormGroup,
  Validators,
} from '@angular/forms';
import { MessageService } from 'primeng/api';
import { SoumissionService } from '../service/soumission.service';
import { ApprentiService } from '../../shared/services/apprenti.service';
@Component({
  selector: 'app-soumission',
  templateUrl: './soumission.component.html',
  styleUrls: ['./soumission.component.scss'],
})
export class SoumissionComponent implements OnInit {
  @Input() dayId!: number;
  soumissionForm!: FormGroup;
  displayResponsive: boolean = false;
  @Input() repoLink: any;
  status!: string;
 @Input() submitted!: boolean;
  constructor(
    private messageService: MessageService,
    private formBuilder: FormBuilder,
    private soumissionService: SoumissionService
  ) {}

  ngOnInit(): void {
    this.soumissionForm = this.formBuilder.group({
      repoLink: new FormControl('', [
        Validators.required,
        Validators.pattern(/^https?:\/\//),
      ]),
    });
    this.submit(this.dayId);
    this.soumissionForm.patchValue({
      repoLink: this.repoLink,
    });
  }

  showResponsiveDialog() {
    this.displayResponsive = true;
  }
  submit(id: number) {
    if (this.soumissionForm.touched) {
      if (this.soumissionForm.valid) {
        this.soumissionService
          .submitGitLink(id, this.soumissionForm.value)
          .subscribe(
            (response: any) => {
              this.messageService.add({
                severity: 'success',
                summary: 'Succès',
                detail: response.message,
              });
              this.displayResponsive = false;
              this.submitted = true ;
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
        this.soumissionForm.markAllAsTouched();
      }
    }
  }
}
