import { Component, OnInit } from '@angular/core';
import { FormBuilder,
  FormControl,
  FormGroup,
  Validators, } from '@angular/forms';
import { ActivatedRoute } from '@angular/router';
  import { ConfirmationService,
    MessageService,
    PrimeNGConfig } from 'primeng/api';
import { DashboardService } from '../service/dashboard.service';

@Component({
  selector: 'app-lesson-admin',
  templateUrl: './lesson-admin.component.html',
  styleUrls: ['./lesson-admin.component.scss']
})
export class LessonAdminComponent implements OnInit {

  constructor(
    private dashBoardService : DashboardService, 
    private formBuilder : FormBuilder,
    private confirmationService: ConfirmationService,
    private messageService: MessageService,
    private primengConfig: PrimeNGConfig,
    private activatedRoute : ActivatedRoute
    ) { }
  lessonId! : number;
  lesson :any={}
  activityForm! : FormGroup
  ressourceForm! : FormGroup
  activityDialog!: boolean;
  ressourceDialog!: boolean;
  editRessourceDialog!:boolean;
  editActivityDialog!:boolean;
  modified : boolean = false


  ngOnInit(): void {
    
    this.lessonId = this.activatedRoute.snapshot.params['lessonId']
    this.getLesson(this.lessonId)
    this.primengConfig.ripple = true;
    this.activityForm = this.formBuilder.group({
      index: new FormControl(),
      title: new FormControl(
        '',
        Validators.compose([Validators.required, Validators.minLength(3)])
      ),
    });

    this.ressourceForm = this.formBuilder.group({
      index: new FormControl(),
      title: new FormControl(
        '',
        Validators.compose([Validators.required, Validators.minLength(3)])
      ),
      url: new FormControl(
        '',
        Validators.compose([
          Validators.required,
          Validators.pattern(
            /^(http:\/\/www\.|https:\/\/www\.|http:\/\/|https:\/\/)?[a-z0-9]+([\-\.]{1}[a-z0-9]+)*\.[a-z]{2,5}(:[0-9]{1,5})?(\/.*)?$/
          ),
        ])
      ),
    });

    this.ressourceForm.valueChanges.subscribe(() => {
      this.modified = true;
    });
    this.activityForm.valueChanges.subscribe(() => {
      this.modified = true;
    });
  }

  // To complete when we get the complete API for lessons
  getLesson(lessonId : number){
    this.dashBoardService.getCursus(1).subscribe(data=>{this.lesson=data.modules[0].DayCourses[0];
    });
    
    
  }

  addActivity(){

    this.activityDialog = true;
   

  }
  hideActivityDialog() {
    this.activityForm.reset()
    this.activityDialog = false;
}
saveActivity(){
  this.lesson.activities[this.lesson.activities.length]=this.activityForm.value
  this.activityDialog = false;
  this.messageService.add({
    severity: "success",
    detail: "Ajout effectué avec succès"
  });
}


addRessource(){
 this.ressourceDialog = true;
}

editRessource(resource : any,index : any){
  this.ressourceForm.setValue({index:`${index}`,title:`${resource.title}`,url : `${resource.url}`})
  this.modified=false
  this.editRessourceDialog = true;
}

editActivity(activity : any,index : any){
  this.activityForm.setValue({index:`${index}`,title:`${activity.title}`})
  this.modified=false
  this.editActivityDialog = true;
}

hideRessourceDialog() {
  this.ressourceDialog = false;
  this.ressourceForm.reset()
}
saveRessource(){
  this.lesson.ressources[this.lesson.ressources.length]=this.ressourceForm.value
  this.ressourceDialog = false;
  this.ressourceForm.reset();
  this.messageService.add({
    severity: "success",
    detail: "Ajout effectué avec succès"
  });
  
}
saveEditRessource(){
  this.lesson.ressources[this.ressourceForm.value.index].title=this.ressourceForm.value.title
  this.lesson.ressources[this.ressourceForm.value.index].url=this.ressourceForm.value.url
  this.editRessourceDialog = false;
  this.ressourceForm.reset()
  this.messageService.add({
    severity: "success",
    detail: "Modification effectuée avec succès"
  });
}
hideEditRessourceDialog(){
  this.editRessourceDialog = false;
  this.ressourceForm.reset()
}
    

saveEditActivity(){
  this.lesson.activities[this.activityForm.value.index].title=this.activityForm.value.title
  this.editActivityDialog = false;
  this.activityForm.reset()
  this.messageService.add({
    severity: "success",
    detail: "Modification effectuée avec succès"
  });
}
hideEditActivityDialog(){
  this.editActivityDialog = false;
  this.activityForm.reset()
}

deleteRessource(event: any,index:any) {
  this.confirmationService.confirm({
    target: event.target,
    message: "Êtes-vous sûr(e) de vouloir supprimer?",
    icon: "pi pi-exclamation-triangle",
    acceptLabel:'Confirmer',
    acceptIcon:'pi pi-check',
    rejectLabel:'Annuler',
    rejectIcon:'pi pi-times',
    
    accept: () => {
      this.lesson.ressources.splice(index, 1);
      this.messageService.add({
        severity: "success",
        detail: "Suppression effectuée avec succès"
      });
    },
  });
}
deleteActivity(event: any,index:any) {
  this.confirmationService.confirm({
    target: event.target,
    message: "Êtes-vous sûr(e) de vouloir supprimer?",
    icon: "pi pi-exclamation-triangle",
    acceptLabel:'Confirmer',
    acceptIcon:'pi pi-check',
    rejectLabel:'Annuler',
    rejectIcon:'pi pi-times',
    
    accept: () => {
      this.lesson.activities.splice(index, 1);
      this.messageService.add({
        severity: "success",
        detail: "Suppression effectuée avec succès"
      });
    },
  });
}

}

  

