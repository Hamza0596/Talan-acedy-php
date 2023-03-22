import { Component, OnInit } from '@angular/core';
import { ConfirmationService, PrimeIcons } from 'primeng/api';
import { DashboardService } from '../service/dashboard.service';

@Component({
  selector: 'app-timelineapplication',
  templateUrl: './timelineapplication.component.html',
  styleUrls: ['./timelineapplication.component.scss'],
  providers: [ConfirmationService],
})
export class TimelineapplicationComponent implements OnInit {
  search: any;
  candidatures!: any;
  loading: boolean = true;
  productDialog!: boolean;
  events1!: any[];
  details: any;
  header!: string;

  constructor(
    private dashboardService: DashboardService,
    private confirmationService: ConfirmationService
  ) {}
  ngOnInit(): void {
    this.getAllCandidates();
  }
  getAllCandidates() {
    this.dashboardService.getAllCandidatures().subscribe((data) => {
      this.candidatures = data;
      this.loading = false;
    });
  }

  editProduct(product: any) {
    this.details = product;
    this.productDialog = true;
    this.header =
      'TimeLine Candidature ' +
      product.candidat.firstName
        .toLowerCase()
        .replace(/(^|\s)\S/g, (L: any) => L.toUpperCase()) +
      ' ' +
      product.candidat.lastName
        .toLowerCase()
        .replace(/(^|\s)\S/g, (L: any) => L.toUpperCase());
        console.log(window.innerWidth);
        
      if (product.status == "accepted") {  
        this.events1 = [
          {
            status: 'Inscription',
            date: product.candidat.registrationDate.split('T')[0].split('-')[2] + '/'+ product.candidat.registrationDate.split('T')[0].split('-')[1] + '/' + product.candidat.registrationDate.split('T')[0].split('-')[0]  + ' ' +product.candidat.registrationDate.split('T')[1].split(':').slice(0, 2).join(':'),
            icon: PrimeIcons.ID_CARD,
            color: '#9C27B0',
          },
          {
            status: 'Candidature',
            date: product.datePostule.split('T')[0].split('-')[2] + '/'+ product.datePostule.split('T')[0].split('-')[1] + '/' + product.datePostule.split('T')[0].split('-')[0]  + ' ' +product.datePostule.split('T')[1].split(':').slice(0, 2).join(':'),
            icon: PrimeIcons.FILE_EDIT,
            color: '#673AB7',
          },
          {status: 'Entretien', date: '15/01/2023 16:15', icon: PrimeIcons.PHONE, color: '#FF9800'},
            {status: 'Acceptation', date: '20/01/2023 10:00', icon: PrimeIcons.CHECK, color: '#607D8B'}
        ];
      } else {

        this.events1 = [
          {
            status: 'Inscription',
            date: product.candidat.registrationDate.split('T')[0].split('-')[2] + '/'+ product.candidat.registrationDate.split('T')[0].split('-')[1] + '/' + product.candidat.registrationDate.split('T')[0].split('-')[0]  + ' ' +product.candidat.registrationDate.split('T')[1].split(':').slice(0, 2).join(':'),
            icon: PrimeIcons.ID_CARD,
            color: '#9C27B0',
          },
          {
            status: 'Candidature',
            date: product.datePostule.split('T')[0].split('-')[2] + '/'+ product.datePostule.split('T')[0].split('-')[1] + '/' + product.datePostule.split('T')[0].split('-')[0]  + ' ' +product.datePostule.split('T')[1].split(':').slice(0, 2).join(':'),
            icon: PrimeIcons.FILE_EDIT,
            color: '#673AB7',
          }
        ];
      }
   

  }
  showMore() {}
  hideDialog() {
    this.productDialog = false;
  }
}
