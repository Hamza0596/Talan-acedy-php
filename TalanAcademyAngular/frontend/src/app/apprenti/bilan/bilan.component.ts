import { Component, OnInit } from '@angular/core';
import { Router } from '@angular/router';
import { Chart } from 'chart.js';
import { MessageService } from 'primeng/api';
import { ApprentiService } from 'src/app/shared/services/apprenti.service';
import { ProfilService } from 'src/app/shared/services/profil.service';

@Component({
  selector: 'app-bilan',
  templateUrl: './bilan.component.html',
  styleUrls: ['./bilan.component.scss'],
})
export class BilanComponent implements OnInit {
  user_data: any;
  image: any;
  user: any;
  imageToShow: any;
  profileImageExist!: boolean;
  dashboardInfo: any;
  roundProgress!: number;
  userId!: number;
  allCorrections: any[] = [];
  display: boolean = false;
  display2: boolean = false;
  display3: boolean = false;
  displayReclam: boolean = false;
  correction: any;
  correctionIndex!: number;
  yAxe: any[] = [];
  xAxe: any[] = [];
  corrections: any;
  ordersAndResult: any[] = [];
  totalCorrection: any[] = [];
  selectedCorrection: any[] = [];
  comment: any[] = [];
  allComment: any[] = [];
  comments: any[] = [];
  displayGit: boolean = false;
  repo: any;
  allRepo: any[] = [];
  repos: any;
  allModule: any[] = [];
  filterModule: any[] = [];

  constructor(
    private router: Router,
    private ApprentiService: ApprentiService,
    private ProfilService: ProfilService,
    private messageService: MessageService
  ) {}

  ngOnInit(): void {
    this.getDashboard();
    this.user_data = localStorage.getItem('user_data');
    this.user = JSON.parse(this.user_data);
    this.userId = this.user.id;
    this.getAllCorrection(this.userId);
    if (this.user.image) {
      this.getImage();
    }
  }

  getImage() {
    this.ProfilService.getImage().subscribe((image) => {
      if (image) {
        this.createImageFromBlob(image);
        this.profileImageExist = true;
      } else {
        this.imageToShow = `${this.user.firstName![0]}${
          this.user.lastName![0]
        }`;
        this.profileImageExist = false;
      }
    });
  }
  profile(): void {
    this.router.navigate(['apprenti/profile']);
  }
  ressource() {
    this.router.navigate(['apprenti/ressource']);
  }
  AddRessource() {
    this.router.navigate(['apprenti/course']);
  }
  getDashboard() {
    this.ApprentiService.getSession().subscribe(
      (resp) => {
        this.dashboardInfo = resp.dashboard;
        this.roundProgress = Math.round(resp.dashboard.progress);
      },
      (error) => {
        this.messageService.add({
          severity: 'info',
          summary: 'Informations',
          detail: error.error.message,
        });
      }
    );
  }
  Buttons() {
    document.getElementsByClassName('p-button-label')[0].innerHTML = 'Effacer';
    document.getElementsByClassName('p-button-label')[1].innerHTML = 'Chercher';
    var all = document.getElementsByClassName(
      'ng-trigger ng-trigger-overlayAnimation ng-tns-c118-5 p-column-filter-overlay p-component p-fluid p-column-filter-overlay-menu ng-star-inserted'
    )[0];
    all.setAttribute('id', 'width-update');
    var element = document.getElementById('width-update');
    element!.style.minWidth = '230px';
  }

  getAllCorrection(id: number) {
    this.ApprentiService.getAllCorrection(id).subscribe(
      (resp) => {
        this.allCorrections = resp.corrections;

        for (let i = 0; i < this.allCorrections.length; i++) {
          this.allModule.push(this.allCorrections[i].module);
        }

        this.filterModule = Array.from(new Set(this.allModule)).map(
          (module) => ({ name: module, value: `${module}` })
        );

        this.repo = resp.corrections.repoLink;

        for (let i = 0; i < this.allCorrections.length; i++) {
          this.allCorrections[i].index = i;
          this.correction = this.allCorrections[i].corrections;

          this.allRepo.push(this.allCorrections[i].repoLink);
          if (this.correction) {
            for (let i = 0; i < this.correction.length; i++) {
              this.comment = this.correction[i].comment;
              this.allComment.push(this.comment);
            }
            for (const orderAndResult of this.correction) {
              this.ordersAndResult = orderAndResult.ordersAndResult;
              this.totalCorrection.push(this.ordersAndResult);
            }
          }
        }

        let xValues = [];
        let yValues = [];
        for (let i = 0; i < this.allCorrections.length; i++) {
          xValues.push(i + 1);
          yValues.push(this.allCorrections[i].average);
        }

        new Chart('myChart', {
          type: 'line',
          data: {
            labels: xValues,
            datasets: [
              {
                fill: false,

                borderColor: '#004d9d',
                borderWidth: 1,
                data: yValues,
                label: 'Mon score %',
              },
            ],
          },
        });
      },
      (error) => {
        if (error.status === 404) {
          this.messageService.add({
            severity: 'info',
            summary: 'Informations',
            detail: 'Pas de session en cours',
          });
        } else if (error.status === 500) {
          this.messageService.add({
            severity: 'error',
            summary: 'Informations',
            detail: 'Problème serveur, réessayer plus tard!!',
          });
        }
      }
    );
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

  generateArray(length: number) {
    return new Array(length);
  }

  showDialog(index: number) {
    if (this.allCorrections[index].corrections == undefined) {
  this.comments = [];
    } else {
     
      this.comments = this.allCorrections[index].corrections[0].comment;
    }
    this.display = true;
  }
  showReclam() {
    this.displayReclam = true;
  }
  showDialog3(index: number) {
    this.display3 = true;
  }
  showGit(index: number) {
    this.displayGit = true;
    this.repos = this.allRepo[index];
  }
  showDialog2(index: number) {
    if (this.allCorrections[index].corrections == undefined) {
      this.selectedCorrection = [];
    } else {
      this.selectedCorrection =
        this.allCorrections[index].corrections[0].ordersAndResult;
    }
    this.display2 = true;
  }
}
