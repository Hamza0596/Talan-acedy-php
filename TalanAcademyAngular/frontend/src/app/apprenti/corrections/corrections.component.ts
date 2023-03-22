import { Component, ChangeDetectorRef, OnInit } from '@angular/core';
import { FormBuilder, FormGroup, Validators } from '@angular/forms';
import { MessageService } from 'primeng/api';
import { ApprentiService } from 'src/app/shared/services/apprenti.service';

@Component({
  selector: 'app-corrections',
  templateUrl: './corrections.component.html',
  styleUrls: ['./corrections.component.scss'],
})
export class CorrectionsComponent implements OnInit {
  correctedName!: string;
  gitLink!: string;
  questionList: any = [];
  isToggleable = true;
  showContent = true;
  submitted = false;
  message!: string;
  question: any;
  formGroup!: FormGroup;
  innerWidth!: number;
  constructor(
    private apprentiService: ApprentiService,
    private fb: FormBuilder,
    private messageService: MessageService,
    private cdr: ChangeDetectorRef,
  ) {}

  ngOnInit(): void {
    this.innerWidth = window.innerWidth/10
    this.apprentiService.getCorrection().subscribe((resp) => {
      this.handleMessage(resp);
      if (this.questionList !== undefined) {
        this.questionList.forEach((question: any) => {
          this.formGroup.addControl(
            question.correctionResult.toString(),
            this.fb.control(null, Validators.required)
          );
        });
      }
      this.cdr.detectChanges()
    });
    this.formGroup = this.fb.group({
      comment: ['', Validators.required],
    });
  }

  handleMessage(resp: any) {
    const message = resp.message;
    const keywords = [
      "Vous n'avez pas de correction",
      'Aucune correction trouvée',
      'Corrections ',
    ];
    if (keywords.some((word) => message.includes(word))) {
      if (message.includes("Vous n'avez pas de correction")) {
        this.message = resp.message;
        this.isToggleable = !this.isToggleable;
        this.showContent = !this.showContent;
      }
      if (message.includes('Aucune correction trouvée')) {
        this.message = resp.message;
        this.isToggleable = !this.isToggleable;
        this.showContent = !this.showContent;
      }
      if (message.includes('Corrections ')) {
        this.correctedName =
          resp.corrections[0].firstName
            .toLowerCase()
            .replace(/(^|\s)\S/g, (L: any) => L.toUpperCase()) +
          ' ' +
          resp.corrections[0].lastName
            .toLowerCase()
            .replace(/(^|\s)\S/g, (L: any) => L.toUpperCase());
        this.message = 'Vous allez corrigé à ' + this.correctedName;
        if (resp.corrections[0].correctionResults == undefined) {
          this.message =
            'Vous avez soumis votre correction à ' + this.correctedName;
          this.isToggleable = !this.isToggleable;
          this.showContent = !this.showContent;
        }
        this.gitLink = resp.corrections[0].submittedWork;
        this.questionList = resp.corrections[0].correctionResults;
      }
    }
  }

  onSubmit() {
    const corrections = Object.entries(this.formGroup.value)
      .filter(([id, result]) => id !== 'comment')
      .map(([id, result]) => ({ id: +id, result }));
    const comment = this.formGroup.value.comment;
    const formData = { comment, corrections };
    this.apprentiService.saveCorrection(formData).subscribe((resp) => {
      this.messageService.add({
        severity: 'success',
        summary: 'Succès',
        detail: 'Vous avez Soumis votre correction avec succès',
      });
      this.message =
        'Vous avez soumis votre correction à ' + this.correctedName;
      this.isToggleable = !this.isToggleable;
      this.showContent = !this.showContent;
    });
  }
}
