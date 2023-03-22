import { Component, OnInit } from '@angular/core';
import { DashboardService } from '../service/dashboard.service';
import { trigger,state,style,transition,animate } from '@angular/animations';
import { ActivatedRoute, Router } from '@angular/router';

@Component({
  selector: 'app-module-admin',
  templateUrl: './module-admin.component.html',
  styleUrls: ['./module-admin.component.scss'],
  animations: [
    trigger('rowExpansionTrigger', [
        state('void', style({
            transform: 'translateX(-10%)',
            opacity: 0
        })),
        state('active', style({
            transform: 'translateX(0)',
            opacity: 1
        })),
        transition('* <=> *', animate('400ms cubic-bezier(0.86, 0, 0.07, 1)'))
    ])
],

})
export class ModuleAdminComponent implements OnInit {

  constructor(private router : Router ,private dashBoardService : DashboardService,private activatedRoute : ActivatedRoute) { }
  cursusId : any
  loading: boolean = true;
  search: any
  moduleList : any
  // =[{'id':1,'name':'module1','description':'un bon module','lessons':[{'name':'lecon 1'},{'name':'lecon 2'},{'name':'lecon 3'},{'name':'lecon 4'},{'name':'lecon 5'},]},{'id':2,'name':'module2','description':'un module tres long','lessons':[{'name':'lecon 10'},{'name':'lecon 20'},{'name':'lecon 30'},{'name':'lecon 40'},{'name':'lecon 50'},]},{'id':3,'name':'module3','description':'le module est tres utile','lessons':[{'name':'lecon 100'},{'name':'lecon 200'},{'name':'lecon 300'},{'name':'lecon 400'},{'name':'lecon 500'},]},{'id':4,'name':'module4','description':'je aime pas ce module il est pas bon','lessons':[{'name':'lecon 1'},{'name':'lecon 2'},{'name':'lecon 3'},]},{'id':5,'name':'module5','description':'nous aimons ce module','lessons':[{'name':'lecon 1'},{'name':'lecon 2'},{'name':'lecon 3'},{'name':'lecon 4'},{'name':'lecon 5'},{'name':'lecon 6'},{'name':'lecon 7'},]},]

  ngOnInit(): void {
    this.cursusId=this.activatedRoute.snapshot.params['cursusId'];
    this.getAllModules(this.cursusId);
  }

  getAllModules(cursusId : number){
    this.dashBoardService.getCursus(cursusId).subscribe((data)=>{this.moduleList=data.modules
    this.loading=false
    console.log(this.moduleList);
    
    })
  }
  editLesson(lessonId : any){
    this.router.navigateByUrl(`admin/lessons/${lessonId}`)
  }
}
