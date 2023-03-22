import {
  Component,
  OnInit,
  ElementRef,
  ViewChild,
  HostListener,
  ChangeDetectorRef,
  AfterViewInit,
} from '@angular/core';
import { Router } from '@angular/router';
import { MessageService, PrimeNGConfig, TreeNode } from 'primeng/api';
import { ApprentiService } from 'src/app/shared/services/apprenti.service';
import { RessourceService } from '../service/ressource.service';
import { Dialog } from 'primeng/dialog';
import { OverlayPanel } from 'primeng/overlaypanel';
import {
  FormBuilder,
  FormControl,
  FormGroup,
  Validators,
} from '@angular/forms';
import { HttpErrorResponse } from '@angular/common/http';
import { Tree } from 'primeng/tree';

@Component({
  selector: 'app-course',
  templateUrl: './course.component.html',
  styleUrls: ['./course.component.scss'],
})
export class CourseComponent implements OnInit {
  @ViewChild('popup', { static: false }) popup!: Dialog;
  @ViewChild('synopsisId', { static: false, read: ElementRef })
  synopsisId!: ElementRef;
  list: any = [];
  listResource: any = [];
  test: any;
  nodes!: TreeNode[];
  synopsis: any;
  selectedSynopsis: any;
  description!: string;
  selectedSyno: any;
  visibleSidebar: boolean = false;
  window = window;
  selectedProduct1!: any;
  currentDayModule!: string;
  currentDayLesson!: string;
  selectedFile: any;
  toggle = true;
  goToCalled!: boolean;
  displayNoteLesson!: boolean;
  id: number = 0;
  titleVisible = false;
  score: number = 0;
  innerWidth: any;
  elements: any;
  firstTitle!: string;
  key!: number;
  key1!: number | null;
  modulesDescriptions: any[] = [];
  moduleSelected!: boolean;
  lessonsActivities: any[] = [];
  selectedLessonActivities: any[] = [];
  childStatus!: object;
  messageFailure!: string;
  succesMessage!: string;
  user_data!: any;
  userId!: number;
  dayId!: number;
  displayBasic2!: boolean;
  type!: string;
  avisForm!: FormGroup;
  reviewResult: any;
  proposForm!: FormGroup;
  productDialog!: boolean;
  submitted!: boolean;
  indexLesson!: number;
  @ViewChild('myDiv') myDiv!: ElementRef;
  @ViewChild('myDivScrollable') myTree!: Tree;
  constructor(
    private router: Router,
    private apprentiService: ApprentiService,
    private primengConfig: PrimeNGConfig,
    private ressourceService: RessourceService,
    private cdr: ChangeDetectorRef,
    private messageService: MessageService,
    private formBuilder: FormBuilder,
  ) {}
  ngAfterViewChecked(): void {
    const div = document.querySelectorAll('.scrollable');
    console.log(this.myTree?.el.nativeElement.scrollHeight);
    console.log(this.myTree?.el.nativeElement.clientHeight);
    


    if (this.myTree?.el.nativeElement.scrollHeight > this.myTree?.el.nativeElement.clientHeight) {
      console.log('aaaaaaaaaa');
      
      div!.forEach((element : any)=>{ element.classList.remove('indicator');element.classList.add('indicator1');})
    } else {
      console.log('bbbbbbbbbbb');
      div!.forEach((element : any)=>{ element.classList.remove('indicator1');element.classList.add('indicator');})
    }
  }

  


  @HostListener('window:resize', ['$event'])
  onResize(event: any) {
  
    const div = document.querySelectorAll('.scrollable');
    


    if (this.myTree!.el.nativeElement.scrollHeight > this.myTree!.el.nativeElement.clientHeight) {
      console.log('aaaaaaaaaa');
      
      div!.forEach((element : any)=>{ element.classList.remove('indicator');element.classList.add('indicator1');})
    } else {
      console.log('bbbbbbbbbbb');
      div!.forEach((element : any)=>{ element.classList.remove('indicator1');element.classList.add('indicator');})
    }


    this.innerWidth = event?.target.innerWidth;
    this.cdr.detectChanges();
  }

  onScroll() {
    this.id = 1;
    if (!this.goToCalled) {
      this.elements.forEach((element: any, index: number) =>
        document.getElementById(`${index}`)?.classList.remove('btnLight')
      );
      for (let index = 0; index < this.elements.length; index++) {
        if (
          this.elements[index + 1] == undefined ||
          this.elements[index + 1].getBoundingClientRect().top > 77
        ) {
          document.getElementById(`${index}`)?.classList.add('btnLight');
          break;
        }
      }
    }
    setTimeout(() => (this.goToCalled = false));
  }

  ngOnInit() {
    
    this.innerWidth = window.innerWidth;
    this.user_data = JSON.parse(localStorage.getItem('user_data') || '{}');
    this.userId = this.user_data.id;
    this.apprentiService.synopsis$.subscribe((data: any) => {
      this.cdr.detectChanges();
      if (data.indexLesson < this.indexLesson + 2) {
        this.messageService.clear();
        window.scroll(0, 0);
        this.key = data.key;
        this.key1 = data.key1;
        this.firstTitle = data.title;
        this.selectedLessonActivities = data.ActivityCourses;
        this.listResource = data.Resources;
        this.selectedSynopsis = data.synopsis;
        this.listResource = this.listResource.map((resource: any) => {
          const voted = resource.voted === 1;
          const disliked = resource.voted === -1;
          return { ...resource, liked: voted, disliked: disliked };
        });
      } else {
        this.messageService.add({
          key: 'bc',
          severity: 'error',
          summary: 'Error',
          detail: "Vous n'êtes pas autorisé à accéder à cette leçon aujourd'hui",
          life: 5000,
        });
      }
      
    });
    this.getCourse();
    this.cdr.detectChanges();
    this.primengConfig.ripple = true;

    this.avisForm = this.formBuilder.group(
      {
        comment: [''],
        rating: [0],
      },
      {
        validators: [this.commentConditionallyRequiredValidator],
      }
    );

    this.proposForm = this.formBuilder.group({
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
      comment: new FormControl(
        '',
        Validators.compose([Validators.required, Validators.minLength(3)])
      ),
    });
  }

  commentConditionallyRequiredValidator(formGroup: any) {
    if (formGroup.value.rating <= 3) {
      return Validators.required(formGroup.get('comment'))
        ? {
            commentRequired: true,
          }
        : null;
    }
    return null;
  }

  hideAvis() {
    this.avisForm.reset();
  }

  selectModule(title: string, key: number) {
    this.type = 'Module';
    this.moduleSelected = true;
    this.key = key;
    this.key1 = null;
    this.firstTitle = title;
    this.id = 0;
    window.scroll(0, 0);
    for (let index = 0; index < this.modulesDescriptions.length; index++) {
      if (title === this.modulesDescriptions[index].title) {
        this.description = this.modulesDescriptions[index].description;
        break;
      }
    }
  }

  selectCourse(
    courseData: any,
    title: string,
    key: number,
    key1: number,
    indexLesson?: number
  ) {
    if (
      indexLesson == undefined ||
      (indexLesson != undefined && indexLesson < this.indexLesson + 2)
    ) {
      this.messageService.clear();
      window.scroll(0, 0);
      this.moduleSelected = false;
      this.displayNoteLesson = title == this.currentDayLesson ? true : false;
      this.type = 'Leçon';
      this.key = key;
      this.key1 = key1;
      this.firstTitle = title;
      this.id = 0;
      this.selectedLessonActivities = this.lessonsActivities.filter(
        (lesson: any) => lesson.title == title
      )[0].activities;
      this.selectedSynopsis = courseData.synopsis;
      this.listResource = courseData.Resources;
      this.listResource = this.listResource.map((resource: any) => {
        const voted = resource.voted === 1;
        const disliked = resource.voted === -1;
        return { ...resource, liked: voted, disliked: disliked };
      });
    } else {
      this.messageService.add({
        key: 'bc',
        severity: 'info',
        summary: 'Info',
        detail: "Vous n'êtes pas autorisé à accéder à cette leçon aujourd'hui",
        life: 5000,
      });
    }
  }

  like(resourceId: number, index: number) {
    let data = { score: 1 };
    let resource = this.listResource[index];
    resource.liked = true;
    resource.disliked = false;
    this.ressourceService
      .recommendationResource(data, resourceId)
      .subscribe((resp) => {
        resource.score++;
      });
  }

  dislike(resourceId: number, index: number) {
    let data = { score: -1 };
    let resource = this.listResource[index];
    resource.liked = false;
    resource.disliked = true;
    this.ressourceService
      .recommendationResource(data, resourceId)
      .subscribe(() => {
        resource.score--;
      });
  }

 
  ngAfterContentChecked() {
    this.id = 0;
    this.list = [];
    this.elements = [];
    const item = this.synopsisId?.nativeElement;
    if (item) {
      let i = 0;
      for (const ele of item.querySelectorAll('h1,h2')) {
        if (ele.outerText) {
          ele.setAttribute('id', i + 1920);
          this.list.push(ele.outerText);
          this.elements.push(ele);
          i++;
        }
      }
      Array.from(document.getElementsByTagName('img')).forEach(
        (img) => (img.style.maxWidth = '100%')
      );
      Array.from(document.getElementsByTagName('p')).forEach(
        (p) => (p.style.lineHeight = '1.7')
      );
      Array.from(document.getElementsByTagName('div')).forEach(
        (div) => (div.style.lineHeight = '1.7')
      );
    }
  }

  getCourse() {
    this.apprentiService.backResponse$.subscribe((resp: any) => {
      this.dayId = resp.currentDay.dayLessonId;
      this.checkReviews(this.dayId);
      this.currentDayModule = resp.currentDay.currentModuleTitle;
      this.currentDayLesson = resp.currentDay.dayLessonTitle;
      resp.listModules.forEach((module: any) => {
        this.modulesDescriptions.push({
          title: module.title,
          description: module.description,
        });
        module.DayCourses.forEach((dayCourse: any) =>
          this.lessonsActivities.push({
            title: dayCourse.description,
            activities: dayCourse.activities,
          })
        );
      });
      if (resp.code == 200) {
        const tree: any = [];
        let key: number = 0;
        let key1: number = 0;
        let indexLesson = 0;
        resp.listModules.forEach((element: any) => {
          const branch: any = {};
          this.selectedProduct1 = branch;
          branch.label = element.title;
          key = key + 1;
          branch.key = key;
          branch.children = [];
          key1 = 0;
          element.DayCourses.forEach((course: any) => {
            if (course.id == this.dayId) this.indexLesson = indexLesson;
            let courseData = {
              synopsis: course.synopsis,
              Resources: course.ressources,
              ActivityCourses: course.activities,
            };
            key1 = key1 + 1;
            branch.children.push({
              indexLesson: indexLesson,
              key: branch.key,
              routerLinkActiveOptions: { exact: true },
              key1: key1,
              label: course.description,
              type: 'url',
              data: courseData,
             status : course.status
            });
            indexLesson += 1;
          });
          tree.push(branch);
          if (branch.label == this.currentDayModule) {
            this.selectedFile = branch.label;
            branch.expanded = true;
            let child = branch.children?.filter(
              (child: { label: string }) => child.label == this.currentDayLesson
            );
            this.selectCourse(
              child[0].data,
              this.currentDayLesson,
              child[0].key,
              child[0].key1
            );
          }
        });
        this.nodes = tree;
        this.visibleSidebar = true;
      }
    });
  }

  goTo(index: number) {
    this.goToCalled = true;
    this.elements.forEach((element: any, index: number) =>
      document.getElementById(`${index}`)?.classList.remove('btnLight')
    );
    this.elements.forEach((ele: any) => {
      if (ele.id == index + 1920) {
        this.router
          .navigate(['/apprenti/course'], {
            fragment: ele.id,
            skipLocationChange: false,
          })
          .then(() => {
            ele.scrollIntoView(true);
            window.scroll(0, window.scrollY - 77);
            document.getElementById(`${index}`)?.classList.add('btnLight');
            ele.focus({ preventScroll: true });
          });
      }
    });
  }

  addEval(panel: OverlayPanel) {
    if (this.avisForm.valid) {
      this.apprentiService
        .saveReview(this.dayId, this.avisForm.value)
        .subscribe(
          (resp: any) => {
            panel.hide();
            this.messageService.add({
              severity: 'success',
              summary: 'Succès',
              detail: resp.message,
            });
            this.checkReviews(this.dayId);
          },
          (error) => {
            this.messageService.add({
              severity: 'error',
              summary: 'Erreur',
              detail: error.error.message,
            });
          }
        );
    }
  }

  checkReviews(dayId: number) {
    return this.apprentiService.getStudentReview(dayId).subscribe((data) => {
      this.reviewResult = data;
    });
  }

  openNew() {
    this.submitted = false;
    this.productDialog = true;
    this.messageFailure = '';
    this.succesMessage = '';
  }

  hideDialog() {
    this.productDialog = false;
    this.submitted = false;
    this.messageFailure = '';
    this.proposForm.reset();
  }

  proposeRessources() {
    this.ressourceService
      .addRessources(this.proposForm.value, this.dayId)
      .subscribe(
        (data) => {
          this.succesMessage = 'Votre proposition est ajoutée avec succés ! ';
          this.hideDialog();
        },
        (error: HttpErrorResponse) => {
          if (error.error.code === 401) {
            this.messageFailure =
              'Il existe déjà une ressource avec cette même URL, merci de proposer une autre !';
          }
          if (error.error.code === 404) {
            this.messageFailure =
              'Vous ne pouvez pas proposer une ressource pour cette leçon ! ';
            this.proposForm.reset();
          }
        },
        () => {
          this.proposForm.reset();
        }
      );
  }
}
