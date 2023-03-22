import { Component, OnInit ,ViewChild,ElementRef, ChangeDetectorRef} from '@angular/core';
import { Router } from '@angular/router';
import { ConfirmationService,
  MessageService,
  PrimeNGConfig } from 'primeng/api';
import { DashboardService } from '../service/dashboard.service';
@Component({
  selector: 'app-cursus-admin',
  templateUrl: './cursus-admin.component.html',
  styleUrls: ['./cursus-admin.component.scss'],
})
export class CursusAdminComponent implements OnInit {

  constructor(private router : Router,private dashBoardService : DashboardService ) { }
  
  loading: boolean = true;
  search: any
  cursusList : any=[]
  tags: any;
  exportedCursus:any;
  cursus!:any;

  ngOnInit(): void {
    this.getAllCursus()
  }

  

  getAllCursus() {
    this.dashBoardService.getAllCursus().subscribe((data) => {
    
      this.cursusList = data.cursusList;
      
      this.cursusList.forEach((element: any) => {
        element.tagsTab = element.tags.split(',');
      });
      this.cursusList.forEach((element: any) => {
        element.description1 = element.description.substring(0, 150);
      });
      this.loading = false;
    });
  }
  editCursus(cursusId: number) {
    this.router.navigateByUrl(`admin/cursus/${cursusId}/modules`);
  }


  makePdf(cursusId: number) {
    console.log(cursusId);

    this.dashBoardService.getCursus(cursusId).subscribe(data=>{
        this.cursus=data;
    this.dashBoardService.getCususPdf(cursusId).subscribe(response => {
      const blob = response.body as Blob;
      const a = document.createElement('a');
      a.href = window.URL.createObjectURL(blob);  
      a.setAttribute("download", "Cursus"+" "+this.cursus.cursus.name);
      a.click();
    });
  })
  }
 

  changeCursusVisibility(cursus: any) {
    this.dashBoardService
      .changeCursusVisibilty(cursus.id)
      .subscribe((data: any) => (cursus.visibility = data.visibility));
  }

  showMore(index: number) {
    this.cursusList[index].description1 = this.cursusList[index].description;
  }
  showLess(index: number) {
    this.cursusList[index].description1 = this.cursusList[
      index
    ].description.substring(0, 150);
  }
}
