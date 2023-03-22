import { Component, OnInit } from '@angular/core';
import { ApprentiService } from '../../services/apprenti.service';
import { FormGroup } from '@angular/forms';
import { timer } from 'rxjs';
import { map } from 'rxjs/operators';
import { Router } from '@angular/router';
@Component({
  selector: 'app-dashboard',
  templateUrl: './dashboard.component.html',
  styleUrls: ['./dashboard.component.scss'],
})
export class DashboardComponent implements OnInit {
  sessionName: any;
  cursusId: any;
  NbModules: any;
  NbCourses: any;
  progression: any;
  sessionId: any;
  cursusImage: any;
  visible!: boolean;
  currentDay: any;
  dayId!: number;
  avisForm!: FormGroup;
  reviewResult: any;
  iswating!: boolean;
  starteDate!: Date;
  message!: any;
  minutes!: number;
  hours!: number;
  seconds: number = 0;
  days!: number;
  months!: number;
  counterId!: any;
  type!: boolean;
  user_data: any;
  userId!: number;
  statut!: string;
  startTime!: Date;
  repoLink: any;
  showValidation$!: any;
  showCorrection$!: any;
  hmax!: number;
  submitted!: boolean;
  hMaxCorrection!: number;
  correctionDay!: boolean;
  finishedSession: boolean = false;
  constructor(
    private apprentiService: ApprentiService,
    private router: Router,
  ) {}

  ngOnInit(): void {
    this.user_data = JSON.parse(localStorage.getItem('user_data') || '{}');
    this.userId = this.user_data.id;
    this.getCurrentDayType();
  }

 

  getWatingTime(starteDay: Date) {
    let reversedDate = starteDay
      .toString()
      .replace(/(\d{2})-(\d{2})-(\d{4})/, '$3-$2-$1');
    let difference = new Date(reversedDate).getTime() - new Date().getTime();
    this.days = Math.floor(difference / (1000 * 60 * 60 * 24));
    this.hours = Math.floor(
      (difference % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60)
    );
    this.minutes = Math.floor((difference % (1000 * 60 * 60)) / (1000 * 60));
    this.seconds = Math.floor((difference % (1000 * 60)) / 1000);
  }

  getCursusImage(id: number) {
    this.apprentiService.getCursusImage(id).subscribe((resp) => {
      let reader = new FileReader();
      reader.addEventListener(
        'load',
        () => {
          this.cursusImage = reader.result;
        },
        false
      );
      if (resp) {
        reader.readAsDataURL(resp);
      }
    });
  }

  getCurrentDayType() {
    this.apprentiService.getDayInformation(this.userId).subscribe((resp) => {
      if (resp.sessionStatus == 'started') {
        this.statut = resp.currentDay.status;
        this.dayId = resp.currentDay.id;
        this.type = this.statut == 'jour-validant';
        if (this.type) {
          this.submitted = resp.currentDay.submittedWork === '' ? false : true;
          this.repoLink = resp.currentDay.submittedWork;
        }
        this.correctionDay = this.statut == 'jour-correction';
        this.hmax = resp.currentDay.hMaxSubmit;
        this.hMaxCorrection = resp.currentDay.hMaxCorrection;
        const currentDate = new Date();
        this.startTime = new Date(
          currentDate.getFullYear(),
          currentDate.getMonth(),
          currentDate.getDate()
        );
        this.cursusId = resp.currentSession.cursusId;
        this.sessionName = resp.currentSession.sessionName;
        this.sessionId = resp.currentSession.sessionId;
        this.NbModules = resp.currentSession.nbSessionModule;
        this.NbCourses = resp.currentSession.nbSessionDay;
        this.progression = Math.round(resp.currentSession.progress);
        this.getCursusImage(this.cursusId);
        this.visible = true;
        this.showValidation$ = timer(0, 60000).pipe(
          map(() => this.getStatus())
        );
        this.showCorrection$ = timer(0, 60000).pipe(
          map(() => this.getCorrection())
        );
      } else if (resp.sessionStatus == 'waiting') {
        this.starteDate = resp.startDate;
        this.message =
          'Félicitations, Vous avez été affecté à Talan Academy cursus' +
          ' ' +
          resp.currentSession.sessionName +
          '!' +
          ' ' +
          'Votre parcours commence le' +
          ' ' +
          this.starteDate +
          '.';
        this.getWatingTime(this.starteDate);
        this.visible = false;
        this.counter();
      } else {
        this.finishedSession = true;
      }
    });
  }


  getCorrection(): boolean {
    if (this.correctionDay == false) {
      return false;
    }
    const currentTime = new Date();
    const difference = currentTime.getTime() - this.startTime.getTime();
    const hoursPassed = difference / (1000 * 60 * 60);
    return hoursPassed < this.hMaxCorrection;
  }

  getStatus(): boolean {
    if (this.type == false) {
      return false;
    }
    const currentTime = new Date();
    const difference = currentTime.getTime() - this.startTime.getTime();
    const hoursPassed = difference / (1000 * 60 * 60);
    return hoursPassed < this.hmax;
  }

  counter() {
    this.counterId = setInterval(() => {
      if (--this.seconds < 0) {
        this.seconds = 59;
        this.minutes = this.minutes - 1;
      }
      if (this.minutes < 0) {
        this.minutes = 59;
        this.hours = this.hours - 1;
      }

      if (this.hours <= 0) {
        this.hours = 0;
      }
      if (this.days <= 0) {
        this.days = 0;
      }
      if (
        this.days == 0 &&
        this.hours == 0 &&
        this.minutes == 0 &&
        this.seconds == 0
      ) {
        clearInterval(this.counterId);
      }
    }, 1000);
  }

  goToProgram() {
    this.router.navigateByUrl('apprenti/programme');
    console.log('go to program');
  }
  display: boolean = false;

  showDialog() {
    this.display = true;
  }
}
