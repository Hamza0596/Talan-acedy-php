import { Component, OnInit } from '@angular/core';
import { ActivatedRoute } from '@angular/router';
import { DashboardService } from '../service/dashboard.service';

@Component({
  selector: 'app-sessiondetails',
  templateUrl: './sessiondetails.component.html',
  styleUrls: ['./sessiondetails.component.scss']
})
export class SessiondetailsComponent implements OnInit {

  sessionId!: number;
  sessionStudents: any = [];
  session!: string;

  sessionValidations: any = [];

  sessionEvaluations: any[] = [];

  sessionComments: any[] = [];

  page: number = 1;
  loading: boolean = true;
  searchStudents!: any;
  searchValidations!: any;
  searchEvaluations!: any;

  displayRates!: boolean;
  val5: number = 5;
  val4: number = 4;
  val3: number = 3;
  val2: number = 2;
  val1: number = 1;

  displayComments!: boolean;
  commentaires: any[] = [];
  commentairesExist: boolean = false;
  course!: string;

  displayEmojis!: boolean;

  sessionIdToCheck!:number;

  // notes: any = [];
  // selectedNote: any = {
  //   name: null, img: null
  // };
  // a: boolean = false;
  // b: boolean = false;
  // c: boolean = false;
  // d: boolean = false;
  constructor(private dashboardService: DashboardService, private activatedRoute: ActivatedRoute) { }
  ngOnInit(): void {
    // this.notes = [
    //   { name: 'Passable', img: 'confused' },
    //   { name: 'Bien', img: 'smiley' },
    //   { name: 'Très bien', img: 'smile' },
    //   { name: 'Excellent', img: 'sunglasses' }
    // ];
    this.sessionId = this.activatedRoute.snapshot.params['id'];
    this.session = this.activatedRoute.snapshot.params['session'];
    this.dashboardService.getSessionById(this.sessionId).subscribe(resp => {
      this.sessionStudents = resp.students;
      for (let c = 0; c < this.sessionStudents.length; c++) {
        this.sessionStudents[c].color = "gray"
      }
      this.sessionValidations = resp.validations;
      this.sessionEvaluations = resp.reviews;
      for (let ev = 0; ev < this.sessionEvaluations.length; ev++) {
        this.sessionEvaluations[ev].id = ev+1;
      }
      //this.sessionComments = resp.reviews.comments;
      for (let i = 0; i < this.sessionEvaluations.length; i++) {
        if (this.sessionEvaluations[i].comments) {
          for (let j = 0; j < this.sessionEvaluations[i].comments.length; j++) {
            let obj = {
              'course': this.sessionEvaluations[i].course,
              'comments': this.sessionEvaluations[i].comments[j].comment
            }
            this.sessionComments.push(obj);
          }
        }
      }
      console.log(this.sessionEvaluations[0].ratingDetails.stars["5"]);
      console.log("Students", this.sessionStudents);
      console.log("Validations", this.sessionValidations);
      console.log("Evaluations", this.sessionEvaluations);
      console.log("Comments", this.sessionComments);
      this.loading = false;
    }, (error) => {
      this.loading = false;
      console.log("ERROR", error);
    }
    )
  }

  paginate(event: number) {
    this.page = event;
  }

  evaluationIdToCheck!:number;

  showRates(id:number) {
    this.displayRates = true;
    this.evaluationIdToCheck =id;
    return this.evaluationIdToCheck;
  }
  hideRates() {
    this.displayRates = false;
  }

  changeColor() {
    console.log("Modifier la couleur");
  }

  showComments(c: string) {
    this.commentaires = [];
    this.displayComments = true;
    this.course = c;
    for (let k = 0; k < this.sessionComments.length; k++) {
      if (this.sessionComments[k].course == c) {
        this.commentaires.push(this.sessionComments[k].comments);
      }
    }
    if (this.commentaires.length > 0) {
      this.commentairesExist = true;
    }
    console.log("le cours est ", c);
    console.log("les commentairse ", this.commentaires);
  }

  // onNoteSelect(event: any) {
  //   console.log("selectedNote a changé :", this.selectedNote);
  //   switch (this.selectedNote.img) {
  //     case 'confused':
  //       this.a = true; this.b = false; this.c = false; this.d = false;
  //       break;
  //     case 'smiley':
  //       this.a = false; this.b = true; this.c = false; this.d = false;
  //       break;
  //     case 'smile':
  //       this.a = false; this.b = false; this.c = true; this.d = false;
  //       break;
  //     case 'sunglasses':
  //       this.a = false; this.b = false; this.c = false; this.d = true;
  //       break;
  //     default:
  //       break;
  //   }
  // }

  showEmojis(id:number) {
      this.displayEmojis = true;
      this.sessionIdToCheck =id;
      return this.sessionIdToCheck;
  }


}
