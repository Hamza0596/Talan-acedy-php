import { Component, OnInit, Output, EventEmitter } from '@angular/core';
import { Router } from '@angular/router';
import { DashboardService } from "../service/dashboard.service";

@Component({
  selector: 'app-dashboard-admin',
  templateUrl: './dashboard-admin.component.html',
  styleUrls: ['./dashboard-admin.component.scss']
})
export class DashboardAdminComponent implements OnInit {
  loading: boolean = true;
  sessions: any = [];
  sessionPlanned!: string;
  sessionsInProgress!: number;
  sessionFinished!: string;
  cursus!: string;
  sessionsPages!: number;
  page: number = 1;
  date14!: Date;
  minDate!: Date;

  maxDate!: Date;
  search: any;
  sessions2!: any;

  invalidDates!: Array<Date>
  @Output() newItemEvent = new EventEmitter<any>();


  constructor(private dashboardService: DashboardService, private router: Router) { }

  ngOnInit(): void {
    this.getSessions();
    this.dashboardService.getCursusStatics().subscribe((resp) => {
      this.cursus = resp.cursus
      this.sessionPlanned = resp.sessionPlanned
      this.sessionsInProgress = resp.sessionsInProgress
      this.sessionFinished = resp.sessionFinished;
    });
  }
  getSessions() {
    this.dashboardService.getSessions().subscribe((resp) => {
      this.sessions = resp.result.sessionsInfo;
      // this.sessions.data = [];
      // this.sessions.options = [];
      for (let i = 0; i < this.sessions.length; i++) {
        this.sessions[i].data = {
          labels: [this.sessions[i].advancement + ' % Complete'],
          datasets: [
            {
              data: [this.sessions[i].advancement, 100 - this.sessions[i].advancement],
              backgroundColor: [
                '#3B82F6', 'white'
              ],
              hoverBackgroundColor: [
                '#3B82F6', 'white'
              ]
            }
          ]
        };
        this.sessions[i].options = {
          responsive: false,
          maintainAspectRatio: false,
          legend: {
            display: false
          },
          elements: {
            arc: {
              borderWidth: 0
            }
          },
          tooltips: {
            enabled: true
          },
          cutoutPercentage: 50,
          height: 100
        };
      }
      this.sessions2 = [];
      // for (let i = 0; i < this.sessions.length; i++) {
      //   let objt = {
      //     "id" : this.sessions[i][1].sessionId,
      //     "session" : this.sessions[i][0].sessionColumn +'#'+ this.sessions[i][0].sessionOrder ,
      //     "calendrier" : this.sessions[i][1].startDate + ' , ' +this.sessions[i][1].endDate,
      //     "apprentis" : this.sessions[i][2].nbApprentis,
      //     "score":this.sessions[i][3].averageColumnMoy,
      //     "evaluation":this.sessions[i][4].notePercentage,
      //     "progression":this.sessions[i][5].advancementColumnTwo
      //   }
      //   this.sessions2.push(objt);
      // }
      console.log("The sessions : ", this.sessions);
      //console.log( "The sessions2 : ",  this.sessions2);
      this.sessionsPages = resp.result.numberPages;
      this.loading = false;
    });
  }
  paginate(event: number) {
    this.page = event;
    this.getSessions();

  }

  goToDetails(session: any, id: any) {
    this.router.navigateByUrl(`admin/sessiondetails/${session}/${id}`);
    console.log('go to details', id);
  }

}

